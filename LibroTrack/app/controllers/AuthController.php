<?php

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/Student.php";

use PragmaRX\Google2FAQRCode\Google2FA;

class AuthController
{
    private User $user;
    private Google2FA $google2fa;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->user     = new User();
        $this->google2fa = new Google2FA();
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    // ── SHOW: Login page ──────────────────────────────────────────────────
    public function login(): void
    {
        require __DIR__ . "/../views/login.php";
    }

    // ── HANDLE: Login form submission ─────────────────────────────────────
    public function authenticate(): void
    {
        $role     = $_POST['role'] ?? 'librarian';
        $password = $_POST['password'] ?? '';

        $username = $role === 'student'
            ? trim($_POST['student_id'] ?? '')
            : trim($_POST['username']   ?? '');

        if (empty($username) || empty($password)) {
            $this->redirect("index.php?controller=Auth&action=login&error=" .
                urlencode("Please fill in all fields."));
        }

        if ($role === 'student') {
            $user = $this->user->authenticateStudent($username, $password);
        } else {
            $user = $this->user->authenticate($username, $password);
        }

        if (!$user) {
            $this->redirect("index.php?controller=Auth&action=login&error=" .
                urlencode("Invalid username or password."));
        }

        // Role mismatch check
        $expectedRole = $role === 'student' ? 'student' : 'admin';
        if ($user['role'] !== $expectedRole) {
            $this->redirect("index.php?controller=Auth&action=login&error=" .
                urlencode("Invalid username or password."));
        }

        // Students skip 2FA — log in directly
        if ($user['role'] === 'student') {
            session_start();
            $_SESSION['userID']   = $user['userID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['name']     = $user['name'];
            $this->redirect("index.php?controller=Student&action=index");
        }

        // Admin — store as pending until 2FA is verified
        $_SESSION['pending_userID']   = $user['userID'];
        $_SESSION['pending_username'] = $user['username'];
        $_SESSION['pending_name']     = $user['name'];
        $_SESSION['pending_role']     = $user['role'];

        // Has 2FA already been set up?
        if (!empty($user['two_fa_enabled']) && $user['two_fa_enabled'] == 1) {
            $this->redirect("index.php?controller=Auth&action=verify2fa");
        } else {
            $this->redirect("index.php?controller=Auth&action=setup2fa");
        }
    }

    // ── SHOW: Setup 2FA (first time) ──────────────────────────────────────
    public function setup2fa(): void
    {
        if (!isset($_SESSION['pending_userID'])) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        $userID = (int)$_SESSION['pending_userID'];
        $user   = $this->user->findById($userID);

        if (!$user) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        // Generate and save secret if not yet set
        if (!empty($user['two_fa_secret'])) {
            $secret = $user['two_fa_secret'];
        } else {
            $secret = $this->google2fa->generateSecretKey();
            $this->user->save2FASecret($userID, $secret);
        }

        // Build QR code URL using free external API (no local library needed)
        $qrUrl  = $this->google2fa->getQRCodeUrl(
            "LibroTrack",
            $user['username'],
            $secret
        );
        $qrCode = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($qrUrl);

        require __DIR__ . "/../views/setup_2fa.php";
    }

    // ── HANDLE: Confirm 2FA setup (verify first code) ─────────────────────
    public function confirm2fa(): void
    {
        if (!isset($_SESSION['pending_userID'])) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        $userID = (int)$_SESSION['pending_userID'];
        $code   = trim($_POST['otp_code'] ?? '');
        $user   = $this->user->findById($userID);

        $valid = $this->google2fa->verifyKey($user['two_fa_secret'], $code);

        if ($valid) {
            // Mark 2FA as enabled
            $this->user->enable2FA($userID);

            // Complete login
            $_SESSION['userID']   = $userID;
            $_SESSION['username'] = $_SESSION['pending_username'];
            $_SESSION['role']     = $_SESSION['pending_role'];
            $_SESSION['name']     = $_SESSION['pending_name'];
            unset($_SESSION['pending_userID'], $_SESSION['pending_username'],
                  $_SESSION['pending_role'],  $_SESSION['pending_name']);

            $this->redirect("index.php?controller=Dashboard&action=index");
        }

        $error = "Invalid code. Please try again.";
        // Regenerate QR for display
        $qrUrl  = $this->google2fa->getQRCodeUrl("LibroTrack", $user['username'], $user['two_fa_secret']);
        $qrCode = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($qrUrl);
        $secret = $user['two_fa_secret'];
        require __DIR__ . "/../views/setup_2fa.php";
    }

    // ── SHOW: Verify 2FA (every login after setup) ────────────────────────
    public function verify2fa(): void
    {
        if (!isset($_SESSION['pending_userID'])) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        $error = $_GET['error'] ?? '';
        require __DIR__ . "/../views/verify_2fa.php";
    }

    // ── HANDLE: Verify 2FA code ────────────────────────────────────────────
    public function processVerify(): void
    {
        if (!isset($_SESSION['pending_userID'])) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        $userID = (int)$_SESSION['pending_userID'];
        $code   = trim($_POST['otp_code'] ?? '');
        $user   = $this->user->findById($userID);

        $valid = $this->google2fa->verifyKey($user['two_fa_secret'], $code);

        if ($valid) {
            $_SESSION['userID']   = $userID;
            $_SESSION['username'] = $_SESSION['pending_username'];
            $_SESSION['role']     = $_SESSION['pending_role'];
            $_SESSION['name']     = $_SESSION['pending_name'];
            unset($_SESSION['pending_userID'], $_SESSION['pending_username'],
                  $_SESSION['pending_role'],  $_SESSION['pending_name']);

            $this->redirect("index.php?controller=Dashboard&action=index");
        }

        $this->redirect("index.php?controller=Auth&action=verify2fa&error=" .
            urlencode("Invalid code. Please try again."));
    }

    // ── HANDLE: Reset 2FA for the currently logged-in admin ─────────────────
    public function reset2fa(): void
    {
        if (!isset($_SESSION['userID']) || ($_SESSION['role'] ?? '') !== 'admin') {
            $this->redirect("index.php?controller=Auth&action=login&error=" .
                urlencode("Please log in to reset 2FA."));
        }

        $userID = (int)$_SESSION['userID'];
        $user   = $this->user->findById($userID);

        if (!$user) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        $this->user->reset2FA($userID);

        $_SESSION['pending_userID']   = $userID;
        $_SESSION['pending_username'] = $user['username'];
        $_SESSION['pending_name']     = $user['name'];
        $_SESSION['pending_role']     = $user['role'];

        $this->redirect("index.php?controller=Auth&action=setup2fa");
    }

    // ── DEMO: Reset pending admin 2FA from the verification screen ──────────
    public function demoReset2fa(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_userID'])) {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        if (($_SESSION['pending_role'] ?? '') !== 'admin') {
            $this->redirect("index.php?controller=Auth&action=login");
        }

        $userID   = (int)$_SESSION['pending_userID'];
        $password = $_POST['password'] ?? '';

        if (!$this->user->verifyPasswordById($userID, $password)) {
            $this->redirect("index.php?controller=Auth&action=verify2fa&error=" .
                urlencode("Password confirmation failed. 2FA was not reset."));
        }

        $this->user->reset2FA($userID);
        $this->redirect("index.php?controller=Auth&action=setup2fa");
    }

