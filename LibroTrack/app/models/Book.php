<?php

require_once __DIR__ . "/../../config/database.php";

class Book
{
    private mysqli $db;

    // Upload directory relative to project root (public-accessible)
    const UPLOAD_DIR  = __DIR__ . "/../../public/assets/img/covers/";
    const UPLOAD_URL  = "assets/img/covers/";
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const MAX_SIZE    = 2 * 1024 * 1024; // 2MB

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        // Make sure the upload directory exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }
    }

    // ── HELPER: Handle cover image upload, returns filename or error string ─
    private function handleUpload(array $file): string|null|false
    {
        // No file uploaded — return null (no image)
        if ($file['error'] === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return "Image upload failed (error code {$file['error']}).";
        }

        if ($file['size'] > self::MAX_SIZE) {
            return "Image must be 2MB or smaller.";
        }

        if (!in_array($file['type'], self::ALLOWED_TYPES)) {
            return "Only JPG, PNG, GIF, and WEBP images are allowed.";
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cover_', true) . '.' . strtolower($ext);
        $dest     = self::UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return "Failed to save the uploaded image.";
        }

        return $filename;
    }

    // ── HELPER: Delete an old cover image file ─────────────────────────────
    private function deleteImage(string $filename): void
    {
        $path = self::UPLOAD_DIR . $filename;
        if ($filename && file_exists($path)) {
            unlink($path);
        }
    }

    // ── READ: Get all books with available copies ──────────────────────────
    public function getAll(string $search = '', string $genre = '', string $status = ''): array
    {
        $conditions = ["1=1"];
        $params     = [];
        $types      = '';

        if ($search !== '') {
            $conditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }

        if ($genre !== '') {
            $conditions[] = "b.genre = ?";
            $params[]      = $genre;
            $types        .= 's';
        }

        $where = implode(' AND ', $conditions);

        $havingClause = '';
        if ($status === 'available') {
            $havingClause = 'HAVING available > 0';
        } elseif ($status === 'borrowed') {
            $havingClause = 'HAVING available = 0';
        }

        $sql = "
            SELECT
                b.bookID,
                b.title,
                b.author,
                b.isbn,
                b.genre,
                b.copies,
                b.location,
                b.description,
                b.cover_image,
                b.dateAdded,
                (b.copies - COALESCE(
                    (SELECT COUNT(*) FROM tbl_transaction t
                     WHERE t.bookID = b.bookID AND t.status = 'borrowed'), 0
                )) AS available
            FROM tbl_books b
            WHERE {$where}
            {$havingClause}
            ORDER BY b.dateAdded DESC
        ";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Get single book by ID ────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*,
                (b.copies - COALESCE(
                    (SELECT COUNT(*) FROM tbl_transaction t
                     WHERE t.bookID = b.bookID AND t.status = 'borrowed'), 0
                )) AS available
            FROM tbl_books b
            WHERE b.bookID = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Get all distinct genres ─────────────────────────────────────
    public function getGenres(): array
    {
        $result = $this->db->query("SELECT DISTINCT genre FROM tbl_books ORDER BY genre");
        return array_column($result->fetch_all(MYSQLI_ASSOC), 'genre');
    }

    // ── READ: Stats counts ─────────────────────────────────────────────────
    public function getStats(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(*) AS total_books,
                COALESCE(SUM(copies), 0) AS total_copies,
                (SELECT COUNT(*) FROM tbl_transaction WHERE status = 'borrowed') AS currently_borrowed
            FROM tbl_books
        ")->fetch_assoc();
        $row['available_copies'] = (int)$row['total_copies'] - (int)$row['currently_borrowed'];
        return $row;
    }

    // ── CREATE ─────────────────────────────────────────────────────────────
    public function create(array $data, array $file = []): bool|string
    {
        if (!empty($data['isbn'])) {
            $chk = $this->db->prepare("SELECT bookID FROM tbl_books WHERE isbn = ?");
            $chk->bind_param('s', $data['isbn']);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                return "A book with this ISBN already exists.";
            }
        }

        // Handle image upload
        $cover = null;
        if (!empty($file['name'])) {
            $result = $this->handleUpload($file);
            if (is_string($result) && !empty($result) && !file_exists(self::UPLOAD_DIR . $result)) {
                return $result; // upload error message
            }
            $cover = $result;
        }

        $title    = trim($data['title']);
        $author   = trim($data['author']);
        $isbn     = !empty($data['isbn'])        ? trim($data['isbn'])        : null;
        $genre    = trim($data['genre']);
        $copies   = (int) $data['copies'];
        $location = !empty($data['location'])    ? trim($data['location'])    : null;
        $desc     = !empty($data['description']) ? trim($data['description']) : null;

        $stmt = $this->db->prepare(
            "INSERT INTO tbl_books (title, author, isbn, genre, copies, location, description, cover_image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssisss', $title, $author, $isbn, $genre, $copies, $location, $desc, $cover);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────
    public function update(int $id, array $data, array $file = []): bool|string
    {
        if (!empty($data['isbn'])) {
            $chk = $this->db->prepare("SELECT bookID FROM tbl_books WHERE isbn = ? AND bookID != ?");
            $chk->bind_param('si', $data['isbn'], $id);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                return "Another book already uses this ISBN.";
            }
        }

        $chk2 = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM tbl_transaction WHERE bookID = ? AND status = 'borrowed'"
        );
        $chk2->bind_param('i', $id);
        $chk2->execute();
        $borrowed = (int) $chk2->get_result()->fetch_assoc()['cnt'];
        if ((int)$data['copies'] < $borrowed) {
            return "Cannot set copies to {$data['copies']} — {$borrowed} " .
                   ($borrowed === 1 ? 'copy is' : 'copies are') . " currently borrowed.";
        }

        // Handle image upload — only replace if a new one is uploaded
        $existing = $this->getById($id);
        $cover    = $existing['cover_image'] ?? null; // keep old by default

        if (!empty($file['name'])) {
            $result = $this->handleUpload($file);
            if (is_string($result) && !file_exists(self::UPLOAD_DIR . $result)) {
                return $result; // upload error message
            }
            // Delete old image if it existed
            if ($cover) {
                $this->deleteImage($cover);
            }
            $cover = $result;
        }

        // Allow removing the image via a checkbox (value='1' when checked)
        if (isset($data['remove_image']) && $data['remove_image'] === '1') {
            if ($cover) $this->deleteImage($cover);
            $cover = null;
        }

        $title    = trim($data['title']);
        $author   = trim($data['author']);
        $isbn     = !empty($data['isbn'])        ? trim($data['isbn'])        : null;
        $genre    = trim($data['genre']);
        $copies   = (int) $data['copies'];
        $location = !empty($data['location'])    ? trim($data['location'])    : null;
        $desc     = !empty($data['description']) ? trim($data['description']) : null;

        $stmt = $this->db->prepare(
            "UPDATE tbl_books
             SET title=?, author=?, isbn=?, genre=?, copies=?, location=?, description=?, cover_image=?
             WHERE bookID=?"
        );
        $stmt->bind_param('ssssisssi', $title, $author, $isbn, $genre, $copies, $location, $desc, $cover, $id);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── DELETE ─────────────────────────────────────────────────────────────
    public function delete(int $id): bool|string
    {
        $chk = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM tbl_transaction WHERE bookID = ? AND status = 'borrowed'"
        );
        $chk->bind_param('i', $id);
        $chk->execute();
        $cnt = (int) $chk->get_result()->fetch_assoc()['cnt'];
        if ($cnt > 0) {
            return "Cannot delete — {$cnt} " . ($cnt === 1 ? 'copy is' : 'copies are') . " currently borrowed.";
        }

        // Delete cover image file if it exists
        $book = $this->getById($id);
        if ($book && $book['cover_image']) {
            $this->deleteImage($book['cover_image']);
        }

        $stmt = $this->db->prepare("DELETE FROM tbl_books WHERE bookID = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute() ? true : $this->db->error;
    }
}