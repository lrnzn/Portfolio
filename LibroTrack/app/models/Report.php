<?php

require_once __DIR__ . "/../../config/database.php";

class Report
{
    private mysqli $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    private function transactionDateFilter(string $month = '', string $year = '', string $alias = 't'): string
    {
        $monthNum = (int)$month;
        $yearNum  = (int)$year;

        if ($monthNum < 1 || $monthNum > 12 || $yearNum < 2000) {
            return '';
        }

        return "AND MONTH({$alias}.borrowDate) = {$monthNum} AND YEAR({$alias}.borrowDate) = {$yearNum}";
    }

    // ── READ: Summary stats ────────────────────────────────────────────────
    public function getStats(string $month = '', string $year = ''): array
    {
        $monthFilter = $this->transactionDateFilter($month, $year, 't');

        $row = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM tbl_transaction t
                 WHERE 1=1 {$monthFilter}) AS total_transactions,
                (SELECT COUNT(*) FROM tbl_books) AS total_books,
                (SELECT COUNT(*) FROM tbl_student) AS total_borrowers,
                COALESCE((
                    SELECT SUM(p.amount)
                    FROM tbl_penalties p
                    JOIN tbl_transaction t ON p.transactionID = t.transactionID
                    WHERE 1=1 {$monthFilter}
                ), 0) AS total_penalties
        ")->fetch_assoc();
        return $row;
    }

    // ── READ: Most borrowed books ──────────────────────────────────────────
    public function getMostBorrowed(int $limit = 5, string $month = '', string $year = ''): array
    {
        $monthFilter = $this->transactionDateFilter($month, $year, 't');

        $stmt = $this->db->prepare("
            SELECT b.title, b.author, b.genre,
                COUNT(t.transactionID) AS borrow_count
            FROM tbl_transaction t
            JOIN tbl_books b ON t.bookID = b.bookID
            WHERE 1=1 {$monthFilter}
            GROUP BY b.bookID
            ORDER BY borrow_count DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Top borrowers ────────────────────────────────────────────────
    public function getTopBorrowers(int $limit = 5, string $month = '', string $year = ''): array
    {
        $monthFilter = $this->transactionDateFilter($month, $year, 't');

        $stmt = $this->db->prepare("
            SELECT CONCAT(s.fname,' ',s.lname) AS studentName,
                s.course, s.studentNumber,
                COUNT(t.transactionID) AS borrow_count
            FROM tbl_transaction t
            JOIN tbl_student s ON t.studentID = s.studentID
            WHERE 1=1 {$monthFilter}
            GROUP BY s.studentID
            ORDER BY borrow_count DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Borrows by genre ─────────────────────────────────────────────
    public function getBorrowsByGenre(string $month = '', string $year = ''): array
    {
        $monthFilter = $this->transactionDateFilter($month, $year, 't');

        $result = $this->db->query("
            SELECT b.genre,
                COUNT(t.transactionID) AS borrow_count
            FROM tbl_transaction t
            JOIN tbl_books b ON t.bookID = b.bookID
            WHERE 1=1 {$monthFilter}
            GROUP BY b.genre
            ORDER BY borrow_count DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Available months for filter ─────────────────────────────────
    public function getAvailableMonths(): array
    {
        $result = $this->db->query("
            SELECT DISTINCT
                MONTH(borrowDate) AS month,
                YEAR(borrowDate)  AS year,
                DATE_FORMAT(borrowDate, '%M %Y') AS label
            FROM tbl_transaction
            ORDER BY year DESC, month DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
