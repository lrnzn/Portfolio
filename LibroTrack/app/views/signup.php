<?php
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Sign Up</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="page-wrapper">

    <div class="left-panel">
        <img src="assets/img/logo.gif" alt="LibroTrack Logo" class="brand-icon">
        <h1>LibroTrack</h1>
        <p>Manage your library with ease. Track books, borrowers, and transactions in one place.</p>
    </div>

    <div class="right-panel">
        <div class="login-card">

            <h2>Create Account</h2>
            <p class="subtitle">Sign up to access the library system</p>

            <?php if (!empty($error)): ?>
                <p class="login-error">❌ <?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form action="index.php?controller=Auth&action=store" method="POST">

                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="fname" placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="mname" placeholder="Enter your middle name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" placeholder="Enter your last name" required>
                    </div>
                    <div class="form-group">
                        <label>Ext.</label>
                        <input type="text" name="nameExt" placeholder="e.g. Jr.">
                    </div>
                </div>

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="studentNumber" placeholder="e.g. ABC01234567" required>
                </div>

                <div class="form-group">
                    <label>Course</label>
                    <select name="course" required>
                        <option value="" disabled selected hidden>Select course</option>
                        <option>BSIT</option>
                        <option>BSIS</option>
                        <option>BIT</option>
                        <option>BSINDTECH</option>
                        <option>BTVTED</option>
                        <option>BSECE</option>
                        <option>BSCPE</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Choose a username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" onclick="toggleSignupPassword()">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm-password"
                               placeholder="Confirm password" required>
                    </div>
                </div>

                <button type="submit" class="login-btn">Sign Up</button>

                <p class="register-prompt">
                    Already have an account?
                    <a href="index.php?controller=Auth&action=login">Sign in here</a>
                </p>

            </form>
        </div>
    </div>

</div>

<script src="/librotrack/public/assets/js/ui_icons.js"></script>
<script src="assets/js/signup.js"></script>

</body>
</html>
