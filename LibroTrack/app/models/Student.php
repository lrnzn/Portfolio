<?php

require_once __DIR__ . "/../../config/database.php";

class Student
{
    private mysqli $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ── READ: All students with borrow counts ──────────────────────────────
    public function getAll(string $search = '', string $course = '', string $status = ''): array
    {
        $conditions = ["1=1"];
        $params     = [];
        $types      = '';

        if ($search !== '') {
            $conditions[] = "(CONCAT(s.fname,' ',s.lname) LIKE ? OR s.studentNumber LIKE ? OR s.email LIKE ?)";
            $like = "%{$search}%";
            array_push($params, $like, $like, $like);
            $types .= 'sss';
        }

        if ($course !== '') {
            $conditions[] = "s.course = ?";
            $params[]      = $course;
            $types        .= 's';
        }

        $where = implode(' AND ', $conditions);

        $havingClause = '';
        if ($status === 'active') {
            $havingClause = 'HAVING active_borrows > 0';
        } elseif ($status === 'overdue') {
            $havingClause = 'HAVING overdue_count > 0';
        } elseif ($status === 'clean') {
            $havingClause = 'HAVING active_borrows = 0';
        }

        $sql = "
            SELECT
                s.studentID,
                s.userID,
                s.fname,
                s.mname,
                s.lname,
                s.nameExt,
                s.studentNumber,
                s.course,
                s.email,
                CONCAT(s.fname, ' ', s.lname) AS fullName,
                COUNT(CASE WHEN t.status = 'borrowed' THEN 1 END) AS active_borrows,
                COUNT(CASE WHEN t.status = 'overdue'
                           OR  (t.status = 'borrowed' AND t.dueDate < CURDATE()) THEN 1 END) AS overdue_count,
                COUNT(t.transactionID) AS total_borrowed
            FROM tbl_student s
            LEFT JOIN tbl_transaction t ON s.studentID = t.studentID
            WHERE {$where}
            GROUP BY s.studentID
            {$havingClause}
            ORDER BY s.lname, s.fname
        ";

        $stmt = $this->db->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: Single student by ID ─────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,
                CONCAT(s.fname, ' ', s.lname) AS fullName,
                COUNT(CASE WHEN t.status = 'borrowed' THEN 1 END) AS active_borrows,
                COUNT(CASE WHEN t.status = 'overdue'
                           OR  (t.status = 'borrowed' AND t.dueDate < CURDATE()) THEN 1 END) AS overdue_count,
                COUNT(t.transactionID) AS total_borrowed,
                u.profile_picture
            FROM tbl_student s
            LEFT JOIN tbl_transaction t ON s.studentID = t.studentID
            LEFT JOIN tbl_users u ON s.userID = u.userID
            WHERE s.studentID = ?
            GROUP BY s.studentID
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Current active borrows for a student (for view modal) ────────
    public function getActiveBorrows(int $studentID): array
    {
        $stmt = $this->db->prepare("
            SELECT t.transactionID, b.title, b.author, t.borrowDate, t.dueDate, t.status,
                CASE WHEN t.dueDate < CURDATE() AND t.status = 'borrowed'
                     THEN DATEDIFF(CURDATE(), t.dueDate) ELSE 0 END AS daysOverdue
            FROM tbl_transaction t
            JOIN tbl_books b ON t.bookID = b.bookID
            WHERE t.studentID = ? AND t.status IN ('borrowed', 'overdue')
            ORDER BY t.dueDate ASC
        ");
        $stmt->bind_param('i', $studentID);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ── READ: All distinct courses ─────────────────────────────────────────
    public function getCourses(): array
    {
        $result = $this->db->query("SELECT DISTINCT course FROM tbl_student ORDER BY course");
        return array_column($result->fetch_all(MYSQLI_ASSOC), 'course');
    }

    // ── READ: Stats ────────────────────────────────────────────────────────
    public function getStats(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(DISTINCT s.studentID) AS total_borrowers,
                COUNT(CASE WHEN t.status = 'borrowed' THEN 1 END) AS currently_borrowing,
                COUNT(DISTINCT CASE WHEN t.status = 'overdue'
                    OR (t.status = 'borrowed' AND t.dueDate < CURDATE())
                    THEN s.studentID END) AS with_overdue
            FROM tbl_student s
            LEFT JOIN tbl_transaction t ON s.studentID = t.studentID
        ")->fetch_assoc();
        return $row;
    }

    // ── CREATE ─────────────────────────────────────────────────────────────
    public function create(array $data): bool|string
    {
        // Check duplicate student number
        $chk = $this->db->prepare("SELECT studentID FROM tbl_student WHERE studentNumber = ?");
        $chk->bind_param('s', $data['studentNumber']);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            return "A student with this student number already exists.";
        }

        // Check duplicate email
        $chk2 = $this->db->prepare("SELECT studentID FROM tbl_student WHERE email = ?");
        $chk2->bind_param('s', $data['email']);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            return "A student with this email already exists.";
        }

        // Create user account first
        $name     = trim($data['fname'] . ' ' . $data['lname']);
        $username = strtolower(trim($data['fname'])) . '.' . strtolower(trim($data['lname']));
        $password = password_hash($data['studentNumber'], PASSWORD_BCRYPT);

        $userStmt = $this->db->prepare(
            "INSERT INTO tbl_users (name, username, password, role) VALUES (?, ?, ?, 'student')"
        );
        $userStmt->bind_param('sss', $name, $username, $password);
        if (!$userStmt->execute()) {
            return "Failed to create user account: " . $this->db->error;
        }
        $userID = $this->db->insert_id;

        // Create student record
        $fname  = trim($data['fname']);
        $mname  = !empty($data['mname'])   ? trim($data['mname'])   : null;
        $lname  = trim($data['lname']);
        $ext    = !empty($data['nameExt']) ? trim($data['nameExt']) : null;
        $stuNum = trim($data['studentNumber']);
        $course = trim($data['course']);
        $email  = trim($data['email']);

        $stmt = $this->db->prepare(
            "INSERT INTO tbl_student (userID, fname, mname, lname, nameExt, studentNumber, course, email)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isssssss', $userID, $fname, $mname, $lname, $ext, $stuNum, $course, $email);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── CREATE: With custom username (used by signup form) ───────────────
    public function createWithUsername(array $data, string $username, string $password): bool|string
    {
        // Check duplicate student number
        $chk = $this->db->prepare("SELECT studentID FROM tbl_student WHERE studentNumber = ?");
        $chk->bind_param('s', $data['studentNumber']);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            return "A student with this student number already exists.";
        }

        // Check duplicate email
        $chk2 = $this->db->prepare("SELECT studentID FROM tbl_student WHERE email = ?");
        $chk2->bind_param('s', $data['email']);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            return "A student with this email already exists.";
        }

        // Check duplicate username
        $chk3 = $this->db->prepare("SELECT userID FROM tbl_users WHERE username = ?");
        $chk3->bind_param('s', $username);
        $chk3->execute();
        if ($chk3->get_result()->num_rows > 0) {
            return "This username is already taken.";
        }

        $name     = trim($data['fname'] . ' ' . $data['lname']);
        $hash     = password_hash($password, PASSWORD_BCRYPT);

        $userStmt = $this->db->prepare(
            "INSERT INTO tbl_users (name, username, password, role) VALUES (?, ?, ?, 'student')"
        );
        $userStmt->bind_param('sss', $name, $username, $hash);
        if (!$userStmt->execute()) {
            return "Failed to create user account: " . $this->db->error;
        }
        $userID = $this->db->insert_id;

        $fname  = trim($data['fname']);
        $mname  = !empty($data['mname'])   ? trim($data['mname'])   : null;
        $lname  = trim($data['lname']);
        $ext    = !empty($data['nameExt']) ? trim($data['nameExt']) : null;
        $stuNum = trim($data['studentNumber']);
        $course = trim($data['course']);
        $email  = trim($data['email']);

        $stmt = $this->db->prepare(
            "INSERT INTO tbl_student (userID, fname, mname, lname, nameExt, studentNumber, course, email)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isssssss', $userID, $fname, $mname, $lname, $ext, $stuNum, $course, $email);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────
    public function update(int $id, array $data): bool|string
    {
        // Check duplicate student number on another record
        $chk = $this->db->prepare("SELECT studentID FROM tbl_student WHERE studentNumber = ? AND studentID != ?");
        $chk->bind_param('si', $data['studentNumber'], $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            return "Another student already has this student number.";
        }

        // Check duplicate email on another record
        $chk2 = $this->db->prepare("SELECT studentID FROM tbl_student WHERE email = ? AND studentID != ?");
        $chk2->bind_param('si', $data['email'], $id);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            return "Another student already has this email.";
        }

        $fname  = trim($data['fname']);
        $mname  = !empty($data['mname'])   ? trim($data['mname'])   : null;
        $lname  = trim($data['lname']);
        $ext    = !empty($data['nameExt']) ? trim($data['nameExt']) : null;
        $stuNum = trim($data['studentNumber']);
        $course = trim($data['course']);
        $email  = trim($data['email']);

        $stmt = $this->db->prepare(
            "UPDATE tbl_student
             SET fname=?, mname=?, lname=?, nameExt=?, studentNumber=?, course=?, email=?
             WHERE studentID=?"
        );
        $stmt->bind_param('sssssssi', $fname, $mname, $lname, $ext, $stuNum, $course, $email, $id);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── DELETE ─────────────────────────────────────────────────────────────
    public function delete(int $id): bool|string
    {
        // Block deletion if student has active borrows
        $chk = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM tbl_transaction WHERE studentID = ? AND status IN ('borrowed','overdue')"
        );
        $chk->bind_param('i', $id);
        $chk->execute();
        $cnt = (int) $chk->get_result()->fetch_assoc()['cnt'];
        if ($cnt > 0) {
            return "Cannot delete — this student has {$cnt} active borrow" . ($cnt === 1 ? '' : 's') . ".";
        }

        // Get userID so we can delete the user account too
        $get = $this->db->prepare("SELECT userID FROM tbl_student WHERE studentID = ?");
        $get->bind_param('i', $id);
        $get->execute();
        $row = $get->get_result()->fetch_assoc();

        // Deleting the user cascades to student (FK ON DELETE CASCADE)
        if ($row) {
            $del = $this->db->prepare("DELETE FROM tbl_users WHERE userID = ?");
            $del->bind_param('i', $row['userID']);
            return $del->execute() ? true : $this->db->error;
        }

        $stmt = $this->db->prepare("DELETE FROM tbl_student WHERE studentID = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute() ? true : $this->db->error;
    }
}