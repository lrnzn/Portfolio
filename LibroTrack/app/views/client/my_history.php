<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — My Borrow History</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/borrowers.css">
</head>
<body>

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
        <li><a href="index.php?controller=Student&action=history" class="active">My History</a></li>
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
            <h1>My Borrow History</h1>
            <p class="page-subtitle">A complete record of all your borrowing transactions.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid stats-grid--student" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['total_borrowed'] ?></span>
                <span class="stat-label">Total Books Borrowed</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['on_time'] ?></span>
                <span class="stat-label">Returned on Time</span>
            </div>
        </div>
        <div class="stat-card <?= $stats['returned_late'] > 0 ? 'stat-card--warning' : '' ?>">
            <div class="stat-icon">⚠️</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['returned_late'] ?></span>
                <span class="stat-label">Returned Late</span>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <form class="toolbar" method="GET" action="index.php">
        <input type="hidden" name="controller" value="Student">
        <input type="hidden" name="action"     value="history">
        <input type="text" name="search" class="search-input"
               placeholder="🔍 Search by book title..."
               value="<?= htmlspecialchars($search) ?>">
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
            <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
            <option value="overdue"  <?= $status === 'overdue'  ? 'selected' : '' ?>>Overdue</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
        <?php if ($search || $status): ?>
            <a href="index.php?controller=Student&action=history"
               class="btn-cancel" style="text-decoration:none;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <!-- History Table -->
    <div class="card">
        <?php if (empty($history)): ?>
            <div style="text-align:center;padding:2.5rem;color:var(--text-muted);">
                <p style="font-size:2rem;margin-bottom:0.5rem;">📋</p>
                <p>No transaction history<?= $search || $status ? ' matching your filters' : ' yet' ?>.</p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Borrowed</th>
                    <th>Due Date</th>
                    <th>Returned</th>
                    <th>Penalty</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $i => $t):
                    $ds = $t['displayStatus'];
                    $badgeClass = match($ds) {
                        'returned'      => 'badge--returned',
                        'returned_late' => 'badge--borrowed',
                        'overdue'       => 'badge--overdue',
                        default         => 'badge--borrowed',
                    };
                    $badgeLabel = match($ds) {
                        'returned'      => 'Returned',
                        'returned_late' => 'Returned Late',
                        'overdue'       => 'Overdue',
                        default         => 'Borrowed',
                    };
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($t['title']) ?></strong></td>
                    <td><?= htmlspecialchars($t['author']) ?></td>
                    <td><?= date('M d, Y', strtotime($t['borrowDate'])) ?></td>
                    <td><?= date('M d, Y', strtotime($t['dueDate'])) ?></td>
                    <td><?= $t['returnDate'] ? date('M d, Y', strtotime($t['returnDate'])) : '—' ?></td>
                    <td>
                        <?php if ($t['penaltyAmount']): ?>
                            <span class="badge <?= $t['penaltyPaid'] ? 'badge--returned' : 'badge--overdue' ?>">
                                ₱<?= number_format($t['penaltyAmount'], 2) ?>
                                <?= $t['penaltyPaid'] ? ' ✓' : '' ?>
                            </span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <span class="pagination-info">
                Showing <?= count($history) ?> record<?= count($history) !== 1 ? 's' : '' ?>
                <?= $search || $status ? 'matching your filters' : 'total' ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</main>
<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
</body>
</html>
