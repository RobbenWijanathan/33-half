
import { preview } from './audio-preview';

function initCarousel(carousel) {
    const scroller = carousel.querySelector('[data-carousel-window]');
    const records = [...carousel.querySelectorAll('[data-carousel-record]')];
    const captions = [...carousel.querySelectorAll('[data-carousel-caption]')];
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const counter = carousel.querySelector('[data-carousel-counter]');
    const announcement = carousel.querySelector('[data-carousel-announcement]');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let focusedIndex = 0;
    let scrollFrame = null;
    let wheelAmount = 0;
    let lastWheelEvent = 0;
    let lastWheelStep = -Infinity;

    // The nearest cover to the left edge is the single source of truth for captions.
    function focusRecord(index) {
        if (index === focusedIndex) return;
        preview.stop();
        focusedIndex = index;

        records.forEach((record, position) => {
            const focused = position === index;
            record.classList.toggle('is-focused', focused);
            record.setAttribute('aria-pressed', String(focused));
            record.tabIndex = focused ? 0 : -1;
            captions[position].hidden = !focused;
        });

        previous.disabled = index === 0;
        next.disabled = index === records.length - 1;
        counter.textContent = (index + 1) + ' / ' + records.length;
        announcement.textContent = captions[index].querySelector('h2').textContent;
    }

    function updateFromScroll() {
        const left = scroller.scrollLeft;
        let closest = 0;
        let smallestDistance = Infinity;

        records.forEach((record, index) => {
            const distance = Math.abs(record.offsetLeft - left);
            if (distance < smallestDistance) {
                closest = index;
                smallestDistance = distance;
            }
        });

        focusRecord(closest);
        scrollFrame = null;
    }

    function scrollToRecord(index, animate = true) {
        const record = records[Math.max(0, Math.min(index, records.length - 1))];
        scroller.scrollTo({
            left: record.offsetLeft,
            behavior: animate && !reducedMotion.matches ? 'smooth' : 'instant',
        });
    }

    scroller.addEventListener('scroll', () => {
        if (scrollFrame === null) scrollFrame = requestAnimationFrame(updateFromScroll);
    }, { passive: true });

    scroller.addEventListener('wheel', (event) => {
        if (!event.cancelable || event.ctrlKey || event.shiftKey || Math.abs(event.deltaX) >= Math.abs(event.deltaY)) return;

        const direction = Math.sign(event.deltaY);
        const target = focusedIndex + direction;
        if (target < 0 || target >= records.length) return;

        // At either end the same gesture can continue scrolling the page.
        event.preventDefault();
        const now = performance.now();
        const factor = event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? scroller.clientHeight : 1;
        if (now - lastWheelEvent > 160 || Math.sign(wheelAmount) !== direction) wheelAmount = 0;
        lastWheelEvent = now;
        wheelAmount += event.deltaY * factor;

        if (Math.abs(wheelAmount) < 32 || now - lastWheelStep < 230) return;
        scrollToRecord(target);
        wheelAmount = 0;
        lastWheelStep = now;
    }, { passive: false });

    records.forEach((record, index) => {
        record.addEventListener('click', () => scrollToRecord(index));
    });
    previous.addEventListener('click', () => scrollToRecord(focusedIndex - 1));
    next.addEventListener('click', () => scrollToRecord(focusedIndex + 1));

    scroller.addEventListener('keydown', (event) => {
        const destinations = {
            ArrowLeft: focusedIndex - 1,
            ArrowRight: focusedIndex + 1,
            Home: 0,
            End: records.length - 1,
        };
        if (event.key in destinations) {
            event.preventDefault();
            scrollToRecord(destinations[event.key]);
        }
    });

    // Keep the selected sleeve on the left when the mobile layout changes size.
    const observer = new ResizeObserver(() => scrollToRecord(focusedIndex, false));
    observer.observe(scroller);

    return () => {
        observer.disconnect();
        if (scrollFrame !== null) cancelAnimationFrame(scrollFrame);
    };
}

export function initCarousels(root) {
    const dispose = [...root.querySelectorAll('[data-record-carousel]')].map(initCarousel);
    return () => dispose.forEach((cleanup) => cleanup());
}
