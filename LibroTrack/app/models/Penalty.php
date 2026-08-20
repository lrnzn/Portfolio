<?php

require_once __DIR__ . "/../../config/database.php";

class Penalty
{
    private mysqli $db;
    const RATE = 5.00; // ₱5.00 per day

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ── READ: All overdue records with penalty info ────────────────────────
    public function getAll(string $search = '', string $status = ''): array
    {
        $conditions = ["((t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE()) OR t.status = 'overdue')"];
        $params     = [];
        $types      = '';

        if ($search !== '') {
            $conditions[] = "(CONCAT(s.fname,' ',s.lname) LIKE ? OR b.title LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like);
            $types .= 'ss';
        }

        if ($status === 'paid') {
            $conditions[] = "p.paid = 1";
        } elseif ($status === 'unpaid') {
            $conditions[] = "(p.paid = 0 OR p.paid IS NULL)";
        }

        $where = implode(' AND ', $conditions);

        $sql = "
            SELECT
                t.transactionID,
                t.dueDate,
                t.borrowDate,
                t.status,
                CONCAT(s.fname,' ',s.lname) AS studentName,
                s.studentNumber,
                s.course,
                b.title AS bookTitle,
                DATEDIFF(CURDATE(), t.dueDate) AS daysOverdue,
                DATEDIFF(CURDATE(), t.dueDate) * ? AS penaltyAmount,
                p.penaltyID,
                p.paid,
                p.amount AS recordedPenalty
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
            WHERE {$where}
            ORDER BY daysOverdue DESC
        ";

        $rate = self::RATE;
        array_unshift($params, $rate);
        $types = 'd' . $types;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Stats ────────────────────────────────────────────────────────
    public function getStats(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(DISTINCT t.transactionID) AS overdue_count,
                SUM(DATEDIFF(CURDATE(), t.dueDate) * " . self::RATE . ") AS total_penalties,
                COALESCE(SUM(CASE WHEN p.paid = 1 THEN p.amount ELSE 0 END), 0) AS paid_amount,
                COALESCE(SUM(CASE WHEN (p.paid = 0 OR p.paid IS NULL)
                    THEN DATEDIFF(CURDATE(), t.dueDate) * " . self::RATE . " ELSE 0 END), 0) AS unpaid_amount
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
            WHERE (t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE())
               OR t.status = 'overdue'
        ")->fetch_assoc();
        return $row;
    }

    // ── UPDATE: Mark penalty as paid ───────────────────────────────────────
    public function markPaid(int $transactionID): bool|string
    {
        // Check if penalty record exists
        $chk = $this->db->prepare(
            "SELECT penaltyID FROM tbl_penalties WHERE transactionID = ?"
        );
        $chk->bind_param('i', $transactionID);
        $chk->execute();
        $existing = $chk->get_result()->fetch_assoc();

        if ($existing) {
            $stmt = $this->db->prepare(
                "UPDATE tbl_penalties SET paid = 1 WHERE transactionID = ?"
            );
            $stmt->bind_param('i', $transactionID);
        } else {
            // Create penalty record and mark as paid
            $daysOverdue = $this->db->prepare(
                "SELECT DATEDIFF(CURDATE(), dueDate) AS days FROM tbl_transaction WHERE transactionID = ?"
            );
            $daysOverdue->bind_param('i', $transactionID);
            $daysOverdue->execute();
            $days   = (int) $daysOverdue->get_result()->fetch_assoc()['days'];
            $amount = $days * self::RATE;

            $stmt = $this->db->prepare(
                "INSERT INTO tbl_penalties (transactionID, daysOverdue, amount, paid)
                 VALUES (?, ?, ?, 1)"
            );
            $stmt->bind_param('iid', $transactionID, $days, $amount);
        }

        return $stmt->execute() ? true : $this->db->error;
    }
}