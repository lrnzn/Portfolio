<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Verify 2FA</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/twofa.css">
</head>
<body>
<div class="page-wrapper">

    <div class="left-panel">
        <img src="assets/img/logo.gif" alt="LibroTrack" class="brand-icon">
        <h1>LibroTrack</h1>
        <p>Your campus library, organized and at your fingertips.</p>
    </div>

    <div class="right-panel">
        <div class="login-card twofa-card">

            <div class="twofa-icon">📱</div>
            <h2>Two-Factor Authentication</h2>
            <p class="subtitle">Open your authenticator app and enter the 6-digit code.</p>

            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <span class="login-error-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="index.php?controller=Auth&action=processVerify" method="POST">
                <div class="form-group">
                    <label>6-Digit Code *</label>
                    <input type="text" name="otp_code" class="otp-input"
                           placeholder="000000" maxlength="6"
                           autocomplete="one-time-code" autofocus required>
                </div>
                <button type="submit" class="login-btn">🔓 Verify &amp; Sign In</button>
            </form>

            <div class="twofa-hint">
                <p>🔒 Code refreshes every 30 seconds.</p>
                <p>Having trouble? Contact your system administrator.</p>
            </div>

            <details class="demo-reset-2fa">
                <summary>Demo-only: scan a new QR code</summary>
                <div class="demo-reset-box">
                    <p>
                        This resets 2FA for the pending admin account so a new QR code can be scanned.
                        Use this only during system demonstration.
                    </p>
                    <form action="index.php?controller=Auth&action=demoReset2fa" method="POST"
                          onsubmit="return confirm('Reset 2FA and show a new QR code?');">
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="password" placeholder="Enter admin password" required>
                        </div>
                        <button type="submit" class="btn-cancel demo-reset-btn">Reset 2FA QR</button>
                    </form>
                </div>
            </details>

            <a href="index.php?controller=Auth&action=login"
               style="display:block;text-align:center;margin-top:1rem;font-size:0.85rem;color:var(--text-muted);">
               ← Back to Login
            </a>

        </div>
    </div>
</div>
<script src="/librotrack/public/assets/js/ui_icons.js"></script>
</body>
</html>
