<?php

require_once __DIR__ . "/../../config/database.php";

class User
{
    private mysqli $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ── READ: Find a user by username ─────────────────────────────────────
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tbl_users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── READ: Find a user by ID ────────────────────────────────────────────
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM tbl_users WHERE userID = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ── 2FA: Save secret key ──────────────────────────────────────────────
    public function save2FASecret(int $userID, string $secret): void
    {
        $stmt = $this->db->prepare(
            "UPDATE tbl_users SET two_fa_secret = ? WHERE userID = ?"
        );
        $stmt->bind_param('si', $secret, $userID);
        $stmt->execute();
    }

    // ── 2FA: Enable 2FA after first successful verify ─────────────────────
    public function enable2FA(int $userID): void
    {
        $stmt = $this->db->prepare(
            "UPDATE tbl_users SET two_fa_enabled = 1 WHERE userID = ?"
        );
        $stmt->bind_param('i', $userID);
        $stmt->execute();
    }

    // Reset 2FA so the account can scan a fresh QR code again.
    public function reset2FA(int $userID): void
    {
        $stmt = $this->db->prepare(
            "UPDATE tbl_users SET two_fa_secret = NULL, two_fa_enabled = 0 WHERE userID = ?"
        );
        $stmt->bind_param('i', $userID);
        $stmt->execute();
    }

    public function verifyPasswordById(int $userID, string $password): bool
    {
        $user = $this->findById($userID);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['password']) || $user['password'] === $password;
    }

    // ── AUTH: Verify student by username OR student number ────────────────
    public function authenticateStudent(string $identifier, string $password): ?array
    {
        // Try username first
        $user = $this->findByUsername($identifier);

        // If not found by username, try student number
        if (!$user) {
            $stmt = $this->db->prepare(
                "SELECT u.* FROM tbl_users u
                 JOIN tbl_student s ON u.userID = s.userID
                 WHERE s.studentNumber = ? AND u.role = 'student'
                 LIMIT 1"
            );
            $stmt->bind_param('s', $identifier);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $user = $row ?: null;
        }

        if (!$user) return null;

        // Must be a student account
        if ($user['role'] !== 'student') return null;

        if (password_verify($password, $user['password'])) {
            return $user;
        }

        // Fallback plain-text match for dev accounts
        if ($user['password'] === $password) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE tbl_users SET password = ? WHERE userID = ?");
            $stmt->bind_param('si', $newHash, $user['userID']);
            $stmt->execute();
            return $user;
        }

        return null;
    }

    // ── AUTH: Verify credentials, return user array or null ───────────────
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return null;
        }

        // Standard bcrypt check
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        // Fallback: plain-text match (for dev/seeded accounts not yet hashed)
        // If matched, re-hash and update the stored password automatically
        if ($user['password'] === $password) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE tbl_users SET password = ? WHERE userID = ?");
            $stmt->bind_param('si', $newHash, $user['userID']);
            $stmt->execute();
            return $user;
        }

        return null;
    }
}
