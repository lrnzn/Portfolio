// LibroTrack — Reports Page JS | public/assets/js/reports.js

// When month changes, also update the hidden year input
document.addEventListener('DOMContentLoaded', function () {
    const monthSelect = document.querySelector('select[name="month"]');
    const yearInput   = document.getElementById('year-input');

    if (monthSelect && yearInput) {
        monthSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            yearInput.value = selected.dataset.year || '';
            this.form.submit();
        });
    }
});
