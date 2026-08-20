<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Overdue & Penalties</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/borrowers.css">
    <link rel="stylesheet" href="assets/css/book_management.css">
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
        <span class="nav-role-badge">Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?controller=Dashboard&action=index">Dashboard</a></li>
        <li><a href="index.php?controller=Book&action=index">Books</a></li>
        <li><a href="index.php?controller=Borrower&action=index">Borrowers</a></li>
        <li><a href="index.php?controller=Transaction&action=index">Transactions</a></li>
        <li><a href="index.php?controller=Overdue&action=index" class="active">Overdue</a></li>
        <li><a href="index.php?controller=Report&action=index">Reports</a></li>
    </ul>
    <div class="nav-user">
        <span class="nav-avatar">👩‍💼</span>
        <span class="nav-username">Librarian</span>
        <a href="index.php?controller=Auth&action=logout" class="nav-logout">Logout</a>
    </div>
</nav>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1>Overdue & Penalty Tracking</h1>
            <p class="page-subtitle">Monitor overdue books and manage penalty records.</p>
        </div>
        <div class="penalty-rate">⚙️ Penalty Rate: <strong>₱5.00 / day</strong></div>
    </div>

    <!-- Stats -->
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card stat-card--warning">
            <div class="stat-icon">⚠️</div>
            <div>
                <div class="stat-value"><?= number_format((int)($stats['overdue_count'] ?? 0)) ?></div>
                <div class="stat-label">Overdue Books</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div>
                <div class="stat-value">₱<?= number_format((float)($stats['total_penalties'] ?? 0), 2) ?></div>
                <div class="stat-label">Total Penalties</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div>
                <div class="stat-value">₱<?= number_format((float)($stats['paid_amount'] ?? 0), 2) ?></div>
                <div class="stat-label">Penalties Paid</div>
            </div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-icon">❌</div>
            <div>
                <div class="stat-value">₱<?= number_format((float)($stats['unpaid_amount'] ?? 0), 2) ?></div>
                <div class="stat-label">Unpaid Penalties</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form class="toolbar search-form" method="GET" action="index.php">
        <input type="hidden" name="controller" value="Overdue">
        <input type="hidden" name="action"     value="index">
        <input type="text" name="search" class="search-input"
               placeholder="🔍 Search by borrower name or book title..."
               value="<?= htmlspecialchars($search) ?>">
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Penalty Status</option>
            <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
            <option value="paid"   <?= $status === 'paid'   ? 'selected' : '' ?>>Paid</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
        <?php if ($search || $status): ?>
            <a href="index.php?controller=Overdue&action=index"
               class="btn-cancel" style="text-decoration:none;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="card">
        <?php if (empty($records)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎉</div>
                <p><?= $search || $status ? 'No records matching your filters.' : 'No overdue books right now!' ?></p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Borrower</th>
                    <th>Book Title</th>
                    <th>Due Date</th>
                    <th>Days Overdue</th>
                    <th>Penalty</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $i => $r):
                    $isPaid = (int)($r['paid'] ?? 0) === 1;
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r['studentName']) ?></strong><br>
                        <small style="color:var(--text-muted);"><?= htmlspecialchars($r['studentNumber']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($r['bookTitle']) ?></td>
                    <td><?= date('M d, Y', strtotime($r['dueDate'])) ?></td>
                    <td>
                        <span class="badge badge--overdue"><?= $r['daysOverdue'] ?> day<?= $r['daysOverdue'] != 1 ? 's' : '' ?></span>
                    </td>
                    <td><strong>₱<?= number_format((float)($r['penaltyAmount'] ?? 0), 2) ?></strong></td>
                    <td>
                        <?php if ($isPaid): ?>
                            <span class="badge badge--returned">Paid</span>
                        <?php else: ?>
                            <span class="badge badge--overdue">Unpaid</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$isPaid): ?>
                        <form action="index.php?controller=Overdue&action=markPaid"
                              method="POST" style="display:inline;">
                            <input type="hidden" name="transactionID" value="<?= $r['transactionID'] ?>">
                            <button type="submit" class="btn-edit">✅ Mark Paid</button>
                        </form>
                        <?php else: ?>
                            <span style="font-size:0.8rem;color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <span class="pagination-info">
                Showing <?= count($records) ?> overdue record<?= count($records) !== 1 ? 's' : '' ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/overdue.js"></script>
</body>
</html>
