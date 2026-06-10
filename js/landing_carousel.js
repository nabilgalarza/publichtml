/**
 * Carruseles de productos en home (Tendencias / Más vendidos).
 */
(function () {
    function scrollStep(viewport) {
        const slide = viewport.querySelector('.improgyp-carousel-slide');
        if (!slide) return viewport.clientWidth * 0.9;
        const gap = parseFloat(getComputedStyle(viewport).columnGap || getComputedStyle(viewport).gap) || 16;
        return slide.offsetWidth + gap;
    }

    function updateCarouselArrows(root) {
        const viewport = root.querySelector('.improgyp-carousel-viewport');
        const prev = root.querySelector('.improgyp-carousel-prev');
        const next = root.querySelector('.improgyp-carousel-next');
        if (!viewport || !prev || !next) return;

        const maxScroll = viewport.scrollWidth - viewport.clientWidth - 2;
        const atStart = viewport.scrollLeft <= 2;
        const atEnd = viewport.scrollLeft >= maxScroll;

        prev.disabled = atStart;
        next.disabled = atEnd;
        prev.setAttribute('aria-disabled', atStart ? 'true' : 'false');
        next.setAttribute('aria-disabled', atEnd ? 'true' : 'false');
    }

    function initCarousel(root) {
        const viewport = root.querySelector('.improgyp-carousel-viewport');
        const prev = root.querySelector('.improgyp-carousel-prev');
        const next = root.querySelector('.improgyp-carousel-next');
        if (!viewport) return;

        const onScroll = () => updateCarouselArrows(root);
        viewport.addEventListener('scroll', onScroll, { passive: true });

        prev?.addEventListener('click', () => {
            viewport.scrollBy({ left: -scrollStep(viewport), behavior: 'smooth' });
        });
        next?.addEventListener('click', () => {
            viewport.scrollBy({ left: scrollStep(viewport), behavior: 'smooth' });
        });

        updateCarouselArrows(root);
        window.addEventListener('resize', () => updateCarouselArrows(root), { passive: true });
    }

    function initAll() {
        document.querySelectorAll('.improgyp-product-carousel').forEach(initCarousel);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
