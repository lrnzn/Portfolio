<?php

require_once __DIR__ . "/../models/Student.php";

class BorrowerController
{
    private Student $student;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?controller=Auth&action=login&error=" . urlencode("Please log in to access this page."));
            exit;
        }
        $this->student = new Student();
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

    // ── READ: Borrower Management page ────────────────────────────────────
    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $course = $_GET['course'] ?? '';
        $status = $_GET['status'] ?? '';

        $borrowers = $this->student->getAll($search, $course, $status);
        $courses   = $this->student->getCourses();
        $stats     = $this->student->getStats();

        $stats = array_merge(
            ['total_borrowers' => 0, 'currently_borrowing' => 0, 'with_overdue' => 0],
            $stats
        );

        $flash        = $_GET['flash']        ?? '';
        $flash_type   = $_GET['flash_type']   ?? 'success';
        $flash_action = $_GET['flash_action'] ?? '';

        require __DIR__ . "/../views/admin/borrowers.php";
    }

    // ── READ: Get single borrower as JSON (for edit/view modal) ───────────
    public function get(): void
    {
        $id      = (int)($_GET['id'] ?? 0);
        $student = $this->student->getById($id);

        if (!$student) {
            $this->json(['success' => false, 'message' => 'Borrower not found.'], 404);
        }

        $borrows = $this->student->getActiveBorrows($id);
        $this->json(['success' => true, 'student' => $student, 'borrows' => $borrows]);
    }

    // ── CREATE ────────────────────────────────────────────────────────────
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Borrower&action=index");
        }

        $result = $this->student->create($_POST);

        if ($result === true) {
            $this->redirect("index.php?controller=Borrower&action=index&flash=Borrower+added+successfully.&flash_type=success&flash_action=added");
        } else {
            $this->redirect("index.php?controller=Borrower&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── UPDATE ────────────────────────────────────────────────────────────
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Borrower&action=index");
        }

        $id     = (int)($_POST['studentID'] ?? 0);
        $result = $this->student->update($id, $_POST);

        if ($result === true) {
            $this->redirect("index.php?controller=Borrower&action=index&flash=Borrower+updated+successfully.&flash_type=success&flash_action=updated");
        } else {
            $this->redirect("index.php?controller=Borrower&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── DELETE ────────────────────────────────────────────────────────────
    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Borrower&action=index");
        }

        $id     = (int)($_POST['studentID'] ?? 0);
        $result = $this->student->delete($id);

        if ($result === true) {
            $this->redirect("index.php?controller=Borrower&action=index&flash=Borrower+deleted+successfully.&flash_type=success&flash_action=deleted");
        } else {
            $this->redirect("index.php?controller=Borrower&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }
}
