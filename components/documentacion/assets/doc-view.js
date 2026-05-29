(function () {
    const root = document.getElementById('doc-view');
    if (!root) return;

    const shell = root.querySelector('.doc-view-shell');
    const body = document.getElementById('doc-body-scroll');
    const sections = () => root.querySelectorAll('.doc-section');
    const navBtns = () => root.querySelectorAll('.doc-nav-btn[data-doc]');
    const searchInput = document.getElementById('doc-search');
    const toggleView = document.getElementById('doc-toggle-view');

    function isViewAll() {
        return shell && shell.classList.contains('doc-view-all');
    }

    function updateUrl(seccion, modoCompleto) {
        const params = new URLSearchParams();
        params.set('view', 'ayuda');
        if (seccion && seccion !== 'inicio') params.set('seccion', seccion);
        if (modoCompleto) params.set('modo', 'completo');
        const qs = params.toString();
        if (history.replaceState) {
            history.replaceState(null, '', 'dashboard.php?' + qs);
        }
    }

    function setViewAll(on) {
        if (!shell) return;
        shell.classList.toggle('doc-view-all', on);
        if (toggleView) {
            toggleView.setAttribute('aria-pressed', on ? 'true' : 'false');
            toggleView.textContent = on ? 'Una sección' : 'Ver toda la guía';
        }
        const list = sections();
        if (on) {
            list.forEach((s) => s.classList.add('active'));
        } else {
            list.forEach((s) => s.classList.remove('active'));
            const activeBtn = root.querySelector('.doc-nav-btn.active');
            const id = activeBtn ? activeBtn.getAttribute('data-doc') : 'inicio';
            const sec = document.getElementById('doc-' + id);
            if (sec) sec.classList.add('active');
        }
        const active = root.querySelector('.doc-nav-btn.active');
        updateUrl(active ? active.getAttribute('data-doc') : 'inicio', on);
    }

    function showSection(id) {
        navBtns().forEach((b) => b.classList.remove('active'));
        const btn = root.querySelector('.doc-nav-btn[data-doc="' + id + '"]');
        if (btn) btn.classList.add('active');

        if (isViewAll()) {
            const sec = document.getElementById('doc-' + id);
            if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
            updateUrl(id, true);
            return;
        }

        sections().forEach((s) => s.classList.remove('active'));
        const sec = document.getElementById('doc-' + id);
        if (sec) sec.classList.add('active');
        if (body) body.scrollTop = 0;
        updateUrl(id, false);
    }

    navBtns().forEach((btn) => {
        btn.addEventListener('click', () => showSection(btn.getAttribute('data-doc')));
    });

    root.querySelectorAll('.doc-faq-q').forEach((q) => {
        q.addEventListener('click', () => q.closest('.doc-faq-item').classList.toggle('open'));
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            navBtns().forEach((btn) => {
                const text = btn.textContent.toLowerCase();
                btn.classList.toggle('doc-hidden-nav', q !== '' && !text.includes(q));
            });
        });
    }

    if (toggleView) {
        toggleView.addEventListener('click', () => setViewAll(!isViewAll()));
    }

    const startCompleto = root.getAttribute('data-modo-completo') === '1';
    const startSeccion = root.getAttribute('data-seccion') || 'inicio';

    if (startCompleto) {
        setViewAll(true);
        if (startSeccion !== 'inicio') {
            const sec = document.getElementById('doc-' + startSeccion);
            if (sec) setTimeout(() => sec.scrollIntoView({ behavior: 'auto', block: 'start' }), 50);
        }
    } else {
        showSection(startSeccion);
    }

    window.verSeccionDoc = showSection;
})();
