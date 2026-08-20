// LibroTrack — Book Management JS | public/assets/js/book_management.js

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
        '<p style="color:var(--text-muted);text-align:center;padding:1rem;">Loading...</p>';

    try {
        const res  = await fetch(`index.php?controller=Book&action=get&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        const b           = data.book;
        const avail       = parseInt(b.available);
        const copies      = parseInt(b.copies);
        const badgeClass  = avail === 0 ? 'badge--overdue' : (avail <= 1 ? 'badge--borrowed' : 'badge--returned');
        const statusLabel = avail === 0 ? 'Unavailable' : `${avail} of ${copies} available`;
        const dateAdded   = new Date(b.dateAdded).toLocaleDateString('en-PH', {
            year: 'numeric', month: 'long', day: 'numeric'
        });

        const coverHtml = b.cover_image
            ? `<img src="assets/img/covers/${b.cover_image}" alt="Cover"
                    style="width:64px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--cream-dark);">`
            : `<span style="font-size:3rem;">📖</span>`;

        document.getElementById('view-modal-body').innerHTML = `
            <div style="display:grid;gap:0.75rem;">
                <div style="background:var(--cream);border-radius:12px;padding:1.25rem;display:flex;gap:1rem;align-items:center;">
                    ${coverHtml}
                    <div>
                        <h3 style="font-family:'Playfair Display',serif;font-size:1.15rem;color:var(--brown-dark);margin-bottom:0.25rem;">
                            ${escHtml(b.title)}
                        </h3>
                        <p style="font-size:0.875rem;color:var(--text-muted);">by ${escHtml(b.author)}</p>
                        <span class="badge ${badgeClass}" style="margin-top:0.5rem;display:inline-block;">${statusLabel}</span>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    ${infoBlock('Genre', b.genre)}
                    ${infoBlock('ISBN', b.isbn || '—')}
                    ${infoBlock('Total Copies', b.copies)}
                    ${infoBlock('Available', b.available)}
                    ${infoBlock('Shelf Location', b.location || '—')}
                    ${infoBlock('Date Added', dateAdded)}
                </div>
                ${b.description ? `
                <div style="background:var(--cream);border-radius:10px;padding:1rem;">
                    <p style="font-size:0.75rem;font-weight:500;color:var(--brown-mid);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:0.4rem;">Description</p>
                    <p style="font-size:0.875rem;color:var(--brown-dark);line-height:1.6;">${escHtml(b.description)}</p>
                </div>` : ''}
                <div class="modal-footer" style="margin-top:0;">
                    <button class="btn-cancel" onclick="closeViewModal()">Close</button>
                    <button class="btn-edit" style="padding:0.65rem 1.25rem;font-size:0.9rem;"
                        onclick="closeViewModal(); openEditModal(${b.bookID})">✏️ Edit</button>
                </div>
            </div>
        `;
    } catch (e) {
        document.getElementById('view-modal-body').innerHTML =
            `<p style="color:var(--warning);text-align:center;">Failed to load book: ${e.message}</p>`;
    }
}

function closeViewModal() {
    document.getElementById('view-overlay').classList.remove('active');
    document.getElementById('view-modal').classList.remove('active');
}

// ── EDIT Modal ────────────────────────────────────────────────────
async function openEditModal(id) {
    try {
        const res  = await fetch(`index.php?controller=Book&action=get&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        const b = data.book;
        document.getElementById('edit-bookID').value      = b.bookID;
        document.getElementById('edit-title').value       = b.title;
        document.getElementById('edit-author').value      = b.author;
        document.getElementById('edit-isbn').value        = b.isbn || '';
        document.getElementById('edit-genre').value       = b.genre;
        document.getElementById('edit-copies').value      = b.copies;
        document.getElementById('edit-location').value    = b.location || '';
        document.getElementById('edit-description').value = b.description || '';

        // Show current cover image if it exists
        const coverWrap    = document.getElementById('edit-current-cover');
        const coverPreview = document.getElementById('edit-cover-preview');
        const removeCheck  = document.getElementById('edit-remove-image');
        const coverInput   = document.getElementById('edit-cover-input');
        removeCheck.checked = false;
        coverInput.value    = '';
        if (b.cover_image) {
            coverPreview.src        = `assets/img/covers/${b.cover_image}`;
            coverWrap.style.display = 'block';
        } else {
            coverWrap.style.display = 'none';
        }

        document.getElementById('edit-overlay').classList.add('active');
        document.getElementById('edit-modal').classList.add('active');
    } catch (e) {
        alert('Failed to load book: ' + e.message);
    }
}

function closeEditModal() {
    document.getElementById('edit-overlay').classList.remove('active');
    document.getElementById('edit-modal').classList.remove('active');
}

// ── DELETE Modal ──────────────────────────────────────────────────
function openDeleteModal(id, title) {
    document.getElementById('delete-bookID').value           = id;
    document.getElementById('delete-book-title').textContent = title;
    document.getElementById('delete-overlay').classList.add('active');
    document.getElementById('delete-modal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('delete-overlay').classList.remove('active');
    document.getElementById('delete-modal').classList.remove('active');
}

// ── Helpers ───────────────────────────────────────────────────────
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function infoBlock(label, value) {
    return `
        <div style="background:var(--cream);border-radius:10px;padding:0.75rem 1rem;">
            <p style="font-size:0.72rem;font-weight:500;color:var(--brown-mid);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:0.25rem;">${label}</p>
            <p style="font-size:0.9rem;color:var(--brown-dark);font-weight:500;">${escHtml(String(value))}</p>
        </div>`;
}

// ── Auto-dismiss toast ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.toast');
    if (toast) setTimeout(() => toast.remove(), 4000);
});