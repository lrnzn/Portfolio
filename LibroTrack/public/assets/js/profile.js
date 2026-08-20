// LibroTrack — Profile Page JS | public/assets/js/profile.js

// Auto-dismiss toast
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.toast');
    if (toast) setTimeout(() => toast.remove(), 4000);
});
