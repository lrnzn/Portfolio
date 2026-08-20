<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <img src="assets/img/logo.gif" alt="LibroTrack" class="brand-icon">
        <span class="nav-title">LibroTrack</span>
        <span class="nav-role-badge nav-role-badge--student">Student</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?controller=Student&action=index" class="active">Home</a></li>
        <li><a href="index.php?controller=Student&action=catalog">Browse Books</a></li>
        <li><a href="index.php?controller=Student&action=borrowed">My Borrowed</a></li>
        <li><a href="index.php?controller=Student&action=history">My History</a></li>
        <li><a href="index.php?controller=Profile&action=index">Profile</a></li>
    </ul>
    <div class="nav-user">
        <span class="nav-avatar">
            <?php if ($profilePicUrl): ?>
                <img src="<?= htmlspecialchars($profilePicUrl) ?>" alt="Profile" class="nav-profile-pic">
            <?php else: ?>
                🎓
            <?php endif; ?>
        </span>
        <span class="nav-username"><?= htmlspecialchars($student['fname']) ?></span>
        <a href="index.php?controller=Auth&action=logout" class="nav-logout">Logout</a>
    </div>
</nav>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1>Hello, <?= htmlspecialchars($student['fname']) ?>! 👋</h1>
            <p class="page-subtitle">Here's a summary of your library activity.</p>
        </div>
        <div class="header-date"><?= date('F d, Y') ?></div>
    </div>

    <!-- Stats -->
    <div class="stats-grid stats-grid--student">
        <div class="stat-card">
            <div class="stat-icon">📤</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['active_borrows'] ?></span>
                <span class="stat-label">Currently Borrowed</span>
            </div>
        </div>
        <div class="stat-card <?= $stats['overdue_count'] > 0 ? 'stat-card--warning' : '' ?>">
            <div class="stat-icon">⚠️</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['overdue_count'] ?></span>
                <span class="stat-label">Overdue</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['total_borrowed'] ?></span>
                <span class="stat-label">Total Borrowed (All Time)</span>
            </div>
        </div>
    </div>

    <div class="content-grid">

        <!-- Active Borrows -->
        <div class="card">
            <div class="card-head">
                <h2>My Borrowed Books</h2>
                <a href="index.php?controller=Student&action=borrowed" class="card-link">View all →</a>
            </div>

            <?php if ((int)$stats['overdue_count'] > 0): ?>
            <div class="overdue-warning" style="margin-bottom:1rem;">
                ⚠️ You have <strong><?= $stats['overdue_count'] ?> overdue book<?= $stats['overdue_count'] != 1 ? 's' : '' ?></strong>.
                Please return <?= $stats['overdue_count'] == 1 ? 'it' : 'them' ?> as soon as possible to avoid additional penalties.
            </div>
            <?php endif; ?>

            <table class="data-table">
                <thead>
                    <tr><th>Book Title</th><th>Author</th><th>Due Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($activeBorrows)): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">No active borrows.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activeBorrows as $b):
                            $isOverdue  = (int)$b['daysOverdue'] > 0;
                            $badgeClass = $isOverdue ? 'badge--overdue' : 'badge--borrowed';
                            $label      = $isOverdue ? "Overdue ({$b['daysOverdue']}d)" : 'Borrowed';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
                            <td><?= htmlspecialchars($b['author']) ?></td>
                            <td><?= date('M d, Y', strtotime($b['dueDate'])) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $label ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Right Column -->
        <div class="right-col">
            <div class="card">
                <div class="card-head"><h2>Quick Actions</h2></div>
                <div class="quick-actions">
                    <a href="index.php?controller=Student&action=catalog" class="action-btn">🔍 Browse Books</a>
                    <a href="index.php?controller=Student&action=borrowed" class="action-btn">📖 My Books</a>
                    <a href="index.php?controller=Student&action=history" class="action-btn">🕘 My History</a>
                </div>
            </div>

            <div class="card" style="text-align:center;padding:1.5rem;">
                <p style="font-size:1.5rem;margin-bottom:0.5rem;">
                    <?= $stats['slots_remaining'] ?> / 3
                </p>
                <p style="color:var(--text-muted);font-size:0.875rem;">Borrow slots remaining</p>
                <?php if ($stats['slots_remaining'] > 0): ?>
                    <a href="index.php?controller=Student&action=catalog"
                       class="btn-primary" style="display:inline-block;margin-top:1rem;">Browse Books</a>
                <?php else: ?>
                    <p style="font-size:0.82rem;color:var(--warning);margin-top:0.5rem;">
                        Maximum borrow limit reached.
                    </p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>
<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
</body>
</html>
