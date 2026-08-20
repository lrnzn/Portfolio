// LibroTrack — Borrower Management JS | public/assets/js/borrower_management.js

// ── ADD Modal ─────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('add-overlay').classList.add('active');
    document.getElementById('add-modal').classList.add('active');
}
function closeAddModal() {
    document.getElementById('add-overlay').classList.remove('active');
    document.getElementById('add-modal').classList.remove('active');
}

// ── VIEW Modal ────────────────────────────────────────────────────
async function openViewModal(id) {
    document.getElementById('view-overlay').classList.add('active');
    document.getElementById('view-modal').classList.add('active');
    document.getElementById('view-modal-body').innerHTML =
        '<p style="text-align:center;color:var(--text-muted);padding:1rem;">Loading...</p>';

    try {
        const res  = await fetch(`index.php?controller=Borrower&action=get&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        const s = data.student;
        const borrows = data.borrows;

        const borrowRows = borrows.length > 0
            ? borrows.map(b => {
                const isOverdue = parseInt(b.daysOverdue) > 0;
                const badge = isOverdue ? 'badge--overdue' : 'badge--borrowed';
                const label = isOverdue ? `Overdue (${b.daysOverdue}d)` : 'Borrowed';
                return `<tr>
                    <td>${escHtml(b.title)}</td>
                    <td>${escHtml(b.dueDate)}</td>
                    <td><span class="badge ${badge}">${label}</span></td>
                </tr>`;
            }).join('')
            : `<tr><td colspan="3" style="text-align:center;color:var(--text-muted);">No active borrows.</td></tr>`;

        const avatarHtml = s.profile_picture
            ? `<img src="assets/img/profiles/${s.profile_picture}"
                    style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--cream-dark);">`
            : `<div style="width:56px;height:56px;border-radius:50%;background:var(--brown-warm);color:white;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;font-family:'Playfair Display',serif;">${s.fname.charAt(0).toUpperCase()}</div>`;

        document.getElementById('view-modal-body').innerHTML = `
            <div class="borrower-profile">
                <div class="borrower-avatar">${avatarHtml}</div>
                <div class="borrower-details">
                    <h3>${escHtml(s.fname)} ${escHtml(s.lname)}${s.nameExt ? ' ' + escHtml(s.nameExt) : ''}</h3>
                    <p>${escHtml(s.studentNumber)} &nbsp;|&nbsp; ${escHtml(s.course)}</p>
                    <p>${escHtml(s.email)}</p>
                </div>
            </div>
            <div class="borrower-stats">
                <div class="b-stat"><span>${s.active_borrows}</span><small>Active Borrows</small></div>
                <div class="b-stat"><span>${s.total_borrowed}</span><small>Total Borrowed</small></div>
                <div class="b-stat ${parseInt(s.overdue_count) > 0 ? 'b-stat--warn' : ''}">
                    <span>${s.overdue_count}</span><small>Overdue</small>
                </div>
            </div>
            <h4 class="borrows-heading">Current Borrows</h4>
            <table class="data-table">
                <thead><tr><th>Book Title</th><th>Due Date</th><th>Status</th></tr></thead>
                <tbody>${borrowRows}</tbody>
            </table>
            <div class="modal-footer" style="margin-top:1rem;">
                <button class="btn-cancel" onclick="closeViewModal()">Close</button>
                <button class="btn-edit" style="padding:0.65rem 1.25rem;font-size:0.9rem;"
                    onclick="closeViewModal(); openEditModal(${s.studentID})">✏️ Edit</button>
            </div>
        `;
    } catch (e) {
        document.getElementById('view-modal-body').innerHTML =
            `<p style="color:var(--warning);text-align:center;">Failed to load borrower: ${e.message}</p>`;
    }
}
function closeViewModal() {
    document.getElementById('view-overlay').classList.remove('active');
    document.getElementById('view-modal').classList.remove('active');
}

// ── EDIT Modal ────────────────────────────────────────────────────
async function openEditModal(id) {
    try {
        const res  = await fetch(`index.php?controller=Borrower&action=get&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        const s = data.student;
        document.getElementById('edit-studentID').value      = s.studentID;
        document.getElementById('edit-fname').value          = s.fname;
        document.getElementById('edit-mname').value          = s.mname || '';
        document.getElementById('edit-lname').value          = s.lname;
        document.getElementById('edit-nameExt').value        = s.nameExt || '';
        document.getElementById('edit-studentNumber').value  = s.studentNumber;
        document.getElementById('edit-course').value         = s.course;
        document.getElementById('edit-email').value          = s.email;

        document.getElementById('edit-overlay').classList.add('active');
        document.getElementById('edit-modal').classList.add('active');
    } catch (e) {
        alert('Failed to load borrower: ' + e.message);
    }
}
function closeEditModal() {
    document.getElementById('edit-overlay').classList.remove('active');
    document.getElementById('edit-modal').classList.remove('active');
}

// ── DELETE Modal ──────────────────────────────────────────────────
function openDeleteModal(id, name) {
    document.getElementById('delete-studentID').value           = id;
    document.getElementById('delete-borrower-name').textContent = name;
    document.getElementById('delete-overlay').classList.add('active');
    document.getElementById('delete-modal').classList.add('active');
}
function closeDeleteModal() {
    document.getElementById('delete-overlay').classList.remove('active');
    document.getElementById('delete-modal').classList.remove('active');
}

// ── Helper ────────────────────────────────────────────────────────
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Auto-dismiss toast ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.toast');
    if (toast) setTimeout(() => toast.remove(), 4000);
});