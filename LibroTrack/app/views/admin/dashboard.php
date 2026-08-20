<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <img src="assets/img/logo.gif" alt="LibroTrack" class="brand-icon">
        <span class="nav-title">LibroTrack</span>
        <span class="nav-role-badge">Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?controller=Dashboard&action=index" class="active">Dashboard</a></li>
        <li><a href="index.php?controller=Book&action=index">Books</a></li>
        <li><a href="index.php?controller=Borrower&action=index">Borrowers</a></li>
        <li><a href="index.php?controller=Transaction&action=index">Transactions</a></li>
        <li><a href="index.php?controller=Overdue&action=index">Overdue</a></li>
        <li><a href="index.php?controller=Report&action=index">Reports</a></li>
    </ul>
    <div class="nav-user">
        <span class="nav-avatar">👩‍💼</span>
        <span class="nav-username">Librarian</span>
        <a href="index.php?controller=Auth&action=logout" class="nav-logout">Logout</a>
    </div>
</nav>

<main class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p class="page-subtitle">Welcome back! Here's what's happening in the library today.</p>
        </div>
        <div class="header-date"><?= date('F d, Y') ?></div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['total_books']) ?></span>
                <span class="stat-label">Total Books</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['available_copies']) ?></span>
                <span class="stat-label">Available Copies</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📤</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['currently_borrowed']) ?></span>
                <span class="stat-label">Currently Borrowed</span>
            </div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-icon">⚠️</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['overdue']) ?></span>
                <span class="stat-label">Overdue</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['total_borrowers']) ?></span>
                <span class="stat-label">Registered Borrowers</span>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-head">
                <h2>Recent Transactions</h2>
                <a href="index.php?controller=Transaction&action=history" class="card-link">View all →</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Borrower</th>
                        <th>Book Title</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">
                                No transactions yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t):
                            $status = $t['displayStatus'];
                            $badgeClass = match($status) {
                                'returned' => 'badge--returned',
                                'overdue'  => 'badge--overdue',
                                default    => 'badge--borrowed',
                            };
                            $badgeLabel = ucfirst($status);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($t['studentName']) ?></td>
                            <td><?= htmlspecialchars($t['bookTitle']) ?></td>
                            <td><?= date('M d, Y', strtotime($t['borrowDate'])) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Right Column -->
        <div class="right-col">

            <!-- Overdue Alert -->
            <div class="card card--alert">
                <div class="card-head">
                    <h2>⚠️ Overdue Books</h2>
                    <a href="index.php?controller=Overdue&action=index" class="card-link">Manage →</a>
                </div>
                <?php if (empty($overdueBooks)): ?>
                    <p style="font-size:0.875rem;color:var(--text-muted);text-align:center;padding:1rem 0;">
                        🎉 No overdue books right now.
                    </p>
                <?php else: ?>
                <ul class="overdue-list">
                    <?php foreach ($overdueBooks as $o): ?>
                    <li>
                        <div class="overdue-info">
                            <span class="overdue-name"><?= htmlspecialchars($o['studentName']) ?></span>
                            <span class="overdue-book"><?= htmlspecialchars($o['bookTitle']) ?></span>
                        </div>
                        <span class="overdue-days">
                            <?= $o['daysOverdue'] ?> day<?= $o['daysOverdue'] != 1 ? 's' : '' ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-head"><h2>Quick Actions</h2></div>
                <div class="quick-actions">
                    <a href="index.php?controller=Transaction&action=index"      class="action-btn">📤 Borrow Book</a>
                    <a href="index.php?controller=Transaction&action=returnPage" class="action-btn">📥 Return Book</a>
                    <a href="index.php?controller=Book&action=index"             class="action-btn">➕ Add Book</a>
                    <a href="index.php?controller=Borrower&action=index"        class="action-btn">👤 Add Borrower</a>
                    <a href="index.php?controller=Auth&action=reset2fa"
                       class="action-btn"
                       onclick="return confirm('Reset 2FA for this admin account and show a new QR code?');">🔐 Reset 2FA</a>
                </div>
            </div>

        </div>
    </div>

</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
</body>
</html>
