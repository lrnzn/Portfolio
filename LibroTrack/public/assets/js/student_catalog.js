// LibroTrack — Student Catalog JS | public/assets/js/student_catalog.js

function switchView(view) {
    const isGrid = view === 'grid';
    document.getElementById('books-grid').style.display = isGrid ? 'grid' : 'none';
    document.getElementById('books-list').style.display = isGrid ? 'none' : 'block';
    document.getElementById('btn-grid').classList.toggle('active', isGrid);
    document.getElementById('btn-list').classList.toggle('active', !isGrid);
}

function openBorrowModal(title) {
    document.getElementById('modal-book-title').textContent = title;
    document.getElementById('modal-overlay').classList.add('active');
    document.getElementById('modal').classList.add('active');
}

function closeModal() {
    document.getElementById('modal-overlay').classList.remove('active');
    document.getElementById('modal').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function () {
    // Start in grid view by default
    document.getElementById('books-grid').style.display = 'grid';
    document.getElementById('books-list').style.display = 'none';
});