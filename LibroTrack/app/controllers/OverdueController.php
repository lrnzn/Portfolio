<?php

require_once __DIR__ . "/../models/Penalty.php";

class OverdueController
{
    private Penalty $penalty;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?controller=Auth&action=login&error=" . urlencode("Please log in to access this page."));
            exit;
        }
        $this->penalty = new Penalty();
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    // ── READ: Overdue page ─────────────────────────────────────────────────
    public function index(): void
    {
        $search  = trim($_GET['search'] ?? '');
        $status  = $_GET['status'] ?? '';

        $records = $this->penalty->getAll($search, $status);
        $stats   = $this->penalty->getStats();
        $stats   = array_merge(
            ['overdue_count' => 0, 'total_penalties' => 0, 'paid_amount' => 0, 'unpaid_amount' => 0],
            $stats
        );

        $flash      = $_GET['flash']      ?? '';
        $flash_type = $_GET['flash_type'] ?? 'success';

        require __DIR__ . "/../views/admin/overdue.php";
    }

    // ── UPDATE: Mark penalty as paid ───────────────────────────────────────
    public function markPaid(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Overdue&action=index");
        }

        $transactionID = (int)($_POST['transactionID'] ?? 0);
        $result        = $this->penalty->markPaid($transactionID);

        if ($result === true) {
            $this->redirect("index.php?controller=Overdue&action=index&flash=Penalty+marked+as+paid.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Overdue&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }
}
