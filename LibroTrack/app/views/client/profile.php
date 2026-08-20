<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — My Profile</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>

<?php if (!empty($flash)): ?>
<div class="toast toast--<?= htmlspecialchars($flash_type) ?>">
    <?= $flash_type === 'success' ? '✅' : '❌' ?>
    <?= htmlspecialchars($flash) ?>
</div>
<?php endif; ?>

<nav class="navbar">
    <div class="nav-brand">
        <img src="assets/img/logo.gif" alt="LibroTrack" class="brand-icon">
        <span class="nav-title">LibroTrack</span>
        <span class="nav-role-badge nav-role-badge--student">Student</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?controller=Student&action=index">Home</a></li>
        <li><a href="index.php?controller=Student&action=catalog">Browse Books</a></li>
        <li><a href="index.php?controller=Student&action=borrowed">My Borrowed</a></li>
        <li><a href="index.php?controller=Student&action=history">My History</a></li>
        <li><a href="index.php?controller=Profile&action=index" class="active">Profile</a></li>
    </ul>
    <div class="nav-user">
        <span class="nav-avatar">
            <?php if ($picUrl): ?>
                <img src="<?= $picUrl ?>" alt="Profile" class="nav-profile-pic">
            <?php else: ?>
                🎓
            <?php endif; ?>
        </span>
        <span class="nav-username"><?= htmlspecialchars($fname) ?></span>
        <a href="index.php?controller=Auth&action=logout" class="nav-logout">Logout</a>
    </div>
</nav>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1>My Profile</h1>
            <p class="page-subtitle">Manage your account information and settings.</p>
        </div>
    </div>

    <div class="profile-layout">

        <!-- LEFT: Profile Picture + Info Summary -->
        <div class="profile-sidebar">
            <div class="card profile-pic-card">
                <div class="profile-avatar">
                    <?php if ($picUrl): ?>
                        <img src="<?= $picUrl ?>" alt="Profile Picture" id="profile-img">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder" id="profile-placeholder">
                            <?= strtoupper(substr($profile['fname'] ?? 'S', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h3 class="profile-name"><?= htmlspecialchars(
                    trim(
                        $profile['fname'] . ' ' .
                        ($profile['mname'] ? $profile['mname'] . ' ' : '') .
                        $profile['lname'] .
                        ($profile['nameExt'] ? ', ' . $profile['nameExt'] : '')
                    )
                ) ?></h3>
                <p class="profile-meta"><?= htmlspecialchars($profile['studentNumber'] ?? '') ?></p>
                <p class="profile-meta"><?= htmlspecialchars($profile['course'] ?? '') ?></p>

                <!-- Upload Picture Form -->
                <form action="index.php?controller=Profile&action=uploadPicture"
                      method="POST" enctype="multipart/form-data" class="pic-form">
                    <label class="btn-upload" for="pic-input">📷 Change Photo</label>
                    <input type="file" id="pic-input" name="profile_picture"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           onchange="this.form.submit()">
                    <small>Max 5MB — JPG, PNG, GIF, WEBP</small>
                </form>

                <?php if ($picUrl): ?>
                <form action="index.php?controller=Profile&action=removePicture"
                      method="POST" style="margin-top:0.5rem;">
                    <button type="submit" class="btn-remove-pic">🗑️ Remove Photo</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Edit Forms -->
        <div class="profile-forms">

            <!-- Edit Info -->
            <div class="card">
                <div class="card-head"><h2>Personal Information</h2></div>
                <div class="modal-body" style="padding:1.25rem;">
                    <form action="index.php?controller=Profile&action=updateInfo"
                          method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="fname"
                                       value="<?= htmlspecialchars($profile['fname'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="mname"
                                       value="<?= htmlspecialchars($profile['mname'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="lname"
                                       value="<?= htmlspecialchars($profile['lname'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Name Extension</label>
                                <input type="text" name="nameExt"
                                       value="<?= htmlspecialchars($profile['nameExt'] ?? '') ?>"
                                       placeholder="e.g. Jr., Sr.">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Student Number</label>
                            <input type="text" value="<?= htmlspecialchars($profile['studentNumber'] ?? '') ?>"
                                   disabled style="background:var(--cream);color:var(--text-muted);">
                            <small style="color:var(--text-muted);font-size:0.78rem;">Student number cannot be changed.</small>
                        </div>
                        <div class="form-group">
                            <label>Course</label>
                            <input type="text" value="<?= htmlspecialchars($profile['course'] ?? '') ?>"
                                   disabled style="background:var(--cream);color:var(--text-muted);">
                            <small style="color:var(--text-muted);font-size:0.78rem;">Contact the librarian to update your course.</small>
                        </div>
                        <div style="text-align:right;margin-top:0.5rem;">
                            <button type="submit" class="btn-primary">💾 Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-head"><h2>Change Password</h2></div>
                <div class="modal-body" style="padding:1.25rem;">
                    <form action="index.php?controller=Profile&action=changePassword"
                          method="POST">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password"
                                   placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password"
                                   placeholder="At least 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password"
                                   placeholder="Repeat new password" required>
                        </div>
                        <div style="text-align:right;margin-top:0.5rem;">
                            <button type="submit" class="btn-primary">🔒 Change Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/profile.js"></script>
</body>
</html>
