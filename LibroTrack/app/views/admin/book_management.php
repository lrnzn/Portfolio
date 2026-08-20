<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Book Management</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/book_management.css">
</head>
<body>

<!-- Toast Notification -->
<?php if (!empty($flash)): ?>
<div class="toast toast--<?= htmlspecialchars($flash_type) ?>">
    <?= $flash_type === 'success' ? '✅' : '❌' ?>
    <?= htmlspecialchars($flash) ?>
</div>
<?php endif; ?>

<!-- Navbar -->
<nav class="navbar">
    <div class="nav-brand">
        <img src="assets/img/logo.gif" alt="LibroTrack" class="brand-icon">
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
            <h1>Book Management</h1>
            <p class="page-subtitle">Add, edit, or remove books from the library catalog.</p>
        </div>
        <div class="header-center">
            <button class="btn-primary" onclick="openAddModal()">➕ Add New Book</button>
        </div>
        <div class="view-toggle">
            <a href="index.php?controller=Book&action=index" class="view-btn active">📋 Management</a>
            <a href="index.php?controller=Book&action=catalog" class="view-btn">📚 Catalog</a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_books']) ?></div>
                <div class="stat-label">Total Titles</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_copies']) ?></div>
                <div class="stat-label">Total Copies</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div>
                <div class="stat-value"><?= number_format($stats['available_copies']) ?></div>
                <div class="stat-label">Available Copies</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📤</div>
            <div>
                <div class="stat-value"><?= number_format($stats['currently_borrowed']) ?></div>
                <div class="stat-label">Currently Borrowed</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <form class="toolbar search-form" method="GET" action="index.php">
        <input type="hidden" name="controller" value="Book">
        <input type="hidden" name="action"     value="index">
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
            <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="borrowed"  <?= $status === 'borrowed'  ? 'selected' : '' ?>>Fully Borrowed</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
        <?php if ($search || $genre || $status): ?>
            <a href="index.php?controller=Book&action=index"
               class="btn-cancel" style="text-decoration:none;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <!-- Books Table -->
    <div class="card">
        <?php if (empty($books)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No books found<?= $search || $genre || $status ? ' matching your filters' : '' ?>.</p>
                <?php if (!$search && !$genre && !$status): ?>
                    <button class="btn-primary" onclick="openAddModal()">➕ Add Your First Book</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>ISBN</th>
                    <th>Location</th>
                    <th>Copies</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $i => $book):
                    $avail      = (int) $book['available'];
                    $copies     = (int) $book['copies'];
                    $badgeClass = $avail === 0 ? 'badge--overdue' : ($avail <= 1 ? 'badge--borrowed' : 'badge--returned');
                    $isNew      = $flash_action === 'added' && $i === 0;
                ?>
                <tr id="row-<?= $book['bookID'] ?>" <?= $isNew ? 'class="highlight-row"' : '' ?>>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="assets/img/covers/<?= htmlspecialchars($book['cover_image']) ?>"
                                 alt="Cover" style="height:48px;width:36px;object-fit:cover;border-radius:4px;border:1px solid var(--cream-dark);">
                        <?php else: ?>
                            <span style="font-size:1.5rem;">📖</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['genre']) ?></td>
                    <td class="isbn-cell"><?= htmlspecialchars($book['isbn'] ?? '—') ?></td>
                    <td class="location-cell"><?= htmlspecialchars($book['location'] ?? '—') ?></td>
                    <td class="copies-cell"><?= $copies ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $avail ?> / <?= $copies ?></span></td>
                    <td class="action-col">
                        <button class="btn-view"   onclick="openViewModal(<?= $book['bookID'] ?>)">👁 View</button>
                        <button class="btn-edit"   onclick="openEditModal(<?= $book['bookID'] ?>)">✏️ Edit</button>
                        <button class="btn-delete" onclick="openDeleteModal(<?= $book['bookID'] ?>, '<?= addslashes(htmlspecialchars($book['title'])) ?>')">🗑️ Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <span class="pagination-info">
                Showing <?= count($books) ?> book<?= count($books) !== 1 ? 's' : '' ?>
                <?= $search || $genre || $status ? 'matching your filters' : 'total' ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- ═══════════════════════════════ ADD MODAL ═══════════════════════════════ -->