    // ── SHOW: Register page ───────────────────────────────────────────────
    public function register(): void
    {
        require __DIR__ . "/../views/signup.php";
    }

    // ── HANDLE: Signup form submission ────────────────────────────────────
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Auth&action=register");
        }

        require_once __DIR__ . "/../models/Student.php";

        $required = ['fname', 'lname', 'studentNumber', 'course', 'email', 'username', 'password', 'confirm_password'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $this->redirect("index.php?controller=Auth&action=register&error=" .
                    urlencode("Please fill in all required fields."));
            }
        }

        if ($_POST['password'] !== $_POST['confirm_password']) {
            $this->redirect("index.php?controller=Auth&action=register&error=" .
                urlencode("Passwords do not match."));
        }

        $student = new Student();
        $data    = [
            'fname'         => $_POST['fname'],
            'mname'         => $_POST['mname']         ?? '',
            'lname'         => $_POST['lname'],
            'nameExt'       => $_POST['nameExt']       ?? '',
            'studentNumber' => $_POST['studentNumber'],
            'course'        => $_POST['course'],
            'email'         => $_POST['email'],
        ];

        $result = $student->createWithUsername($data, $_POST['username'], $_POST['password']);

        if ($result === true) {
            $this->redirect("index.php?controller=Auth&action=login&error=" .
                urlencode("Account created successfully! You can now sign in."));
        } else {
            $this->redirect("index.php?controller=Auth&action=register&error=" .
                urlencode($result));
        }
    }

    // ── HANDLE: Logout ────────────────────────────────────────────────────
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        $this->redirect("index.php?controller=Auth&action=login");
    }
}
