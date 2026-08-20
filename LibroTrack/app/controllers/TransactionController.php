<?php

require_once __DIR__ . "/../models/Transaction.php";

class TransactionController
{
    private Transaction $transaction;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?controller=Auth&action=login&error=" . urlencode("Please log in to access this page."));
            exit;
        }
        $this->transaction = new Transaction();
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    // ── READ: Borrow page ──────────────────────────────────────────────────
    public function index(): void
    {
        $recentBorrows = $this->transaction->getRecentBorrows();
        $flash         = $_GET['flash']      ?? '';
        $flash_type    = $_GET['flash_type'] ?? 'success';
        require __DIR__ . "/../views/admin/borrow.php";
    }

    // ── READ: Return page ──────────────────────────────────────────────────
    public function returnPage(): void
    {
        $recentReturns = $this->transaction->getRecentReturns();
        $flash         = $_GET['flash']      ?? '';
        $flash_type    = $_GET['flash_type'] ?? 'success';
        require __DIR__ . "/../views/admin/return.php";
    }

    // ── READ: History page ─────────────────────────────────────────────────
    public function history(): void
    {
        $search       = trim($_GET['search'] ?? '');
        $status       = $_GET['status'] ?? '';
        $from         = $_GET['from']   ?? '';
        $to           = $_GET['to']     ?? '';

        $transactions = $this->transaction->getAll($search, $status, $from, $to);
        $stats        = $this->transaction->getStats();
        $stats        = array_merge(
            ['total' => 0, 'borrowed' => 0, 'returned' => 0, 'overdue' => 0],
            $stats
        );

        $flash        = $_GET['flash']      ?? '';
        $flash_type   = $_GET['flash_type'] ?? 'success';
        require __DIR__ . "/../views/admin/history.php";
    }

    // ── READ: AJAX — find student by student number ────────────────────────
    public function findStudent(): void
    {
        $studentNumber = trim($_GET['q'] ?? '');
        if (empty($studentNumber)) {
            $this->json(['success' => false, 'message' => 'No query provided.']);
        }
        $student = $this->transaction->findStudent($studentNumber);
        if (!$student) {
            $this->json(['success' => false, 'message' => 'Student not found.']);
        }
        $this->json(['success' => true, 'student' => $student]);
    }

    // ── READ: AJAX — find book by ISBN or title ────────────────────────────
    public function findBook(): void
    {
        $query = trim($_GET['q'] ?? '');
        if (empty($query)) {
            $this->json(['success' => false, 'message' => 'No query provided.']);
        }
        $book = $this->transaction->findBook($query);
        if (!$book) {
            $this->json(['success' => false, 'message' => 'Book not found.']);
        }
        $this->json(['success' => true, 'book' => $book]);
    }

    // ── READ: AJAX — get active borrows for a student (return dropdown) ────
    public function getActiveBorrows(): void
    {
        $studentID = (int)($_GET['studentID'] ?? 0);
        $borrows   = $this->transaction->getActiveBorrowsByStudent($studentID);
        $this->json(['success' => true, 'borrows' => $borrows]);
    }

    // ── READ: AJAX — get single transaction ───────────────────────────────
    public function get(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $t  = $this->transaction->getById($id);
        if (!$t) {
            $this->json(['success' => false, 'message' => 'Transaction not found.'], 404);
        }
        $this->json(['success' => true, 'transaction' => $t]);
    }

    // ── CREATE: Record a borrow ────────────────────────────────────────────
    public function borrow(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Transaction&action=index");
        }

        $result = $this->transaction->borrow($_POST);

        if ($result === true) {
            $this->redirect("index.php?controller=Transaction&action=index&flash=Borrow+recorded+successfully.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Transaction&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── UPDATE: Process a return ───────────────────────────────────────────
    public function processReturn(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Transaction&action=returnPage");
        }

        $id     = (int)($_POST['transactionID'] ?? 0);
        $result = $this->transaction->returnBook($id, $_POST);

        if ($result === true) {
            $this->redirect("index.php?controller=Transaction&action=returnPage&flash=Book+returned+successfully.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Transaction&action=returnPage&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── UPDATE: Edit borrow dates ──────────────────────────────────────────
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Transaction&action=history");
        }

        $id     = (int)($_POST['transactionID'] ?? 0);
        $result = $this->transaction->update($id, $_POST);

        if ($result === true) {
            $this->redirect("index.php?controller=Transaction&action=history&flash=Transaction+updated+successfully.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Transaction&action=history&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── DELETE: Remove a transaction ───────────────────────────────────────
    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Transaction&action=history");
        }

        $id     = (int)($_POST['transactionID'] ?? 0);
        $result = $this->transaction->delete($id);

        if ($result === true) {
            $this->redirect("index.php?controller=Transaction&action=history&flash=Transaction+deleted+successfully.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Transaction&action=history&flash=" . urlencode($result) . "&flash_type=error");
        }
    }
}