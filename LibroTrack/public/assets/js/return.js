// LibroTrack — Return Page JS | public/assets/js/return.js

const PENALTY_RATE = 5.00;
let selectedDueDate = null;
let studentTimer    = null;

// ── Student Live Search ───────────────────────────────────────────
document.getElementById('student-search').addEventListener('input', function () {
    clearTimeout(studentTimer);
    const val     = this.value.trim();
    const results = document.getElementById('student-results');

    // Reset book section when user changes the search input
    resetBookSection();
    document.getElementById('student-preview').style.display = 'none';
    document.getElementById('input-studentID') && (document.getElementById('input-studentID').value = '');

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
            const s  = data.student;
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="ls-main">${escHtml(s.fname + ' ' + s.lname)} — ${escHtml(s.studentNumber)}</span>
                <span class="ls-sub">${escHtml(s.course)} &nbsp;|&nbsp; Active borrows: ${s.active_borrows}</span>
                <span class="ls-badge ${parseInt(s.active_borrows) > 0 ? 'ls-badge--warn' : 'ls-badge--ok'}">
                    ${parseInt(s.active_borrows) > 0 ? s.active_borrows + ' Active' : 'No Borrows'}
                </span>`;

            if (parseInt(s.active_borrows) > 0) {
                li.addEventListener('click', () => selectStudent(s));
            } else {
                li.style.opacity = '0.6';
                li.style.cursor  = 'not-allowed';
                li.title = 'This student has no active borrows.';
            }
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

async function selectStudent(s) {
    document.getElementById('student-search').value             = `${s.fname} ${s.lname} — ${s.studentNumber}`;
    document.getElementById('student-preview').style.display    = 'flex';
    document.querySelector('#student-preview .preview-name').textContent   = `${s.fname} ${s.lname}`;
    document.querySelector('#student-preview .preview-meta').textContent   = `${s.studentNumber} | ${s.course}`;
    document.querySelector('#student-preview .preview-status').textContent = `Active borrows: ${s.active_borrows}`;
    document.querySelector('#student-preview .preview-badge').textContent  = 'Selected';
    document.querySelector('#student-preview .preview-badge').className    = 'badge badge--borrowed preview-badge';
    hideResults(document.getElementById('student-results'));

    await loadActiveBorrows(s.studentID);
}

function clearStudent() {
    const el = document.getElementById('student-search');
    if (el) el.value = '';
    document.getElementById('student-preview').style.display = 'none';
    resetBookSection();
}

// ── Load active borrows for selected student ──────────────────────
async function loadActiveBorrows(studentID) {
    try {
        const res  = await fetch(`index.php?controller=Transaction&action=getActiveBorrows&studentID=${studentID}`);
        const data = await res.json();
        const select = document.getElementById('book-select');

        select.innerHTML = '<option value="" disabled selected hidden>-- Select book to return --</option>';

        data.borrows.forEach(b => {
            const overdueTxt = b.daysOverdue > 0 ? ` — ⚠️ ${b.daysOverdue}d overdue` : '';
            const opt = document.createElement('option');
            opt.value                 = b.transactionID;
            opt.dataset.daysOverdue   = b.daysOverdue;
            opt.dataset.penaltyAmount = b.penaltyAmount;
            opt.dataset.dueDate       = b.dueDate;
            opt.dataset.title         = b.title;
            opt.textContent           = `${b.title} (Due: ${b.dueDate}${overdueTxt})`;
            select.appendChild(opt);
        });

        document.getElementById('book-select-group').style.display = 'block';
    } catch (e) {
        console.error('Failed to load borrows:', e);
    }
}

// ── Book dropdown change ──────────────────────────────────────────
function onBookChange(select) {
    const option = select.options[select.selectedIndex];
    resetReturnDetails();

    if (!select.value) return;

    selectedDueDate = option.dataset.dueDate;
    document.getElementById('input-transactionID').value = select.value;

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('return-date').value = today;

    document.getElementById('return-details').style.display = 'grid';
    document.getElementById('confirm-btn').style.display    = 'block';

    recalculatePenalty(today);
}

// ── Recalculate penalty when return date changes ──────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('return-date').addEventListener('change', function () {
        if (selectedDueDate) recalculatePenalty(this.value);
    });

    document.getElementById('student-preview').style.display    = 'none';
    document.getElementById('book-select-group').style.display  = 'none';
    document.getElementById('overdue-box').style.display        = 'none';
    document.getElementById('return-details').style.display     = 'none';
    document.getElementById('penalty-paid-group').style.display = 'none';
    document.getElementById('confirm-btn').style.display        = 'none';

    const toast = document.querySelector('.toast');
    if (toast) setTimeout(() => toast.remove(), 4000);
});

function recalculatePenalty(returnDateStr) {
    if (!selectedDueDate) return;

    const dueDate     = new Date(selectedDueDate);
    const returnDate  = new Date(returnDateStr);
    const diffMs      = returnDate - dueDate;
    const daysOverdue = diffMs > 0 ? Math.floor(diffMs / (1000 * 60 * 60 * 24)) : 0;
    const penalty     = daysOverdue * PENALTY_RATE;

    document.getElementById('input-daysOverdue').value = daysOverdue;

    if (daysOverdue > 0) {
        document.getElementById('overdue-days').textContent     = daysOverdue;
        document.getElementById('overdue-penalty').textContent  = `₱${penalty.toFixed(2)}`;
        document.getElementById('overdue-box').style.display    = 'block';
        document.getElementById('penalty-amount-display').value = `₱${penalty.toFixed(2)}`;
        document.getElementById('penalty-paid-group').style.display = 'block';
    } else {
        document.getElementById('overdue-box').style.display        = 'none';
        document.getElementById('penalty-amount-display').value     = 'No penalty';
        document.getElementById('penalty-paid-group').style.display = 'none';
    }
}

// ── Reset helpers ─────────────────────────────────────────────────
function resetBookSection() {
    const select = document.getElementById('book-select');
    if (select) select.innerHTML = '<option value="" disabled selected hidden>-- Select student first --</option>';
    document.getElementById('book-select-group').style.display = 'none';
    resetReturnDetails();
}

function resetReturnDetails() {
    selectedDueDate = null;
    document.getElementById('input-transactionID').value        = '';
    document.getElementById('input-daysOverdue').value          = '0';
    document.getElementById('overdue-box').style.display        = 'none';
    document.getElementById('return-details').style.display     = 'none';
    document.getElementById('penalty-paid-group').style.display = 'none';
    document.getElementById('confirm-btn').style.display        = 'none';
}

// ── Hide results when clicking outside ───────────────────────────
document.addEventListener('click', function (e) {
    if (!e.target.closest('.livesearch-wrap')) {
        const results = document.getElementById('student-results');
        if (results) hideResults(results);
    }
});

function hideResults(el) {
    el.classList.remove('active');
    el.innerHTML = '';
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}