document.documentElement.classList.add('js-enabled');

const responsivePageSizeForm = document.querySelector('[data-responsive-page-size]');

if (responsivePageSizeForm) {
    const pageSizeInput = responsivePageSizeForm.querySelector('input[name="per_page"]');
    const mobileBreakpoint = window.matchMedia('(max-width: 650px)');

    const syncPageSize = () => {
        const pageSize = mobileBreakpoint.matches ? '6' : '9';

        if (pageSizeInput.value === pageSize) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('per_page', pageSize);
        url.searchParams.delete('page');
        window.location.replace(url);
    };

    syncPageSize();
    mobileBreakpoint.addEventListener('change', syncPageSize);
}

const revealElements = document.querySelectorAll('[data-reveal]');

if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -32px 0px' },
    );

    revealElements.forEach((element) => revealObserver.observe(element));
} else {
    revealElements.forEach((element) => element.classList.add('is-visible'));
}

const parallaxElement = document.querySelector('[data-parallax]');

if (parallaxElement && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let ticking = false;

    window.addEventListener(
        'scroll',
        () => {
            if (ticking) {
                return;
            }

            ticking = true;
            window.requestAnimationFrame(() => {
                const offset = Math.min(window.scrollY * 0.12, 72);
                parallaxElement.style.setProperty('--parallax-offset', `${offset}px`);
                ticking = false;
            });
        },
        { passive: true },
    );
}
