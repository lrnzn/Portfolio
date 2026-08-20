(function () {
    const iconHref = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css';

    if (!document.querySelector(`link[href="${iconHref}"]`)) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = iconHref;
        document.head.appendChild(link);
    }

    const icons = new Map([
        ['🏛️', ['bi-bank2', 'icon-muted']],
        ['🎓', ['bi-mortarboard-fill', 'icon-info']],
        ['👩‍💼', ['bi-person-badge-fill', 'icon-gold']],
        ['👤', ['bi-person-plus-fill', 'icon-purple']],
        ['👥', ['bi-people-fill', 'icon-purple']],
        ['📚', ['bi-bookshelf', 'icon-books']],
        ['📖', ['bi-book-half', 'icon-books']],
        ['📘', ['bi-book', 'icon-books']],
        ['📋', ['bi-clipboard-data-fill', 'icon-info']],
        ['📊', ['bi-bar-chart-fill', 'icon-purple']],
        ['📦', ['bi-box-seam-fill', 'icon-gold']],
        ['📤', ['bi-box-arrow-up-right', 'icon-info']],
        ['📥', ['bi-box-arrow-in-down-left', 'icon-info']],
        ['🕘', ['bi-clock-history', 'icon-muted']],
        ['✅', ['bi-check-circle-fill', 'icon-success']],
        ['❌', ['bi-x-circle-fill', 'icon-danger']],
        ['⚠️', ['bi-exclamation-triangle-fill', 'icon-warning']],
        ['⚠', ['bi-exclamation-triangle-fill', 'icon-warning']],
        ['➕', ['bi-plus-lg', 'icon-purple']],
        ['✏️', ['bi-pencil-fill', 'icon-info']],
        ['✏', ['bi-pencil-fill', 'icon-info']],
        ['🗑️', ['bi-trash3-fill', 'icon-danger']],
        ['🗑', ['bi-trash3-fill', 'icon-danger']],
        ['👁', ['bi-eye-fill', 'icon-purple']],
        ['🔍', ['bi-search', 'icon-info']],
        ['💰', ['bi-cash-coin', 'icon-gold']],
        ['⚙️', ['bi-gear-fill', 'icon-muted']],
        ['⚙', ['bi-gear-fill', 'icon-muted']],
        ['⊞', ['bi-grid-3x3-gap-fill', 'icon-muted']],
        ['☰', ['bi-list', 'icon-nav']],
        ['×', ['bi-x-lg', 'icon-nav']]
    ]);

    const tokenPattern = new RegExp(
        Array.from(icons.keys())
            .map((token) => token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
            .join('|'),
        'g'
    );

    function makeIcon(token) {
        const [iconClass, colorClass] = icons.get(token);
        const icon = document.createElement('i');
        icon.className = `bi ${iconClass} ui-icon ${colorClass}`;
        icon.setAttribute('aria-hidden', 'true');
        return icon;
    }

    function replaceTextNode(node) {
        const text = node.nodeValue;
        if (!tokenPattern.test(text)) {
            tokenPattern.lastIndex = 0;
            return;
        }

        tokenPattern.lastIndex = 0;
        const fragment = document.createDocumentFragment();
        let lastIndex = 0;
        let match;

        while ((match = tokenPattern.exec(text)) !== null) {
            if (match.index > lastIndex) {
                fragment.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
            }
            fragment.appendChild(makeIcon(match[0]));
            lastIndex = match.index + match[0].length;
        }

        if (lastIndex < text.length) {
            fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
        }

        node.parentNode.replaceChild(fragment, node);
    }

    function replaceIcons() {
        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode(node) {
                    const parent = node.parentElement;
                    if (!parent || parent.closest('script, style, textarea')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    if (parent.classList.contains('ui-icon') || parent.closest('.ui-icon')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return tokenPattern.test(node.nodeValue)
                        ? NodeFilter.FILTER_ACCEPT
                        : NodeFilter.FILTER_REJECT;
                }
            }
        );

        const nodes = [];
        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }
        nodes.forEach(replaceTextNode);

        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach((field) => {
            field.placeholder = field.placeholder.replace(/^\s*🔍\s*/, '');
        });
    }

    document.addEventListener('DOMContentLoaded', replaceIcons);
    window.LibroTrackIcons = { replace: replaceIcons };
})();
