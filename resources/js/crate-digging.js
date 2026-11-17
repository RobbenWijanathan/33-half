
import { preview } from './audio-preview';

document.querySelectorAll('[data-record-carousel]').forEach((carousel) => {
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

    // The nearest cover to the center is the single source of truth for captions.
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
        const bounds = scroller.getBoundingClientRect();
        const center = bounds.top + scroller.clientHeight / 2;
        let closest = 0;
        let smallestDistance = Infinity;

        records.forEach((record, index) => {
            const cover = record.getBoundingClientRect();
            const distance = Math.abs(cover.top + cover.height / 2 - center);
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
        const bounds = scroller.getBoundingClientRect();
        const cover = record.getBoundingClientRect();

        scroller.scrollTo({
            top: scroller.scrollTop + cover.top + cover.height / 2 - bounds.top - scroller.clientHeight / 2,
            behavior: animate && !reducedMotion.matches ? 'smooth' : 'instant',
        });
    }

    scroller.addEventListener('scroll', () => {
        if (scrollFrame === null) scrollFrame = requestAnimationFrame(updateFromScroll);
    }, { passive: true });

    records.forEach((record, index) => {
        record.addEventListener('click', () => scrollToRecord(index));
    });
    previous.addEventListener('click', () => scrollToRecord(focusedIndex - 1));
    next.addEventListener('click', () => scrollToRecord(focusedIndex + 1));

    scroller.addEventListener('keydown', (event) => {
        const destinations = {
            ArrowUp: focusedIndex - 1,
            ArrowDown: focusedIndex + 1,
            Home: 0,
            End: records.length - 1,
        };
        if (event.key in destinations) {
            event.preventDefault();
            scrollToRecord(destinations[event.key]);
        }
    });

    // Keep the selected sleeve centered when the mobile layout changes size.
    new ResizeObserver(() => scrollToRecord(focusedIndex, false)).observe(scroller);
});

document.querySelectorAll('[data-auto-submit]').forEach((select) => {
    select.addEventListener('change', () => select.form.requestSubmit());
});
