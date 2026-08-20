document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.navbar').forEach((navbar) => {
        const navLinks = navbar.querySelector('.nav-links');
        const navBrand = navbar.querySelector('.nav-brand');

        if (!navLinks || !navBrand || navbar.querySelector('.nav-toggle')) {
            return;
        }

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'nav-toggle';
        toggle.setAttribute('aria-label', 'Open navigation menu');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.textContent = '☰';

        navBrand.insertAdjacentElement('afterend', toggle);

        toggle.addEventListener('click', () => {
            const isOpen = navbar.classList.toggle('nav-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
            toggle.textContent = isOpen ? '×' : '☰';
            window.LibroTrackIcons?.replace();
        });

        navLinks.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                navbar.classList.remove('nav-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Open navigation menu');
                toggle.textContent = '☰';
            }
        });
    });
});
