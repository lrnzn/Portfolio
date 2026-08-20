<?php

require_once __DIR__ . "/../../config/database.php";

class Profile
{
    private mysqli $db;

    const UPLOAD_DIR    = __DIR__ . "/../../public/assets/img/profiles/";
    const UPLOAD_URL    = "assets/img/profiles/";
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const MAX_SIZE      = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();

        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }
    }

    // ── READ: Get full profile (user + student joined) ─────────────────────
    public function getProfile(int $userID): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.userID, u.name, u.username, u.profile_picture,
                s.studentID, s.fname, s.mname, s.lname, s.nameExt,
                s.studentNumber, s.course, s.email
            FROM tbl_users u
            JOIN tbl_student s ON u.userID = s.userID
            WHERE u.userID = ?
        ");
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── UPDATE: Update student info ────────────────────────────────────────
    public function updateInfo(int $userID, array $data): bool|string
    {
        // Check duplicate email on another student
        $chk = $this->db->prepare(
            "SELECT s.studentID FROM tbl_student s
             WHERE s.email = ? AND s.userID != ?"
        );
        $chk->bind_param('si', $data['email'], $userID);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            return "This email is already used by another account.";
        }

        $fname   = trim($data['fname']);
        $mname   = !empty($data['mname'])   ? trim($data['mname'])   : null;
        $lname   = trim($data['lname']);
        $nameExt = !empty($data['nameExt']) ? trim($data['nameExt']) : null;
        $email   = trim($data['email']);

        // Update student record
        $stmt = $this->db->prepare(
            "UPDATE tbl_student
             SET fname=?, mname=?, lname=?, nameExt=?, email=?
             WHERE userID=?"
        );
        $stmt->bind_param('sssssi', $fname, $mname, $lname, $nameExt, $email, $userID);
        if (!$stmt->execute()) return $this->db->error;

        // Update display name in tbl_users (include mname and nameExt if present)
        $fullName = trim(
            $fname . ' ' .
            ($mname ? $mname . ' ' : '') .
            $lname .
            ($nameExt ? ', ' . $nameExt : '')
        );
        $upd = $this->db->prepare("UPDATE tbl_users SET name=? WHERE userID=?");
        $upd->bind_param('si', $fullName, $userID);
        return $upd->execute() ? true : $this->db->error;
    }

    // ── UPDATE: Change password ────────────────────────────────────────────
    public function changePassword(int $userID, string $current, string $new): bool|string
    {
        // Get current hash
        $stmt = $this->db->prepare("SELECT password FROM tbl_users WHERE userID = ?");
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) return "User not found.";

        if (!password_verify($current, $row['password'])) {
            return "Current password is incorrect.";
        }

        if (strlen($new) < 6) {
            return "New password must be at least 6 characters.";
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);
        $upd  = $this->db->prepare("UPDATE tbl_users SET password=? WHERE userID=?");
        $upd->bind_param('si', $hash, $userID);
        return $upd->execute() ? true : $this->db->error;
    }

    // ── UPDATE: Upload profile picture ─────────────────────────────────────
    public function updateProfilePicture(int $userID, array $file): bool|string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return "Upload failed (error code {$file['error']}).";
        }

        if ($file['size'] > self::MAX_SIZE) {
            return "Image must be 5MB or smaller.";
        }

        if (!in_array($file['type'], self::ALLOWED_TYPES)) {
            return "Only JPG, PNG, GIF, and WEBP images are allowed.";
        }

        // Delete old picture
        $old = $this->db->prepare("SELECT profile_picture FROM tbl_users WHERE userID = ?");
        $old->bind_param('i', $userID);
        $old->execute();
        $existing = $old->get_result()->fetch_assoc()['profile_picture'] ?? null;
        if ($existing && file_exists(self::UPLOAD_DIR . $existing)) {
            unlink(self::UPLOAD_DIR . $existing);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'profile_' . $userID . '_' . uniqid() . '.' . $ext;
        $dest     = self::UPLOAD_DIR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return "Failed to save the uploaded image.";
        }

        $stmt = $this->db->prepare("UPDATE tbl_users SET profile_picture=? WHERE userID=?");
        $stmt->bind_param('si', $filename, $userID);
        return $stmt->execute() ? true : $this->db->error;
    }

    // ── UPDATE: Remove profile picture ─────────────────────────────────────
    public function removeProfilePicture(int $userID): bool|string
    {
        $old = $this->db->prepare("SELECT profile_picture FROM tbl_users WHERE userID = ?");
        $old->bind_param('i', $userID);
        $old->execute();
        $existing = $old->get_result()->fetch_assoc()['profile_picture'] ?? null;

        if ($existing && file_exists(self::UPLOAD_DIR . $existing)) {
            unlink(self::UPLOAD_DIR . $existing);
        }

        $stmt = $this->db->prepare("UPDATE tbl_users SET profile_picture=NULL WHERE userID=?");
        $stmt->bind_param('i', $userID);
        return $stmt->execute() ? true : $this->db->error;
    }
}