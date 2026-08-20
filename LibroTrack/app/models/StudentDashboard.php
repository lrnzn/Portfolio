<?php

require_once __DIR__ . "/../../config/database.php";

class StudentDashboard
{
    private mysqli $db;
    const BORROW_LIMIT = 3;
    const PENALTY_RATE = 5.00;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ── READ: Get student record from userID (session) ─────────────────────
    public function getStudentByUserID(int $userID): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, CONCAT(s.fname,' ',s.lname) AS fullName
            FROM tbl_student s
            WHERE s.userID = ?
        ");
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Stats for dashboard and my_borrowed ──────────────────────────
    public function getStats(int $studentID): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN t.status IN ('borrowed','overdue')
                      OR (t.status='borrowed' AND t.dueDate < CURDATE()) THEN 1 END) AS active_borrows,
                COUNT(CASE WHEN (t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE())
                           OR t.status='overdue' THEN 1 END) AS overdue_count,
                COUNT(t.transactionID) AS total_borrowed,
                COUNT(CASE WHEN t.status='returned' AND t.returnDate <= t.dueDate THEN 1 END) AS on_time,
                COUNT(CASE WHEN t.status='returned' AND t.returnDate > t.dueDate THEN 1 END) AS returned_late
            FROM tbl_transaction t
            WHERE t.studentID = ?
        ");
        $stmt->bind_param('i', $studentID);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $row['slots_remaining'] = self::BORROW_LIMIT - (int)$row['active_borrows'];
        return $row;
    }

    // ── READ: Active borrows for dashboard and my_borrowed ─────────────────
    public function getActiveBorrows(int $studentID): array
    {
        $stmt = $this->db->prepare("
            SELECT t.transactionID, t.borrowDate, t.dueDate, t.status,
                b.title, b.author, b.location, b.cover_image,
                CASE WHEN t.dueDate < CURDATE() AND t.status = 'borrowed'
                     THEN DATEDIFF(CURDATE(), t.dueDate) ELSE 0 END AS daysOverdue,
                CASE WHEN t.dueDate < CURDATE() AND t.status = 'borrowed'
                     THEN DATEDIFF(CURDATE(), t.dueDate) * ? ELSE 0 END AS penaltyAmount,
                DATEDIFF(t.dueDate, CURDATE()) AS daysLeft
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

    // ── READ: Full history ─────────────────────────────────────────────────
    public function getHistory(int $studentID, string $search = '', string $status = ''): array
    {
        $conditions = ["t.studentID = ?"];
        $params     = [$studentID];
        $types      = 'i';

        if ($search !== '') {
            $conditions[] = "b.title LIKE ?";
            $params[]      = "%{$search}%";
            $types        .= 's';
        }

        if ($status !== '') {
            if ($status === 'overdue') {
                $conditions[] = "(t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE())";
            } else {
                $conditions[] = "t.status = ?";
                $params[]      = $status;
                $types        .= 's';
            }
        }

        $where = implode(' AND ', $conditions);

        $stmt = $this->db->prepare("
            SELECT t.transactionID, t.borrowDate, t.dueDate, t.returnDate, t.status,
                b.title, b.author,
                p.amount AS penaltyAmount, p.paid AS penaltyPaid,
                CASE
                    WHEN t.status = 'returned' AND t.returnDate > t.dueDate THEN 'returned_late'
                    WHEN t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE() THEN 'overdue'
                    ELSE t.status
                END AS displayStatus
            FROM tbl_transaction t
            JOIN tbl_books b ON t.bookID = b.bookID
            LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
            WHERE {$where}
            ORDER BY t.dateAdded DESC
        ");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Book catalog for student (same as admin but student-facing) ──
    public function getCatalog(string $search = '', string $genre = '', string $status = ''): array
    {
        $conditions = ["1=1"];
        $params     = [];
        $types      = '';

        if ($search !== '') {
            $conditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }

        if ($genre !== '') {
            $conditions[] = "b.genre = ?";
            $params[]      = $genre;
            $types        .= 's';
        }

        $havingClause = '';
        if ($status === 'available') {
            $havingClause = 'HAVING available > 0';
        } elseif ($status === 'unavailable') {
            $havingClause = 'HAVING available = 0';
        }

        $where = implode(' AND ', $conditions);

        $sql = "
            SELECT b.bookID, b.title, b.author, b.genre, b.isbn, b.copies,
                b.cover_image, b.description, b.location,
                (b.copies - COALESCE(
                    (SELECT COUNT(*) FROM tbl_transaction t
                     WHERE t.bookID = b.bookID AND t.status IN ('borrowed','overdue')), 0
                )) AS available
            FROM tbl_books b
            WHERE {$where}
            {$havingClause}
            ORDER BY b.title
        ";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Genres for filter ────────────────────────────────────────────
    public function getGenres(): array
    {
        $result = $this->db->query("SELECT DISTINCT genre FROM tbl_books ORDER BY genre");
        return array_column($result->fetch_all(MYSQLI_ASSOC), 'genre');
    }
}
