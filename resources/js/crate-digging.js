import { preview } from './audio-preview';

const stage = document.querySelector('[data-crate-stage]');
if (stage) {
    const cards = [...stage.querySelectorAll('[data-crate-card]')];
    const counter = document.querySelector('[data-crate-counter]');
    const storageKey = 'crate-digging';
    let order = cards.map((_, index) => index);
    let position = 0;
    let touchStart = null;

    // Remember the shuffle and the open record, so a reload (for example after "Add") keeps your place.
    const remember = () => {
        try { sessionStorage.setItem(storageKey, JSON.stringify({ order, position })); } catch { /* storage unavailable */ }
    };
    const restore = () => {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey));
            const valid = Array.isArray(saved?.order) && saved.order.length === cards.length
                && [...saved.order].sort((a, b) => a - b).every((value, index) => value === index);
            if (!valid) return;
            order = saved.order;
            position = Number.isInteger(saved.position) ? saved.position : 0;
        } catch { /* nothing saved */ }
    };

    const show = (next, shouldPlay = true) => {
        if (!cards.length) return;
        preview.stop();
        position = (next + order.length) % order.length;
        cards.forEach((card) => { card.hidden = true; card.classList.remove('is-playing'); });
        const card = cards[order[position]];
        card.hidden = false;
        counter.textContent = `${String(position + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        remember();
        if (shouldPlay && preview.hasInteracted()) preview.play(card, card.dataset.audioUrl, card.dataset.startTime);
    };

    document.querySelector('[data-crate-prev]')?.addEventListener('click', () => show(position - 1));
    document.querySelector('[data-crate-next]')?.addEventListener('click', () => show(position + 1));
    document.querySelector('[data-crate-random]')?.addEventListener('click', () => {
        for (let i = order.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [order[i], order[j]] = [order[j], order[i]];
        }
        show(0);
    });
    // Vertical movement belongs to the page. Only a clearly sideways swipe flips the record.
    stage.addEventListener('touchstart', (event) => {
        const touch = event.changedTouches[0];
        touchStart = event.touches.length === 1 ? { x: touch.clientX, y: touch.clientY } : null;
    }, { passive: true });
    stage.addEventListener('touchend', (event) => {
        if (!touchStart) return;
        const touch = event.changedTouches[0];
        const deltaX = touchStart.x - touch.clientX;
        const deltaY = touchStart.y - touch.clientY;
        touchStart = null;
        if (Math.abs(deltaX) > 45 && Math.abs(deltaX) > Math.abs(deltaY) * 1.5) show(position + (deltaX > 0 ? 1 : -1));
    }, { passive: true });
    stage.addEventListener('touchcancel', () => { touchStart = null; }, { passive: true });
    document.addEventListener('keydown', (event) => {
        if (event.altKey || event.ctrlKey || event.metaKey) return;
        if (event.target instanceof HTMLElement && ['INPUT', 'SELECT', 'TEXTAREA'].includes(event.target.tagName)) return;
        if (event.key === 'ArrowRight') show(position + 1);
        if (event.key === 'ArrowLeft') show(position - 1);
    });
    restore();
    show(position, false);
}
