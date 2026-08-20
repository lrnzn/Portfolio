<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Book Catalog</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
</head>
<body>

<!-- Top Navigation -->
<nav class="navbar">
    <div class="nav-brand">
        <img src="assets/img/logo.gif" alt="LibroTrack Logo" class="brand-icon">
        <span class="nav-title">LibroTrack</span>
        <span class="nav-role-badge">Admin</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?controller=Dashboard&action=index">Dashboard</a></li>
        <li><a href="index.php?controller=Book&action=index" class="active">Books</a></li>
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
            <h1>Book Catalog</h1>
            <p class="page-subtitle">Browse the complete library collection.</p>
        </div>
        <div class="header-center">
            <div class="view-toggle">
                <button class="view-btn active" id="btn-grid" onclick="switchView('grid')">⊞ Grid</button>
                <button class="view-btn" id="btn-list" onclick="switchView('list')">☰ List</button>
            </div>
        </div>
        <div class="view-toggle">
            <a href="index.php?controller=Book&action=index" class="view-btn">📋 Management</a>
            <a href="index.php?controller=Book&action=catalog" class="view-btn active">📚 Catalog</a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="toolbar">
        <input type="text" class="search-input" placeholder="🔍 Search by title, author, or ISBN...">
        <select class="filter-select">
            <option value="">All Genres</option>
        </select>
        <select class="filter-select">
            <option value="">All Status</option>
        </select>
    </div>

    <!-- GRID VIEW -->
    <div class="books-grid" id="books-grid">
        <?php if (!empty($books)): ?>
            <?php foreach ($books as $book):
                $available = $book['available'] ?? $book['copies'];
                $status = $available == 0 ? 'unavailable' : ($available <= 1 ? 'low' : 'available');
                $badge  = $available == 0 ? 'badge--overdue' : ($available <= 1 ? 'badge--borrowed' : 'badge--returned');
                $label  = $available == 0 ? 'Unavailable' : "Available: {$available}/{$book['copies']}";
            ?>
            <div class="book-card book-card--<?= $status ?>">
                <div class="book-cover">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="assets/img/covers/<?= htmlspecialchars($book['cover_image']) ?>"
                             alt="<?= htmlspecialchars($book['title']) ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span class="book-cover-icon">📖</span>
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <h3 class="book-title"><?= $book['title'] ?></h3>
                    <p class="book-author"><?= $book['author'] ?></p>
                    <span class="book-genre"><?= $book['genre'] ?></span>
                    <span class="badge <?= $badge ?>"><?= $label ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No books found.</p>
        <?php endif; ?>
    </div>

    <!-- LIST VIEW -->
    <div class="card" id="books-list" style="display:none;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Copies</th>
                    <th>Available</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($books)): ?>
                    <?php foreach ($books as $i => $book): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <?php if (!empty($book['cover_image'])): ?>
                                <img src="assets/img/covers/<?= htmlspecialchars($book['cover_image']) ?>"
                                     alt="cover" style="height:48px;width:36px;object-fit:cover;border-radius:4px;border:1px solid var(--cream-dark);">
                            <?php else: ?>
                                <span style="font-size:1.5rem;">📖</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $book['title'] ?></td>
                        <td><?= $book['author'] ?></td>
                        <td><?= $book['genre'] ?></td>
                        <td><?= $book['copies'] ?></td>
                        <td><?= $book['available'] ?? $book['copies'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No books found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/book_catalog.js"></script>

</body>
</html>