<div class="modal-overlay" id="add-overlay" onclick="closeAddModal()"></div>
<div class="modal" id="add-modal">
    <div class="modal-header">
        <h2>➕ Add New Book</h2>
        <button class="modal-close" onclick="closeAddModal()">✕</button>
    </div>
    <div class="modal-body">
        <form action="index.php?controller=Book&action=store" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" placeholder="Enter book title" required>
                </div>
                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" placeholder="Enter author name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" placeholder="e.g. 978-0-07-352702-4">
                </div>
                <div class="form-group">
                    <label>Genre *</label>
                    <select name="genre" required>
                        <option value="" disabled selected hidden>Select genre</option>
                        <option>Science &amp; Technology</option>
                        <option>History</option>
                        <option>Literature</option>
                        <option>Novel</option>
                        <option>Mathematics</option>
                        <option>Engineering</option>
                        <option>Social Science</option>
                        <option>Business</option>
                        <option>Philosophy</option>
                        <option>Arts</option>
                        <option>Fantasy</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Number of Copies *</label>
                    <input type="number" name="copies" placeholder="e.g. 3" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label>Shelf Location</label>
                    <input type="text" name="location" placeholder="e.g. Section A, Row 3">
                </div>
            </div>
            <div class="form-group">
                <label>Description (optional)</label>
                <textarea name="description" rows="3" placeholder="Brief description of the book..."></textarea>
            </div>
            <div class="form-group">
                <label>Cover Image (optional)</label>
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small style="color:var(--text-muted);font-size:0.78rem;">JPG, PNG, GIF or WEBP — max 5MB</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Book</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════ VIEW MODAL ══════════════════════════════ -->
<div class="modal-overlay" id="view-overlay" onclick="closeViewModal()"></div>
<div class="modal" id="view-modal">
    <div class="modal-header">
        <h2>📖 Book Details</h2>
        <button class="modal-close" onclick="closeViewModal()">✕</button>
    </div>
    <div class="modal-body" id="view-modal-body">
        <p>Loading...</p>
    </div>
</div>

<!-- ═══════════════════════════════ EDIT MODAL ══════════════════════════════ -->
<div class="modal-overlay" id="edit-overlay" onclick="closeEditModal()"></div>
<div class="modal" id="edit-modal">
    <div class="modal-header">
        <h2>✏️ Edit Book</h2>
        <button class="modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <div class="modal-body">
        <form action="index.php?controller=Book&action=update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="bookID" id="edit-bookID">
            <div class="form-row">
                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" id="edit-title" placeholder="Enter book title" required>
                </div>
                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" id="edit-author" placeholder="Enter author name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" id="edit-isbn" placeholder="e.g. 978-0-07-352702-4">
                </div>
                <div class="form-group">
                    <label>Genre *</label>
                    <select name="genre" id="edit-genre" required>
                        <option value="" disabled selected hidden>Select genre</option>
                        <option>Science &amp; Technology</option>
                        <option>History</option>
                        <option>Literature</option>
                        <option>Non-Fiction</option>
                        <option>Mathematics</option>
                        <option>Engineering</option>
                        <option>Social Science</option>
                        <option>Business</option>
                        <option>Philosophy</option>
                        <option>Arts</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Number of Copies *</label>
                    <input type="number" name="copies" id="edit-copies" min="1" required>
                </div>
                <div class="form-group">
                    <label>Shelf Location</label>
                    <input type="text" name="location" id="edit-location" placeholder="e.g. Section A, Row 3">
                </div>
            </div>
            <div class="form-group">
                <label>Description (optional)</label>
                <textarea name="description" id="edit-description" rows="3" placeholder="Brief description of the book..."></textarea>
            </div>
            <div class="form-group">
                <label>Cover Image (optional)</label>
                <div id="edit-current-cover" style="margin-bottom:0.5rem;display:none;">
                    <img id="edit-cover-preview" src="" alt="Current cover"
                         style="height:80px;border-radius:8px;border:1px solid var(--cream-dark);object-fit:cover;">
                    <label style="display:flex;align-items:center;gap:0.4rem;margin-top:0.4rem;font-size:0.82rem;cursor:pointer;">
                        <input type="hidden" name="remove_image" value="0">
                        <input type="checkbox" name="remove_image" id="edit-remove-image" value="1"> Remove current image
                    </label>
                </div>
                <input type="file" name="cover_image" id="edit-cover-input" accept="image/jpeg,image/png,image/gif,image/webp">
                <small style="color:var(--text-muted);font-size:0.78rem;">Upload a new image to replace the current one. JPG, PNG, GIF or WEBP — max 5MB</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════ DELETE MODAL ══════════════════════════════ -->
<div class="modal-overlay" id="delete-overlay" onclick="closeDeleteModal()"></div>
<div class="modal modal--sm" id="delete-modal">
    <div class="modal-header">
        <h2>🗑️ Delete Book</h2>
        <button class="modal-close" onclick="closeDeleteModal()">✕</button>
    </div>
    <div class="modal-body">
        <p class="delete-msg">
            Are you sure you want to delete <strong id="delete-book-title"></strong>?
            This action cannot be undone.
        </p>
        <form action="index.php?controller=Book&action=destroy" method="POST">
            <input type="hidden" name="bookID" id="delete-bookID">
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete-confirm">🗑️ Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/book_management.js"></script>

</body>
</html>
