<?php

require_once __DIR__ . "/../models/Dashboard.php";

class DashboardController
{
    private Dashboard $dashboard;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?controller=Auth&action=login&error=" . urlencode("Please log in to access this page."));
            exit;
        }
        $this->dashboard = new Dashboard();
    }

    public function index(): void
    {
        $stats        = $this->dashboard->getStats();
        $stats        = array_merge(
            ['total_books' => 0, 'available_copies' => 0, 'currently_borrowed' => 0, 'overdue' => 0, 'total_borrowers' => 0],
            $stats
        );
        $transactions = $this->dashboard->getRecentTransactions();
        $overdueBooks = $this->dashboard->getOverdueBooks();

        require __DIR__ . "/../views/admin/dashboard.php";
    }
}
