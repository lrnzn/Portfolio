<?php if (!defined("LIBROTRACK")) { header("Location: index.php?controller=Auth&action=login"); exit; } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroTrack — Borrower Management</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/books.css">
    <link rel="stylesheet" href="assets/css/borrower_management.css">
</head>
<body>

<!-- Toast -->
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
        <li><a href="index.php?controller=Book&action=index">Books</a></li>
        <li><a href="index.php?controller=Borrower&action=index" class="active">Borrowers</a></li>
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
            <h1>Borrower Management</h1>
            <p class="page-subtitle">Manage registered student borrowers.</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openAddModal()">➕ Add Borrower</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_borrowers']) ?></div>
                <div class="stat-label">Total Borrowers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📤</div>
            <div>
                <div class="stat-value"><?= number_format($stats['currently_borrowing']) ?></div>
                <div class="stat-label">Currently Borrowing</div>
            </div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-icon">⚠️</div>
            <div>
                <div class="stat-value"><?= number_format($stats['with_overdue']) ?></div>
                <div class="stat-label">With Overdue Books</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <form class="toolbar search-form" method="GET" action="index.php">
        <input type="hidden" name="controller" value="Borrower">
        <input type="hidden" name="action"     value="index">
        <input type="text" name="search" class="search-input"
               placeholder="🔍 Search by name, student number, or email..."
               value="<?= htmlspecialchars($search) ?>">
        <select name="course" class="filter-select" onchange="this.form.submit()">
            <option value="">All Courses</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>" <?= $course === $c ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active"  <?= $status === 'active'  ? 'selected' : '' ?>>Currently Borrowing</option>
            <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>With Overdue</option>
            <option value="clean"   <?= $status === 'clean'   ? 'selected' : '' ?>>No Active Borrows</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
        <?php if ($search || $course || $status): ?>
            <a href="index.php?controller=Borrower&action=index"
               class="btn-cancel" style="text-decoration:none;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="card">
        <?php if (empty($borrowers)): ?>
            <div class="empty-state">
                <div class="empty-icon">👤</div>
                <p>No borrowers found<?= $search || $course || $status ? ' matching your filters' : '' ?>.</p>
                <?php if (!$search && !$course && !$status): ?>
                    <button class="btn-primary" onclick="openAddModal()">➕ Add First Borrower</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Student No.</th>
                    <th>Course</th>
                    <th>Email</th>
                    <th>Active Borrows</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($borrowers as $i => $b):
                    $active  = (int) $b['active_borrows'];
                    $overdue = (int) $b['overdue_count'];
                    if ($overdue > 0) {
                        $badgeClass = 'badge--overdue';
                        $badgeLabel = 'Overdue';
                    } elseif ($active > 0) {
                        $badgeClass = 'badge--borrowed';
                        $badgeLabel = 'Active';
                    } else {
                        $badgeClass = 'badge--returned';
                        $badgeLabel = 'No Borrow';
                    }
                    $isNew = $flash_action === 'added' && $i === 0;
                    $fullName = htmlspecialchars($b['fname'] . ' ' . $b['lname'] . ($b['nameExt'] ? ' ' . $b['nameExt'] : ''));
                ?>
                <tr id="row-<?= $b['studentID'] ?>" <?= $isNew ? 'class="highlight-row"' : '' ?>>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= $fullName ?></strong></td>
                    <td class="student-number"><?= htmlspecialchars($b['studentNumber']) ?></td>
                    <td><?= htmlspecialchars($b['course']) ?></td>
                    <td><?= htmlspecialchars($b['email']) ?></td>
                    <td><?= $active ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                    <td class="action-col">
                        <button class="btn-view"   onclick="openViewModal(<?= $b['studentID'] ?>)">👁 View</button>
                        <button class="btn-edit"   onclick="openEditModal(<?= $b['studentID'] ?>)">✏️ Edit</button>
                        <button class="btn-delete" onclick="openDeleteModal(<?= $b['studentID'] ?>, '<?= addslashes($fullName) ?>')">🗑️ Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <span class="pagination-info">
                Showing <?= count($borrowers) ?> borrower<?= count($borrowers) !== 1 ? 's' : '' ?>
                <?= $search || $course || $status ? 'matching your filters' : 'total' ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</main>

