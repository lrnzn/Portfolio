<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Browse Books</title>
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
        <li><a href="index.php?controller=Student&action=index">Home</a></li>
        <li><a href="index.php?controller=Student&action=catalog" class="active">Browse Books</a></li>
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
            <h1>Browse Books</h1>
            <p class="page-subtitle">Search and explore the library catalog.</p>
        </div>
        <div class="view-toggle">
            <button class="view-btn active" id="btn-grid" onclick="switchView('grid')">⊞ Grid</button>
            <button class="view-btn" id="btn-list" onclick="switchView('list')">☰ List</button>
        </div>
    </div>

    <form class="toolbar" method="GET" action="index.php">
        <input type="hidden" name="controller" value="Student">
        <input type="hidden" name="action"     value="catalog">
        <input type="text" name="search" class="search-input"
               placeholder="🔍 Search by title, author, or ISBN..."
               value="<?= htmlspecialchars($search) ?>">
        <select name="genre" class="filter-select" onchange="this.form.submit()">
            <option value="">All Genres</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="available"   <?= $status === 'available'   ? 'selected' : '' ?>>Available</option>
            <option value="unavailable" <?= $status === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
        <?php if ($search || $genre || $status): ?>
            <a href="index.php?controller=Student&action=catalog"
               class="btn-cancel" style="text-decoration:none;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($books)): ?>
        <div style="text-align:center;padding:3rem;color:var(--text-muted);">
            <div style="font-size:3rem;margin-bottom:0.75rem;">📭</div>
            <p>No books found<?= $search || $genre || $status ? ' matching your filters' : '' ?>.</p>
        </div>
    <?php else: ?>

    <!-- Grid View -->
    <div class="books-grid" id="books-grid">
        <?php foreach ($books as $book):
            $avail  = (int)$book['available'];
            $status_cls = $avail === 0 ? 'unavailable' : ($avail <= 1 ? 'low' : 'available');
            $badge  = $avail === 0 ? 'badge--overdue' : ($avail <= 1 ? 'badge--borrowed' : 'badge--returned');
            $label  = $avail === 0 ? 'Unavailable' : "Available: {$avail}/{$book['copies']}";
        ?>
        <div class="book-card book-card--<?= $status_cls ?>">
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
                <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                <p class="book-author"><?= htmlspecialchars($book['author']) ?></p>
                <span class="book-genre"><?= htmlspecialchars($book['genre']) ?></span>
                <span class="badge <?= $badge ?>"><?= $label ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- List View -->
    <div class="card" id="books-list" style="display:none;">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Cover</th><th>Title</th><th>Author</th><th>Genre</th><th>Availability</th></tr>
            </thead>
            <tbody>
                <?php foreach ($books as $i => $book):
                    $avail = (int)$book['available'];
                    $badge = $avail === 0 ? 'badge--overdue' : ($avail <= 1 ? 'badge--borrowed' : 'badge--returned');
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="assets/img/covers/<?= htmlspecialchars($book['cover_image']) ?>"
                                 alt="cover" style="height:48px;width:36px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                            <span style="font-size:1.5rem;">📖</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['genre']) ?></td>
                    <td>
                        <span class="badge <?= $badge ?>">
                            <?= $avail === 0 ? 'Unavailable' : "{$avail}/{$book['copies']}" ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <span class="pagination-info">
            Showing <?= count($books) ?> book<?= count($books) !== 1 ? 's' : '' ?>
            <?= $search || $genre || $status ? 'matching your filters' : 'total' ?>
        </span>
    </div>

    <?php endif; ?>

</main>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/student_catalog.js"></script>
</body>
</html>
