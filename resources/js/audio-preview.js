// A single audio element keeps previews from overlapping.
const audio = new Audio();
audio.preload = 'none';
let active = null;
let interacted = false;
let fadeTimer = null;
let previewTimer = null;

function markPlaying(element, playing) {
    element?.classList.toggle('is-playing', playing);
    const button = element?.matches?.('[data-preview-button]') ? element : element?.querySelector?.('[data-preview-button]');
    button?.setAttribute('aria-pressed', String(playing));
    if (button) button.setAttribute('aria-label', `${playing ? 'Pause' : 'Play'} preview`);
}

function stop({ fade = false } = {}) {
    clearInterval(fadeTimer);
    clearTimeout(previewTimer);
    const previous = active;
    active = null;
    markPlaying(previous, false);
    if (!fade || audio.paused) {
        audio.pause();
        audio.currentTime = 0;
        audio.volume = 1;
        return;
    }
    fadeTimer = setInterval(() => {
        audio.volume = Math.max(0, audio.volume - 0.25);
        if (audio.volume <= 0) {
            clearInterval(fadeTimer);
            audio.pause();
            audio.currentTime = 0;
            audio.volume = 1;
        }
    }, 35);
}

async function play(element, url, startTime = 0, { requireInteraction = true } = {}) {
    if (!url || (requireInteraction && !interacted)) return;
    stop();
    active = element;
    audio.src = url;
    audio.volume = 1;
    audio.currentTime = Number(startTime) || 0;
    try {
        await audio.play();
        if (active !== element) return;
        markPlaying(element, true);
        previewTimer = setTimeout(() => stop({ fade: true }), 10000);
    } catch {
        stop(); // Browser autoplay can fail; explicit play remains available.
    }
}

window.addEventListener('pointerdown', () => { interacted = true; }, { once: true });
window.addEventListener('keydown', () => { interacted = true; }, { once: true });
audio.addEventListener('ended', () => stop());

document.querySelectorAll('[data-preview-card]').forEach((card) => {
    card.addEventListener('mouseenter', () => {
        if (matchMedia('(hover: hover) and (pointer: fine)').matches) play(card, card.dataset.audioUrl, card.dataset.startTime);
    });
    card.addEventListener('mouseleave', () => { if (active === card) stop({ fade: true }); });
});
document.querySelectorAll('[data-preview-button]').forEach((button) => {
    button.addEventListener('click', () => {
        interacted = true;
        const owner = button.closest('[data-preview-card]') || button;
        if (active === owner) stop({ fade: true });
        else play(owner, button.dataset.audioUrl, button.dataset.startTime, { requireInteraction: false });
    });
});

export const preview = { play, stop, hasInteracted: () => interacted };