<!-- ════════════════════════════ ADD MODAL ════════════════════════════ -->
<div class="modal-overlay" id="add-overlay" onclick="closeAddModal()"></div>
<div class="modal" id="add-modal">
    <div class="modal-header">
        <h2>➕ Add Borrower</h2>
        <button class="modal-close" onclick="closeAddModal()">✕</button>
    </div>
    <div class="modal-body">
        <form action="index.php?controller=Borrower&action=store" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="fname" placeholder="Enter first name" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="mname" placeholder="Enter middle name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lname" placeholder="Enter last name" required>
                </div>
                <div class="form-group">
                    <label>Name Extension</label>
                    <input type="text" name="nameExt" placeholder="e.g. Jr., Sr., III">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Student Number *</label>
                    <input type="text" name="studentNumber" placeholder="e.g. 2021-00123" required>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <select name="course" required>
                        <option value="">Select course</option>
                        <option>BSIT</option>
                        <option>BSCS</option>
                        <option>BSED</option>
                        <option>BSBA</option>
                        <option>BSME</option>
                        <option>BSECE</option>
                        <option>BSN</option>
                        <option>BSED</option>
                        <option>BSCRIM</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" placeholder="Enter email address" required>
            </div>
            <div class="default-password-notice">
                🔑 <strong>Default login credentials:</strong><br>
                The student can log in using their <strong>Student Number</strong> as both their username and password.<br>
                They can change their password after logging in.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Borrower</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════ VIEW MODAL ═══════════════════════════ -->
<div class="modal-overlay" id="view-overlay" onclick="closeViewModal()"></div>
<div class="modal" id="view-modal">
    <div class="modal-header">
        <h2>👤 Borrower Details</h2>
        <button class="modal-close" onclick="closeViewModal()">✕</button>
    </div>
    <div class="modal-body" id="view-modal-body">
        <p>Loading...</p>
    </div>
</div>

<!-- ════════════════════════════ EDIT MODAL ═══════════════════════════ -->
<div class="modal-overlay" id="edit-overlay" onclick="closeEditModal()"></div>
<div class="modal" id="edit-modal">
    <div class="modal-header">
        <h2>✏️ Edit Borrower</h2>
        <button class="modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <div class="modal-body">
        <form action="index.php?controller=Borrower&action=update" method="POST">
            <input type="hidden" name="studentID" id="edit-studentID">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="fname" id="edit-fname" placeholder="Enter first name" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="mname" id="edit-mname" placeholder="Enter middle name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="lname" id="edit-lname" placeholder="Enter last name" required>
                </div>
                <div class="form-group">
                    <label>Name Extension</label>
                    <input type="text" name="nameExt" id="edit-nameExt" placeholder="e.g. Jr., Sr., III">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Student Number *</label>
                    <input type="text" name="studentNumber" id="edit-studentNumber" placeholder="e.g. 2021-00123" required>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <select name="course" id="edit-course" required>
                        <option value="" disabled selected hidden>Select course</option>
                        <option>BSIT</option>
                        <option>BSIS</option>
                        <option>BIT</option>
                        <option>BSINDTECH</option>
                        <option>BTVTED</option>
                        <option>BSECE</option>
                        <option>BSCPE</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" id="edit-email" placeholder="Enter email address" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════ DELETE MODAL ══════════════════════════ -->
<div class="modal-overlay" id="delete-overlay" onclick="closeDeleteModal()"></div>
<div class="modal modal--sm" id="delete-modal">
    <div class="modal-header">
        <h2>🗑️ Remove Borrower</h2>
        <button class="modal-close" onclick="closeDeleteModal()">✕</button>
    </div>
    <div class="modal-body">
        <p class="delete-msg">
            Are you sure you want to remove <strong id="delete-borrower-name"></strong> from the system?
            This action cannot be undone.
        </p>
        <form action="index.php?controller=Borrower&action=destroy" method="POST">
            <input type="hidden" name="studentID" id="delete-studentID">
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete-confirm">🗑️ Yes, Remove</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/ui_icons.js"></script>
<script src="assets/js/mobile_nav.js"></script>
<script src="assets/js/borrower_management.js"></script>

</body>
</html>
