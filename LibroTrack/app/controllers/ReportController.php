<?php

require_once __DIR__ . "/../models/Report.php";

class ReportController
{
    private Report $report;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?controller=Auth&action=login&error=" . urlencode("Please log in to access this page."));
            exit;
        }
        $this->report = new Report();
    }

    public function index(): void
    {
        $month  = $_GET['month'] ?? '';
        $year   = $_GET['year']  ?? '';

        $stats          = $this->report->getStats($month, $year);
        $mostBorrowed   = $this->report->getMostBorrowed(5, $month, $year);
        $topBorrowers   = $this->report->getTopBorrowers(5, $month, $year);
        $byGenre        = $this->report->getBorrowsByGenre($month, $year);
        $availableMonths = $this->report->getAvailableMonths();

        $stats = array_merge(
            ['total_transactions' => 0, 'total_books' => 0, 'total_borrowers' => 0, 'total_penalties' => 0],
            $stats
        );

        require __DIR__ . "/../views/admin/reports.php";
    }
}
