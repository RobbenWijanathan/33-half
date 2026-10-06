import { preview } from './audio-preview';

const stage = document.querySelector('[data-crate-stage]');
if (stage) {
    const cards = [...stage.querySelectorAll('[data-crate-card]')];
    const counter = document.querySelector('[data-crate-counter]');
    let order = cards.map((_, index) => index);
    let position = 0;
    let lastWheel = 0;
    let touchStartY = null;

    const show = (next, shouldPlay = true) => {
        if (!cards.length) return;
        preview.stop();
        position = (next + order.length) % order.length;
        cards.forEach((card) => { card.hidden = true; card.classList.remove('is-playing'); });
        const card = cards[order[position]];
        card.hidden = false;
        counter.textContent = `${String(position + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
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
    stage.addEventListener('wheel', (event) => {
        if (Math.abs(event.deltaY) < 12 || Date.now() - lastWheel < 650) return;
        lastWheel = Date.now();
        show(position + (event.deltaY > 0 ? 1 : -1));
    }, { passive: true });
    stage.addEventListener('touchstart', (event) => { touchStartY = event.changedTouches[0].clientY; }, { passive: true });
    stage.addEventListener('touchend', (event) => {
        if (touchStartY === null) return;
        const delta = touchStartY - event.changedTouches[0].clientY;
        if (Math.abs(delta) > 45) show(position + (delta > 0 ? 1 : -1));
        touchStartY = null;
    }, { passive: true });
    document.addEventListener('keydown', (event) => {
        if (event.target instanceof HTMLElement && ['INPUT', 'SELECT', 'TEXTAREA'].includes(event.target.tagName)) return;
        if (event.key === 'ArrowRight') show(position + 1);
        if (event.key === 'ArrowLeft') show(position - 1);
    });
    show(0, false);
}
