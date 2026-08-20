<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Reports</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/borrowers.css">
    <link rel="stylesheet" href="assets/css/reports.css">
</head>
<body>

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
        <li><a href="index.php?controller=Overdue&action=index">Overdue</a></li>
        <li><a href="index.php?controller=Report&action=index" class="active">Reports</a></li>
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
            <h1>Reports</h1>
            <p class="page-subtitle">Library activity summaries and statistics.</p>
        </div>
        <div class="header-actions">
            <form method="GET" action="index.php" style="display:flex;gap:0.5rem;align-items:center;">
                <input type="hidden" name="controller" value="Report">
                <input type="hidden" name="action"     value="index">
                <select name="month" class="filter-select">
                    <option value="">All Time</option>
                    <?php foreach ($availableMonths as $m): ?>
                        <option value="<?= $m['month'] ?>"
                            data-year="<?= $m['year'] ?>"
                            <?= ((int)$month === (int)$m['month'] && (int)$year === (int)$m['year']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="year" id="year-input" value="<?= htmlspecialchars($year) ?>">
            </form>
            <button class="btn-primary" onclick="window.print()">🖨️ Print Report</button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.75rem;">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_transactions']) ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📖</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_books']) ?></div>
                <div class="stat-label">Total Books</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_borrowers']) ?></div>
                <div class="stat-label">Registered Borrowers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div>
                <div class="stat-value">₱<?= number_format($stats['total_penalties'], 2) ?></div>
                <div class="stat-label">Total Penalties</div>
            </div>
        </div>
    </div>

    <div class="content-grid">

        <!-- Most Borrowed Books -->
        <div class="card">
            <div class="card-head">
                <h2>📚 Most Borrowed Books</h2>
                <span style="font-size:0.8rem;color:var(--text-muted);">
                    <?= $month && $year ? date('F Y', mktime(0,0,0,$month,1,$year)) : 'All Time' ?>
                </span>
            </div>
            <?php if (empty($mostBorrowed)): ?>
                <p style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">No data yet.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Rank</th><th>Book Title</th><th>Author</th><th>Times Borrowed</th></tr>
                </thead>
                <tbody>
                    <?php
                    $medals = ['🥇', '🥈', '🥉'];
                    foreach ($mostBorrowed as $i => $b):
                        $rank = $medals[$i] ?? ($i + 1);
                    ?>
                    <tr>
                        <td><?= $rank ?></td>
                        <td><?= htmlspecialchars($b['title']) ?></td>
                        <td><?= htmlspecialchars($b['author']) ?></td>
                        <td><span class="report-count"><?= $b['borrow_count'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Right column -->
        <div class="right-col">

            <!-- Top Borrowers -->
            <div class="card">
                <div class="card-head">
                    <h2>👥 Top Borrowers</h2>
                    <span style="font-size:0.8rem;color:var(--text-muted);">
                        <?= $month && $year ? date('F Y', mktime(0,0,0,$month,1,$year)) : 'All Time' ?>
                    </span>
                </div>
                <?php if (empty($topBorrowers)): ?>
                    <p style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">No data yet.</p>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr><th>Name</th><th>Course</th><th>Borrows</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topBorrowers as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['studentName']) ?></td>
                            <td><?= htmlspecialchars($b['course']) ?></td>
                            <td><span class="report-count"><?= $b['borrow_count'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Borrows by Genre -->
            <div class="card">
                <div class="card-head"><h2>📊 Borrows by Genre</h2></div>
                <?php if (empty($byGenre)): ?>
                    <p style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">No data yet.</p>
                <?php else:
                    $maxCount = max(array_column($byGenre, 'borrow_count'));
                ?>
                <div class="genre-bars">
                    <?php foreach ($byGenre as $g):
                        $pct = $maxCount > 0 ? round(($g['borrow_count'] / $maxCount) * 100) : 0;
                    ?>
                    <div class="genre-bar-item">
                        <span class="genre-label"><?= htmlspecialchars($g['genre']) ?></span>
                        <div class="genre-bar-wrap">
                            <div class="genre-bar" style="width:<?= $pct ?>%;"><?= $g['borrow_count'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/reports.js"></script>
</body>
</html>
