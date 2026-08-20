<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Setup 2FA</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/twofa.css">
</head>
<body>
<div class="page-wrapper">

    <div class="left-panel">
        <img src="assets/img/logo.gif" alt="LibroTrack" class="brand-icon">
        <h1>LibroTrack</h1>
        <p>Secure your account with two-factor authentication.</p>
    </div>

    <div class="right-panel">
        <div class="login-card twofa-card">

            <div class="twofa-icon">🔐</div>
            <h2>Set Up Two-Factor Authentication</h2>
            <p class="subtitle">Scan the QR code below with your authenticator app to get started.</p>

            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <span class="login-error-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="twofa-steps">
                <div class="twofa-step">
                    <span class="step-num">1</span>
                    <p>Install <strong>Google Authenticator</strong> or <strong>Authy</strong> on your phone</p>
                </div>
                <div class="twofa-step">
                    <span class="step-num">2</span>
                    <p>Open the app and scan this QR code</p>
                </div>
                <div class="twofa-step">
                    <span class="step-num">3</span>
                    <p>Enter the 6-digit code below to confirm setup</p>
                </div>
            </div>

            <div class="qr-wrapper">
                <img src="<?= htmlspecialchars($qrCode) ?>" alt="QR Code" class="qr-code">
            </div>

            <div class="secret-key">
                <p class="secret-label">Can't scan? Enter this key manually:</p>
                <code class="secret-code"><?= htmlspecialchars($secret) ?></code>
            </div>

            <form action="index.php?controller=Auth&action=confirm2fa" method="POST">
                <div class="form-group">
                    <label>Enter 6-Digit Code *</label>
                    <input type="text" name="otp_code" class="otp-input"
                           placeholder="000000" maxlength="6"
                           autocomplete="one-time-code" autofocus required>
                </div>
                <button type="submit" class="login-btn">✅ Confirm &amp; Enable 2FA</button>
            </form>

        </div>
    </div>
</div>
<script src="/librotrack/public/assets/js/ui_icons.js"></script>
</body>
</html>
