// LibroTrack — History Page JS | public/assets/js/history.js

// ── EDIT Modal ────────────────────────────────────────────────────
async function openEditModal(id) {
    try {
        const res  = await fetch(`index.php?controller=Transaction&action=get&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        const t = data.transaction;
        document.getElementById('edit-transactionID').value = t.transactionID;
        document.getElementById('edit-borrowDate').value    = t.borrowDate;
        document.getElementById('edit-dueDate').value       = t.dueDate;
        document.getElementById('edit-info').textContent    =
            `${t.studentName} — ${t.bookTitle}`;

        document.getElementById('edit-overlay').classList.add('active');
        document.getElementById('edit-modal').classList.add('active');
    } catch (e) {
        alert('Failed to load transaction: ' + e.message);
    }
}
function closeEditModal() {
    document.getElementById('edit-overlay').classList.remove('active');
    document.getElementById('edit-modal').classList.remove('active');
}

// ── DELETE Modal ──────────────────────────────────────────────────
function openDeleteModal(id, label) {
    document.getElementById('delete-transactionID').value      = id;
    document.getElementById('delete-transaction-label').textContent = label;
    document.getElementById('delete-overlay').classList.add('active');
    document.getElementById('delete-modal').classList.add('active');
}
function closeDeleteModal() {
    document.getElementById('delete-overlay').classList.remove('active');
    document.getElementById('delete-modal').classList.remove('active');
}

// ── Auto-dismiss toast ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.toast');
    if (toast) setTimeout(() => toast.remove(), 4000);
});