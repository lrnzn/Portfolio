<?php

require_once __DIR__ . "/../../config/database.php";

class Transaction
{
    private mysqli $db;
    const PENALTY_RATE = 5.00; // ₱5.00 per day overdue

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ── READ: All transactions with search/filter ──────────────────────────
    public function getAll(string $search = '', string $status = '', string $from = '', string $to = ''): array
    {
        $conditions = ["1=1"];
        $params     = [];
        $types      = '';

        if ($search !== '') {
            $conditions[] = "(CONCAT(s.fname,' ',s.lname) LIKE ? OR b.title LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like);
            $types .= 'ss';
        }

        if ($status !== '') {
            $conditions[] = "t.status = ?";
            $params[]      = $status;
            $types        .= 's';
        }

        if ($from !== '') {
            $conditions[] = "t.borrowDate >= ?";
            $params[]      = $from;
            $types        .= 's';
        }

        if ($to !== '') {
            $conditions[] = "t.borrowDate <= ?";
            $params[]      = $to;
            $types        .= 's';
        }

        $where = implode(' AND ', $conditions);

        $sql = "
            SELECT
                t.transactionID,
                t.borrowDate,
                t.dueDate,
                t.returnDate,
                t.status,
                t.dateAdded,
                CONCAT(s.fname, ' ', s.lname) AS studentName,
                s.studentNumber,
                s.studentID,
                b.title AS bookTitle,
                b.bookID,
                b.author,
                CASE
                    WHEN t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE()
                    THEN DATEDIFF(CURDATE(), t.dueDate)
                    WHEN t.status = 'returned' AND t.returnDate > t.dueDate
                    THEN DATEDIFF(t.returnDate, t.dueDate)
                    ELSE 0
                END AS daysOverdue,
                CASE
                    WHEN t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE()
                    THEN DATEDIFF(CURDATE(), t.dueDate) * 5.00
                    WHEN t.status = 'returned' AND t.returnDate > t.dueDate
                    THEN DATEDIFF(t.returnDate, t.dueDate) * 5.00
                    ELSE 0
                END AS penaltyAmount,
                p.paid   AS penaltyPaid,
                p.penaltyID
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
            WHERE {$where}
            ORDER BY t.dateAdded DESC
        ";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Single transaction by ID ─────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, CONCAT(s.fname,' ',s.lname) AS studentName,
                s.studentNumber, b.title AS bookTitle, b.author,
                p.amount AS penaltyAmount, p.paid AS penaltyPaid, p.penaltyID
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
            WHERE t.transactionID = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Recent borrows (for borrow page sidebar) ─────────────────────
    public function getRecentBorrows(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT t.transactionID, t.dueDate, t.status,
                CONCAT(s.fname,' ',s.lname) AS studentName,
                b.title AS bookTitle
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            WHERE t.status IN ('borrowed','overdue')
            ORDER BY t.dateAdded DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Recent returns (for return page sidebar) ─────────────────────
    public function getRecentReturns(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT t.transactionID, t.returnDate, t.status,
                CONCAT(s.fname,' ',s.lname) AS studentName,
                b.title AS bookTitle,
                p.amount AS penaltyAmount
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
            WHERE t.status = 'returned'
            ORDER BY t.returnDate DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Students who currently have active borrows (for return dropdown) ─
    public function getStudentsWithActiveBorrows(): array
    {
        $result = $this->db->query("
            SELECT s.studentID, s.fname, s.lname, s.studentNumber, s.course,
                COUNT(t.transactionID) AS active_borrows
            FROM tbl_student s
            JOIN tbl_transaction t ON s.studentID = t.studentID
            WHERE t.status IN ('borrowed', 'overdue')
            GROUP BY s.studentID
            ORDER BY s.lname, s.fname
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: All students for dropdown ───────────────────────────────────
    public function getAllStudents(): array
    {
        $result = $this->db->query("
            SELECT s.studentID, s.fname, s.lname, s.studentNumber, s.course,
                COUNT(CASE WHEN t.status IN ('borrowed','overdue') THEN 1 END) AS active_borrows
            FROM tbl_student s
            LEFT JOIN tbl_transaction t ON s.studentID = t.studentID
            GROUP BY s.studentID
            ORDER BY s.lname, s.fname
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: All available books for dropdown ─────────────────────────────
    public function getAllAvailableBooks(): array
    {
        $result = $this->db->query("
            SELECT b.bookID, b.title, b.author, b.genre, b.copies,
                (b.copies - COALESCE(
                    (SELECT COUNT(*) FROM tbl_transaction t
                     WHERE t.bookID = b.bookID AND t.status IN ('borrowed','overdue')), 0
                )) AS available
            FROM tbl_books b
            HAVING available > 0
            ORDER BY b.title
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Find student by student number (for AJAX lookup) ────────────
    public function findStudent(string $query): ?array
    {
        $like = "%{$query}%";
        $stmt = $this->db->prepare("
            SELECT s.studentID, s.fname, s.lname, s.studentNumber, s.course,
                COUNT(CASE WHEN t.status IN ('borrowed','overdue') THEN 1 END) AS active_borrows
            FROM tbl_student s
            LEFT JOIN tbl_transaction t ON s.studentID = t.studentID
            WHERE s.studentNumber LIKE ?
               OR s.fname LIKE ?
               OR s.lname LIKE ?
               OR CONCAT(s.fname,' ',s.lname) LIKE ?
            GROUP BY s.studentID
            LIMIT 1
        ");
        $stmt->bind_param('ssss', $like, $like, $like, $like);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Find book by ISBN or title (for AJAX lookup) ────────────────
    public function findBook(string $query): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.bookID, b.title, b.author, b.genre, b.isbn, b.copies,
                (b.copies - COALESCE(
                    (SELECT COUNT(*) FROM tbl_transaction t2
                     WHERE t2.bookID = b.bookID AND t2.status IN ('borrowed','overdue')), 0
                )) AS available
            FROM tbl_books b
            WHERE b.isbn = ? OR b.title LIKE ?
            LIMIT 1
        ");
        $like = "%{$query}%";
        $stmt->bind_param('ss', $query, $like);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Get active borrows for a student (for return dropdown) ───────
    public function getActiveBorrowsByStudent(int $studentID): array
    {
        $stmt = $this->db->prepare("
            SELECT t.transactionID, t.dueDate, t.status, b.title, b.bookID,
                CASE WHEN t.dueDate < CURDATE() THEN DATEDIFF(CURDATE(), t.dueDate) ELSE 0 END AS daysOverdue,
                CASE WHEN t.dueDate < CURDATE() THEN DATEDIFF(CURDATE(), t.dueDate) * ? ELSE 0 END AS penaltyAmount
            FROM tbl_transaction t
            JOIN tbl_books b ON t.bookID = b.bookID
            WHERE t.studentID = ? AND t.status IN ('borrowed','overdue')
            ORDER BY t.dueDate ASC
        ");
        $rate = self::PENALTY_RATE;
        $stmt->bind_param('di', $rate, $studentID);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Stats ────────────────────────────────────────────────────────
    public function getStats(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(*) AS total,
                COUNT(CASE WHEN status = 'borrowed' THEN 1 END) AS borrowed,
                COUNT(CASE WHEN status = 'returned' THEN 1 END) AS returned,
                COUNT(CASE WHEN status = 'overdue'
                      OR (status = 'borrowed' AND dueDate < CURDATE()) THEN 1 END) AS overdue
            FROM tbl_transaction
        ")->fetch_assoc();
        return $row;
    }

    // ── CREATE: Record a new borrow ────────────────────────────────────────
    public function borrow(array $data): bool|string
    {
        $studentID = (int) $data['studentID'];
        $bookID    = (int) $data['bookID'];

        // Check book has available copies
        $chk = $this->db->prepare("
            SELECT (b.copies - COALESCE(
                (SELECT COUNT(*) FROM tbl_transaction t
                 WHERE t.bookID = b.bookID AND t.status IN ('borrowed','overdue')), 0
            )) AS available FROM tbl_books b WHERE b.bookID = ?
        ");
        $chk->bind_param('i', $bookID);
        $chk->execute();
        $avail = (int) $chk->get_result()->fetch_assoc()['available'];
        if ($avail <= 0) {
            return "No available copies of this book.";
        }

        // Check student doesn't already have this book borrowed
        $chk2 = $this->db->prepare("
            SELECT transactionID FROM tbl_transaction
            WHERE studentID = ? AND bookID = ? AND status IN ('borrowed','overdue')
        ");
        $chk2->bind_param('ii', $studentID, $bookID);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            return "This student already has this book borrowed.";
        }

        $borrowDate = $data['borrowDate'];
        $dueDate    = $data['dueDate'];

        $stmt = $this->db->prepare(
            "INSERT INTO tbl_transaction (studentID, bookID, borrowDate, dueDate, status)
             VALUES (?, ?, ?, ?, 'borrowed')"
        );
        $stmt->bind_param('iiss', $studentID, $bookID, $borrowDate, $dueDate);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── UPDATE: Process a return ───────────────────────────────────────────
    public function returnBook(int $transactionID, array $data): bool|string
    {
        $transaction = $this->getById($transactionID);
        if (!$transaction) {
            return "Transaction not found.";
        }

        if ($transaction['status'] === 'returned') {
            return "This book has already been returned.";
        }

        $returnDate  = $data['returnDate'];
        $daysOverdue = max(0, (int) $data['daysOverdue']);
        $penalty     = $daysOverdue * self::PENALTY_RATE;

        // Update transaction status
        $stmt = $this->db->prepare(
            "UPDATE tbl_transaction
             SET status = 'returned', returnDate = ?
             WHERE transactionID = ?"
        );
        $stmt->bind_param('si', $returnDate, $transactionID);
        if (!$stmt->execute()) {
            return $this->db->error;
        }

        // Create or update penalty record if overdue
        if ($daysOverdue > 0) {
            $paid = isset($data['penalty_paid']) && $data['penalty_paid'] === '1' ? 1 : 0;

            // Check if penalty already exists
            $existing = $this->db->prepare(
                "SELECT penaltyID FROM tbl_penalties WHERE transactionID = ?"
            );
            $existing->bind_param('i', $transactionID);
            $existing->execute();

            if ($existing->get_result()->num_rows > 0) {
                $upd = $this->db->prepare(
                    "UPDATE tbl_penalties SET daysOverdue=?, amount=?, paid=? WHERE transactionID=?"
                );
                $upd->bind_param('idii', $daysOverdue, $penalty, $paid, $transactionID);
                $upd->execute();
            } else {
                $ins = $this->db->prepare(
                    "INSERT INTO tbl_penalties (transactionID, daysOverdue, amount, paid)
                     VALUES (?, ?, ?, ?)"
                );
                $ins->bind_param('iidi', $transactionID, $daysOverdue, $penalty, $paid);
                $ins->execute();
            }
        }

        return true;
    }

    // ── UPDATE: Edit a borrow transaction (dates/status only) ─────────────
    public function update(int $id, array $data): bool|string
    {
        $transaction = $this->getById($id);
        if (!$transaction) return "Transaction not found.";
        if ($transaction['status'] === 'returned') return "Cannot edit a returned transaction.";

        $borrowDate = $data['borrowDate'];
        $dueDate    = $data['dueDate'];

        $stmt = $this->db->prepare(
            "UPDATE tbl_transaction SET borrowDate=?, dueDate=? WHERE transactionID=?"
        );
        $stmt->bind_param('ssi', $borrowDate, $dueDate, $id);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── DELETE: Remove a transaction ───────────────────────────────────────
    public function delete(int $id): bool|string
    {
        $transaction = $this->getById($id);
        if (!$transaction) return "Transaction not found.";

        if ($transaction['status'] === 'borrowed' || $transaction['status'] === 'overdue') {
            return "Cannot delete an active borrow — process the return first.";
        }

        // Penalties cascade-delete via FK, so just delete the transaction
        $stmt = $this->db->prepare("DELETE FROM tbl_transaction WHERE transactionID = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute() ? true : $this->db->error;
    }
}