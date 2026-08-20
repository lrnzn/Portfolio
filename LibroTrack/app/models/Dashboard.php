<?php

require_once __DIR__ . "/../../config/database.php";

class Dashboard
{
    private mysqli $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ── Stats for the 5 stat cards ─────────────────────────────────────────
    public function getStats(): array
    {
        $row = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM tbl_books) AS total_books,
                COALESCE((SELECT SUM(
                    b.copies - COALESCE(
                        (SELECT COUNT(*) FROM tbl_transaction t
                         WHERE t.bookID = b.bookID AND t.status = 'borrowed'), 0
                    )
                ) FROM tbl_books b), 0) AS available_copies,
                (SELECT COUNT(*) FROM tbl_transaction
                 WHERE status = 'borrowed') AS currently_borrowed,
                (SELECT COUNT(*) FROM tbl_transaction
                 WHERE status = 'overdue'
                 OR (status = 'borrowed' AND dueDate < CURDATE())) AS overdue,
                (SELECT COUNT(*) FROM tbl_student) AS total_borrowers
        ")->fetch_assoc();

        return $row;
    }

    // ── 5 most recent transactions ─────────────────────────────────────────
    public function getRecentTransactions(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT
                t.transactionID,
                t.borrowDate,
                t.status,
                t.dueDate,
                CONCAT(s.fname, ' ', s.lname) AS studentName,
                b.title AS bookTitle,
                CASE
                    WHEN t.status IN ('borrowed','overdue') AND t.dueDate < CURDATE()
                    THEN 'overdue'
                    ELSE t.status
                END AS displayStatus
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            ORDER BY t.dateAdded DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── Top overdue books for the alert card ──────────────────────────────
    public function getOverdueBooks(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT
                CONCAT(s.fname, ' ', s.lname) AS studentName,
                b.title AS bookTitle,
                DATEDIFF(CURDATE(), t.dueDate) AS daysOverdue
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            JOIN tbl_books  b ON t.bookID    = b.bookID
            WHERE t.status = 'overdue'
               OR (t.status = 'borrowed' AND t.dueDate < CURDATE())
            ORDER BY daysOverdue DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}