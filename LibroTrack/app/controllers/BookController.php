<?php

require_once __DIR__ . "/../models/Book.php";

class BookController
{
    private Book $book;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
            header("Location: index.php?controller=Auth&action=login&error=" . urlencode("Please log in to access this page."));
            exit;
        }
        $this->book = new Book();
    }

    // Helper: send JSON response and exit
    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    // Helper: redirect with a flash message via query string
    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    // ── READ: Book Management page (table view) ────────────────────────────
    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $genre  = $_GET['genre']  ?? '';
        $status = $_GET['status'] ?? '';

        $books  = $this->book->getAll($search, $genre, $status);
        $genres = $this->book->getGenres();
        $stats  = $this->book->getStats();
        $stats  = array_merge(
            ['total_books' => 0, 'total_copies' => 0, 'available_copies' => 0, 'currently_borrowed' => 0],
            $stats
        );

        $flash        = $_GET['flash']        ?? '';
        $flash_type   = $_GET['flash_type']   ?? 'success';
        $flash_action = $_GET['flash_action'] ?? '';

        require "../app/views/admin/book_management.php";
    }

    // ── READ: Book Catalog page (grid/list view) ───────────────────────────
    public function catalog(): void
    {
        $search = trim($_GET['search'] ?? '');
        $genre  = $_GET['genre']  ?? '';
        $status = $_GET['status'] ?? '';

        $books  = $this->book->getAll($search, $genre, $status);
        $genres = $this->book->getGenres();

        require "../app/views/admin/book_catalog.php";
    }

    // ── CREATE: Save new book ──────────────────────────────────────────────
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Book&action=index");
        }

        $file   = $_FILES['cover_image'] ?? [];
        $result = $this->book->create($_POST, $file);

        if ($result === true) {
            $this->redirect("index.php?controller=Book&action=index&flash=Book+added+successfully.&flash_type=success&flash_action=added");
        } else {
            $this->redirect("index.php?controller=Book&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── READ: Get single book as JSON (for edit modal) ─────────────────────
    public function get(): void
    {
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->book->getById($id);

        if (!$book) {
            $this->json(['success' => false, 'message' => 'Book not found.'], 404);
        }

        $this->json(['success' => true, 'book' => $book]);
    }

    // ── UPDATE: Save edited book ───────────────────────────────────────────
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Book&action=index");
        }

        $id     = (int)($_POST['bookID'] ?? 0);
        $file   = $_FILES['cover_image'] ?? [];
        $result = $this->book->update($id, $_POST, $file);

        if ($result === true) {
            $this->redirect("index.php?controller=Book&action=index&flash=Book+updated+successfully.&flash_type=success&flash_action=updated");
        } else {
            $this->redirect("index.php?controller=Book&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── DELETE: Remove a book ──────────────────────────────────────────────
    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Book&action=index");
        }

        $id     = (int)($_POST['bookID'] ?? 0);
        $result = $this->book->delete($id);

        if ($result === true) {
            $this->redirect("index.php?controller=Book&action=index&flash=Book+deleted+successfully.&flash_type=success&flash_action=deleted");
        } else {
            $this->redirect("index.php?controller=Book&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }
}
