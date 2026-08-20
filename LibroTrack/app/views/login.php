<?php
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="page-wrapper">

    <!-- Left Panel -->
    <div class="left-panel">
        <img src="assets/img/logo.gif" alt="LibroTrack Logo" class="brand-icon">
        <h1>LibroTrack</h1>
        <p>Your campus library,<br>organized and at your fingertips.</p>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
        <div class="login-card">

            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to continue to LibroTrack</p>

            <!-- Role Selector -->
            <div class="role-selector">
                <button class="role-btn active" id="btn-librarian" onclick="selectRole('librarian')">🏛️ Librarian</button>
                <button class="role-btn" id="btn-student" onclick="selectRole('student')">🎓 Student</button>
            </div>

            <!-- Login Form -->
            <form onsubmit="handleLogin(event)">
                <input type="hidden" name="role" id="role-input" value="librarian">

                <div id="librarian-fields" class="form-group">
                    <label for="username">USERNAME</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="off">
                </div>

                <div id="student-fields" class="form-group" style="display:none;">
                    <label for="student-id">STUDENT ID</label>
                    <input type="text" id="student-id" name="student_id" placeholder="Enter student ID e.g. ABC01234567" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">👁</button>
                    </div>
                </div>

                <div class="form-footer">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <?php if (!empty($error)): ?>
                <div class="login-error">
                    <span class="login-error-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <p id="register-prompt" class="register-prompt" style="display:none;">
                Don't have an account? <a href="index.php?controller=Auth&action=register">Register here</a>
            </p>

        </div>
    </div>

</div>
<script src="/librotrack/public/assets/js/ui_icons.js"></script>
<script src="assets/js/login.js"></script>
</body>
</html>
