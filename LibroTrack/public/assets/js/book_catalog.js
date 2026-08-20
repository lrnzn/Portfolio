// LibroTrack — Book Catalog JS | public/assets/js/book_catalog.js

function switchView(view) {
    const grid    = document.getElementById('books-grid');
    const list    = document.getElementById('books-list');
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');

    if (view === 'grid') {
        grid.style.display = 'grid';
        list.style.display = 'none';
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
    } else {
        grid.style.display = 'none';
        list.style.display = 'block';
        btnGrid.classList.remove('active');
        btnList.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Start in grid view by default
    document.getElementById('books-grid').style.display = 'grid';
    document.getElementById('books-list').style.display = 'none';
});