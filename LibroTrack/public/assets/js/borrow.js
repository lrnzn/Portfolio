// LibroTrack — Borrow Page JS | public/assets/js/borrow.js

let studentTimer = null;
let bookTimer    = null;

// ── Student Live Search ───────────────────────────────────────────
document.getElementById('student-search').addEventListener('input', function () {
    clearTimeout(studentTimer);
    const val = this.value.trim();
    const results = document.getElementById('student-results');

    if (val.length < 2) {
        hideResults(results);
        return;
    }
    studentTimer = setTimeout(() => searchStudent(val, results), 300);
});

async function searchStudent(query, results) {
    try {
        const res  = await fetch(`index.php?controller=Transaction&action=findStudent&q=${encodeURIComponent(query)}`);
        const data = await res.json();

        results.innerHTML = '';

        if (data.success) {
            const s   = data.student;
            const li  = document.createElement('li');
            li.innerHTML = `
                <span class="ls-main">${escHtml(s.fname + ' ' + s.lname)} — ${escHtml(s.studentNumber)}</span>
                <span class="ls-sub">${escHtml(s.course)} &nbsp;|&nbsp; Active borrows: ${s.active_borrows}</span>
                <span class="ls-badge ${s.active_borrows > 0 ? 'ls-badge--warn' : 'ls-badge--ok'}">
                    ${s.active_borrows > 0 ? s.active_borrows + ' Active' : 'No Borrows'}
                </span>`;
            li.addEventListener('click', () => selectStudent(s));
            results.appendChild(li);
        } else {
            const li = document.createElement('li');
            li.className = 'ls-empty';
            li.textContent = 'No student found.';
            results.appendChild(li);
        }

        results.classList.add('active');
    } catch (e) {
        console.error('Student search failed:', e);
    }
}

function selectStudent(s) {
    document.getElementById('input-studentID').value            = s.studentID;
    document.getElementById('student-search').value             = `${s.fname} ${s.lname} — ${s.studentNumber}`;
    document.getElementById('student-preview').style.display    = 'flex';
    document.querySelector('#student-preview .preview-name').textContent   = `${s.fname} ${s.lname}`;
    document.querySelector('#student-preview .preview-meta').textContent   = `${s.studentNumber} | ${s.course}`;
    document.querySelector('#student-preview .preview-status').textContent = `Active borrows: ${s.active_borrows}`;
    document.querySelector('#student-preview .preview-badge').textContent  = 'Selected';
    document.querySelector('#student-preview .preview-badge').className    = 'badge badge--returned preview-badge';
    hideResults(document.getElementById('student-results'));
}

function clearStudent() {
    document.getElementById('input-studentID').value         = '';
    document.getElementById('student-search').value          = '';
    document.getElementById('student-preview').style.display = 'none';
}

// ── Book Live Search ──────────────────────────────────────────────
document.getElementById('book-search').addEventListener('input', function () {
    clearTimeout(bookTimer);
    const val = this.value.trim();
    const results = document.getElementById('book-results');

    if (val.length < 2) {
        hideResults(results);
        return;
    }
    bookTimer = setTimeout(() => searchBook(val, results), 300);
});

async function searchBook(query, results) {
    try {
        const res  = await fetch(`index.php?controller=Transaction&action=findBook&q=${encodeURIComponent(query)}`);
        const data = await res.json();

        results.innerHTML = '';

        if (data.success) {
            const b       = data.book;
            const avail   = parseInt(b.available);
            const li      = document.createElement('li');
            const badgeCls = avail === 0 ? 'ls-badge--unavail' : 'ls-badge--ok';
            const badgeTxt = avail === 0 ? 'Unavailable' : `${avail}/${b.copies} available`;

            li.innerHTML = `
                <span class="ls-main">${escHtml(b.title)}</span>
                <span class="ls-sub">${escHtml(b.author)} &nbsp;|&nbsp; ${escHtml(b.genre)}</span>
                <span class="ls-badge ${badgeCls}">${badgeTxt}</span>`;

            if (avail > 0) {
                li.addEventListener('click', () => selectBook(b));
            } else {
                li.style.opacity = '0.6';
                li.style.cursor  = 'not-allowed';
            }
            results.appendChild(li);
        } else {
            const li = document.createElement('li');
            li.className = 'ls-empty';
            li.textContent = 'No book found.';
            results.appendChild(li);
        }

        results.classList.add('active');
    } catch (e) {
        console.error('Book search failed:', e);
    }
}

function selectBook(b) {
    document.getElementById('input-bookID').value            = b.bookID;
    document.getElementById('book-search').value             = b.title;
    document.getElementById('book-preview').style.display    = 'flex';
    document.querySelector('#book-preview .preview-name').textContent   = b.title;
    document.querySelector('#book-preview .preview-meta').textContent   = `${b.author} | ${b.genre}`;
    document.querySelector('#book-preview .preview-status').textContent = `Available: ${b.available}/${b.copies} copies`;
    document.querySelector('#book-preview .preview-badge').textContent  = 'Selected';
    document.querySelector('#book-preview .preview-badge').className    = 'badge badge--returned preview-badge';
    hideResults(document.getElementById('book-results'));
}

function clearBook() {
    document.getElementById('input-bookID').value        = '';
    document.getElementById('book-search').value         = '';
    document.getElementById('book-preview').style.display = 'none';
}

// ── Hide results when clicking outside ───────────────────────────
document.addEventListener('click', function (e) {
    if (!e.target.closest('.livesearch-wrap')) {
        hideResults(document.getElementById('student-results'));
        hideResults(document.getElementById('book-results'));
    }
});

function hideResults(el) {
    el.classList.remove('active');
    el.innerHTML = '';
}

// ── Auto due date ─────────────────────────────────────────────────
document.getElementById('borrow-date').addEventListener('change', function () {
    const borrow = new Date(this.value);
    borrow.setDate(borrow.getDate() + 7);
    document.getElementById('due-date').value = borrow.toISOString().split('T')[0];
});

// ── Init ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('borrow-date').value = today;
    const due = new Date();
    due.setDate(due.getDate() + 7);
    document.getElementById('due-date').value = due.toISOString().split('T')[0];

    document.getElementById('student-preview').style.display = 'none';
    document.getElementById('book-preview').style.display    = 'none';

    const toast = document.querySelector('.toast');
    if (toast) setTimeout(() => toast.remove(), 4000);
});

// ── Form validation ───────────────────────────────────────────────
document.getElementById('borrow-form').addEventListener('submit', function (e) {
    if (!document.getElementById('input-studentID').value) {
        e.preventDefault();
        alert('Please select a valid student first.');
        return;
    }
    if (!document.getElementById('input-bookID').value) {
        e.preventDefault();
        alert('Please select an available book first.');
    }
});

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}