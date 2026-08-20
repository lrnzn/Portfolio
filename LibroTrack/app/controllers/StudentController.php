<?php

require_once __DIR__ . "/../models/StudentDashboard.php";

class StudentController
{
    private StudentDashboard $model;
    private ?array $student = null;

    private ?string $profilePicUrl = null;

    public function __construct()
    {
        session_start();
        if (!isset($_SESSION['userID'])) {
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }
        $this->model   = new StudentDashboard();
        $this->student = $this->model->getStudentByUserID((int)$_SESSION['userID']);

        if (!$this->student) {
            header("Location: index.php?controller=Auth&action=logout");
            exit;
        }

        // Load profile picture for navbar
        require_once __DIR__ . "/../models/Profile.php";
        $profile = (new Profile())->getProfile((int)$_SESSION['userID']);
        if (!empty($profile['profile_picture'])) {
            $this->profilePicUrl = 'assets/img/profiles/' . $profile['profile_picture'];
        }
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    // ── Student Dashboard ──────────────────────────────────────────────────
    public function index(): void
    {
        $student       = $this->student;
        $stats         = $this->model->getStats($student['studentID']);
        $stats         = array_merge(['active_borrows'=>0,'overdue_count'=>0,'total_borrowed'=>0,'slots_remaining'=>3], $stats);
        $activeBorrows = $this->model->getActiveBorrows($student['studentID']);
        $profilePicUrl = $this->profilePicUrl;
        $fname         = $student['fname'] ?? 'Student';
        require __DIR__ . "/../views/client/dashboard.php";
    }

    // ── Browse Book Catalog ────────────────────────────────────────────────
    public function catalog(): void
    {
        $student       = $this->student;
        $search        = trim($_GET['search'] ?? '');
        $genre         = $_GET['genre']  ?? '';
        $status        = $_GET['status'] ?? '';
        $books         = $this->model->getCatalog($search, $genre, $status);
        $genres        = $this->model->getGenres();
        $profilePicUrl = $this->profilePicUrl;
        $fname         = $student['fname'] ?? 'Student';
        require __DIR__ . "/../views/client/catalog.php";
    }

    // ── My Borrowed Books ──────────────────────────────────────────────────
    public function borrowed(): void
    {
        $student       = $this->student;
        $stats         = $this->model->getStats($student['studentID']);
        $stats         = array_merge(['active_borrows'=>0,'overdue_count'=>0,'slots_remaining'=>3], $stats);
        $activeBorrows = $this->model->getActiveBorrows($student['studentID']);
        $profilePicUrl = $this->profilePicUrl;
        $fname         = $student['fname'] ?? 'Student';
        require __DIR__ . "/../views/client/my_borrowed.php";
    }

    // ── My Borrow History ──────────────────────────────────────────────────
    public function history(): void
    {
        $student       = $this->student;
        $search        = trim($_GET['search'] ?? '');
        $status        = $_GET['status'] ?? '';
        $stats         = $this->model->getStats($student['studentID']);
        $stats         = array_merge(['total_borrowed'=>0,'on_time'=>0,'returned_late'=>0], $stats);
        $history       = $this->model->getHistory($student['studentID'], $search, $status);
        $profilePicUrl = $this->profilePicUrl;
        $fname         = $student['fname'] ?? 'Student';
        require __DIR__ . "/../views/client/my_history.php";
    }
}
