<?php

require_once __DIR__ . "/../models/Profile.php";

class ProfileController
{
    private Profile $profile;
    private int $userID;

    public function __construct()
    {
        session_start();
        if (!isset($_SESSION['userID'])) {
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }
        $this->profile = new Profile();
        $this->userID  = (int)$_SESSION['userID'];
    }

    private function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    // ── READ: Profile page ─────────────────────────────────────────────────
    public function index(): void
    {
        $profile    = $this->profile->getProfile($this->userID);
        $flash      = $_GET['flash']      ?? '';
        $flash_type = $_GET['flash_type'] ?? 'success';

        // Pre-derive variables so the view needs zero logic
        $fname  = $profile['fname']  ?? 'Student';
        $picUrl = !empty($profile['profile_picture'])
            ? 'assets/img/profiles/' . htmlspecialchars($profile['profile_picture'])
            : null;

        require __DIR__ . "/../views/client/profile.php";
    }

    // ── UPDATE: Save profile info ──────────────────────────────────────────
    public function updateInfo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Profile&action=index");
        }

        $result = $this->profile->updateInfo($this->userID, $_POST);

        if ($result === true) {
            // Update session name
            $_SESSION['name'] = trim($_POST['fname'] . ' ' . $_POST['lname']);
            $this->redirect("index.php?controller=Profile&action=index&flash=Profile+updated+successfully.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Profile&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── UPDATE: Change password ────────────────────────────────────────────
    public function changePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Profile&action=index");
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            $this->redirect("index.php?controller=Profile&action=index&flash=New+passwords+do+not+match.&flash_type=error");
        }

        $result = $this->profile->changePassword($this->userID, $current, $new);

        if ($result === true) {
            $this->redirect("index.php?controller=Profile&action=index&flash=Password+changed+successfully.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Profile&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── UPDATE: Upload profile picture ─────────────────────────────────────
    public function uploadPicture(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?controller=Profile&action=index");
        }

        $file = $_FILES['profile_picture'] ?? [];

        if (empty($file['name'])) {
            $this->redirect("index.php?controller=Profile&action=index&flash=No+file+selected.&flash_type=error");
        }

        $result = $this->profile->updateProfilePicture($this->userID, $file);

        if ($result === true) {
            $this->redirect("index.php?controller=Profile&action=index&flash=Profile+picture+updated.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Profile&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }

    // ── UPDATE: Remove profile picture ─────────────────────────────────────
    public function removePicture(): void
    {
        $result = $this->profile->removeProfilePicture($this->userID);

        if ($result === true) {
            $this->redirect("index.php?controller=Profile&action=index&flash=Profile+picture+removed.&flash_type=success");
        } else {
            $this->redirect("index.php?controller=Profile&action=index&flash=" . urlencode($result) . "&flash_type=error");
        }
    }
}
