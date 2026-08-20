<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Return Book</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/borrowers.css">
    <link rel="stylesheet" href="assets/css/livesearch.css">
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
            <h1>Return Book</h1>
            <p class="page-subtitle">Process a book return transaction.</p>
        </div>
        <div class="view-toggle">
            <a href="index.php?controller=Transaction&action=index"      class="view-btn">📤 Borrow</a>
            <a href="index.php?controller=Transaction&action=returnPage" class="view-btn active">📥 Return</a>
            <a href="index.php?controller=Transaction&action=history"    class="view-btn">🕘 History</a>
        </div>
    </div>

    <div class="transaction-layout">

        <!-- Return Form -->
        <div class="card transaction-form">
            <div class="card-head"><h2>Process Return</h2></div>
            <form action="index.php?controller=Transaction&action=processReturn"
                  method="POST">
                <input type="hidden" name="transactionID" id="input-transactionID">
                <input type="hidden" name="daysOverdue"   id="input-daysOverdue" value="0">

                <!-- Student Live Search -->
                <div class="form-group">
                    <label>Search Student *</label>
                    <div class="livesearch-wrap">
                        <input type="text" id="student-search"
                               placeholder="Type name or student number..."
                               autocomplete="off">
                        <ul class="livesearch-results" id="student-results"></ul>
                    </div>
                </div>

                <!-- Student Preview -->
                <div class="info-preview" id="student-preview">
                    <div class="preview-icon">🎓</div>
                    <div class="preview-details">
                        <strong class="preview-name"></strong>
                        <span class="preview-meta"></span>
                        <span class="preview-status"></span>
                    </div>
                    <span class="badge preview-badge"></span>
                    <button type="button" class="preview-clear" onclick="clearStudent()">✕</button>
                </div>

                <!-- Book Select (loaded after student is picked) -->
                <div class="form-group" id="book-select-group">
                    <label>Select Book to Return *</label>
                    <select id="book-select" onchange="onBookChange(this)">
                        <option value="">-- Select student first --</option>
                    </select>
                </div>

                <!-- Overdue Warning -->
                <div class="overdue-warning" id="overdue-box">
                    ⚠️ This book is <strong id="overdue-days"></strong> days overdue.
                    Penalty: <strong id="overdue-penalty"></strong> (₱5.00/day)
                </div>

                <!-- Return Details -->
                <div class="form-row" id="return-details">
                    <div class="form-group">
                        <label>Return Date *</label>
                        <input type="date" name="returnDate" id="return-date" required>
                    </div>
                    <div class="form-group">
                        <label>Penalty Amount</label>
                        <input type="text" id="penalty-amount-display" readonly
                               style="background:#FDECEA;color:#C0392B;font-weight:500;">
                    </div>
                </div>

                <div class="form-group" id="penalty-paid-group">
                    <label>Penalty Status</label>
                    <select name="penalty_paid">
                        <option value="0">Not yet paid</option>
                        <option value="1">Paid</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" id="confirm-btn"
                        style="width:100%;margin-top:0.5rem;">
                    ✅ Confirm Return
                </button>
            </form>
        </div>

        <!-- Recent Returns -->
        <div class="card">
            <div class="card-head">
                <h2>Recent Returns</h2>
                <a href="index.php?controller=Transaction&action=history" class="card-link">View all →</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>Borrower</th><th>Book</th><th>Returned</th><th>Penalty</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($recentReturns)): ?>
                        <tr><td colspan="4" style="text-align:center;color:var(--text-muted);">No returns yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentReturns as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['studentName']) ?></td>
                            <td><?= htmlspecialchars($r['bookTitle']) ?></td>
                            <td><?= date('M d', strtotime($r['returnDate'])) ?></td>
                            <td>
                                <?php if ($r['penaltyAmount']): ?>
                                    <span class="badge badge--overdue">₱<?= number_format($r['penaltyAmount'], 2) ?></span>
                                <?php else: ?>
                                    <span class="badge badge--returned">None</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/return.js"></script>
</body>
</html>
