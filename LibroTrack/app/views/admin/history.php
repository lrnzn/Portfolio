<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Transaction History</title>
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
        <li><a href="index.php?controller=Transaction&action=index" class="active">Transactions</a></li>
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

    <div class="page-header">
        <div>
            <h1>Transaction History</h1>
            <p class="page-subtitle">Complete log of all borrowing and returning activities.</p>
        </div>
        <div class="view-toggle">
            <a href="index.php?controller=Transaction&action=index"      class="view-btn">📤 Borrow</a>
            <a href="index.php?controller=Transaction&action=returnPage" class="view-btn">📥 Return</a>
            <a href="index.php?controller=Transaction&action=history"    class="view-btn active">🕘 History</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div><div class="stat-value"><?= number_format($stats['total']) ?></div><div class="stat-label">Total Transactions</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📤</div>
            <div><div class="stat-value"><?= number_format($stats['borrowed']) ?></div><div class="stat-label">Currently Borrowed</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📥</div>
            <div><div class="stat-value"><?= number_format($stats['returned']) ?></div><div class="stat-label">Returned</div></div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-icon">⚠️</div>
            <div><div class="stat-value"><?= number_format($stats['overdue']) ?></div><div class="stat-label">Overdue</div></div>
        </div>
    </div>

    <!-- Filters -->
    <form class="toolbar search-form" method="GET" action="index.php">
        <input type="hidden" name="controller" value="Transaction">
        <input type="hidden" name="action"     value="history">
        <input type="text" name="search" class="search-input"
               placeholder="🔍 Search by borrower or book title..."
               value="<?= htmlspecialchars($search) ?>">
        <div style="display:flex;align-items:center;gap:0.4rem;">
            <label style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;">From</label>
            <input type="date" name="from" class="filter-select"
                   value="<?= htmlspecialchars($from) ?>">
        </div>
        <div style="display:flex;align-items:center;gap:0.4rem;">
            <label style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;">To</label>
            <input type="date" name="to" class="filter-select"
                   value="<?= htmlspecialchars($to) ?>">
        </div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
            <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
            <option value="overdue"  <?= $status === 'overdue'  ? 'selected' : '' ?>>Overdue</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
        <?php if ($search || $status || $from || $to): ?>
            <a href="index.php?controller=Transaction&action=history"
               class="btn-cancel" style="text-decoration:none;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="card">
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p>No transactions found<?= $search || $status || $from || $to ? ' matching your filters' : '' ?>.</p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Borrower</th>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Penalty</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $i => $t):
                    $isOverdue = (int)$t['daysOverdue'] > 0 && $t['status'] !== 'returned';
                    if ($isOverdue) {
                        $badgeClass = 'badge--overdue';
                        $badgeLabel = 'Overdue';
                    } elseif ($t['status'] === 'returned') {
                        $badgeClass = 'badge--returned';
                        $badgeLabel = 'Returned';
                    } else {
                        $badgeClass = 'badge--borrowed';
                        $badgeLabel = 'Borrowed';
                    }
                    $deleteLabel = htmlspecialchars($t['studentName'] . ' — ' . $t['bookTitle']);
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($t['studentName']) ?></td>
                    <td><?= htmlspecialchars($t['bookTitle']) ?></td>
                    <td><?= date('M d, Y', strtotime($t['borrowDate'])) ?></td>
                    <td><?= date('M d, Y', strtotime($t['dueDate'])) ?></td>
                    <td>
                        <?php if ($t['returnDate']): ?>
                            <?= date('M d, Y', strtotime($t['returnDate'])) ?>
                        <?php else: ?>
                            <span style="font-size:0.78rem;color:var(--text-muted);font-style:italic;">Not returned</span>
                        <?php endif; ?>
                    </td>
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
                    <td class="action-col">
                        <?php if ($t['status'] !== 'returned'): ?>
                            <button class="btn-edit"
                                onclick="openEditModal(<?= $t['transactionID'] ?>)">✏️ Edit</button>
                        <?php endif; ?>
                        <?php if ($t['status'] === 'returned'): ?>
                            <button class="btn-delete"
                                onclick="openDeleteModal(<?= $t['transactionID'] ?>, '<?= addslashes($deleteLabel) ?>')">
                                🗑️ Delete
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <span class="pagination-info">
                Showing <?= count($transactions) ?> transaction<?= count($transactions) !== 1 ? 's' : '' ?>
                <?= $search || $status || $from || $to ? 'matching your filters' : 'total' ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="edit-overlay" onclick="closeEditModal()"></div>
<div class="modal modal--sm" id="edit-modal">
    <div class="modal-header">
        <h2>✏️ Edit Transaction</h2>
        <button class="modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <div class="modal-body">
        <p id="edit-info" style="font-size:0.875rem;color:var(--text-muted);margin-bottom:1rem;"></p>
        <form action="index.php?controller=Transaction&action=update" method="POST">
            <input type="hidden" name="transactionID" id="edit-transactionID">
            <div class="form-row">
                <div class="form-group">
                    <label>Borrow Date *</label>
                    <input type="date" name="borrowDate" id="edit-borrowDate" required>
                </div>
                <div class="form-group">
                    <label>Due Date *</label>
                    <input type="date" name="dueDate" id="edit-dueDate" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="delete-overlay" onclick="closeDeleteModal()"></div>
<div class="modal modal--sm" id="delete-modal">
    <div class="modal-header">
        <h2>🗑️ Delete Transaction</h2>
        <button class="modal-close" onclick="closeDeleteModal()">✕</button>
    </div>
    <div class="modal-body">
        <p class="delete-msg">
            Are you sure you want to delete the transaction for
            <strong id="delete-transaction-label"></strong>?
            This action cannot be undone.
        </p>
        <form action="index.php?controller=Transaction&action=destroy" method="POST">
            <input type="hidden" name="transactionID" id="delete-transactionID">
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete-confirm">🗑️ Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/history.js"></script>
</body>
</html>
