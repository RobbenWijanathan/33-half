// A single audio element keeps previews from overlapping.
const PREVIEW_SECONDS = 10;
const audio = new Audio();
audio.preload = 'none';
let active = null;
let interacted = false;
let fadeTimer = null;
let previewTimer = null;
let unavailableTimer = null;

// Screen readers hear failures through one shared live region.
const announcer = document.createElement('div');
announcer.className = 'visually-hidden';
announcer.setAttribute('aria-live', 'polite');
document.body.append(announcer);

// A product card owns its playing state; anywhere else the button owns its own.
const ownerOf = (element) => element.closest('[data-preview-card]') || element;
const buttonOf = (owner) => (owner?.matches?.('[data-preview-button]') ? owner : owner?.querySelector?.('[data-preview-button]'));
const labelOf = (owner) => {
    const scope = owner?.matches?.('[data-preview-button]') ? owner.parentElement : owner;
    return scope?.querySelector?.('[data-preview-label]') || null;
};

function setState(owner, state) {
    if (!owner) return;
    owner.classList.toggle('is-loading', state === 'loading');
    owner.classList.toggle('is-playing', state === 'playing');
    owner.classList.toggle('is-unavailable', state === 'unavailable');

    const button = buttonOf(owner);
    const name = button?.dataset.previewName || 'this record';
    if (button) {
        button.setAttribute('aria-pressed', String(state === 'playing'));
        button.toggleAttribute('aria-busy', state === 'loading');
        button.setAttribute('aria-label', {
            playing: `Stop preview of ${name}`,
            unavailable: `Preview unavailable for ${name}. Try again`,
        }[state] || `Play preview of ${name}`);
    }

    const label = labelOf(owner);
    if (label) {
        label.dataset.idleText ??= label.textContent.trim();
        label.textContent = {
            loading: 'Loading preview…',
            playing: 'Playing. Press to stop',
            unavailable: 'Preview unavailable. Try again',
        }[state] || label.dataset.idleText;
    }
}

function resetAudio() {
    audio.pause();
    audio.currentTime = 0;
    audio.volume = 1;
}

function stop({ fade = false } = {}) {
    clearInterval(fadeTimer);
    clearTimeout(previewTimer);
    const previous = active;
    active = null;
    setState(previous, 'idle');
    if (!fade || audio.paused) {
        resetAudio();
        return;
    }
    fadeTimer = setInterval(() => {
        audio.volume = Math.max(0, audio.volume - 0.25);
        if (audio.volume <= 0) {
            clearInterval(fadeTimer);
            resetAudio();
        }
    }, 35);
}

function markUnavailable(owner) {
    setState(owner, 'unavailable');
    announcer.textContent = `Preview unavailable for ${buttonOf(owner)?.dataset.previewName || 'this record'}.`;
    clearTimeout(unavailableTimer);
    unavailableTimer = setTimeout(() => {
        announcer.textContent = '';
        if (active !== owner) setState(owner, 'idle');
    }, 4000);
}

async function play(element, url, startTime = 0, { requireInteraction = true } = {}) {
    if (!url || (requireInteraction && !interacted)) return;
    const owner = ownerOf(element);
    stop();
    active = owner;
    setState(owner, 'loading');
    audio.src = url;
    audio.volume = 1;
    audio.currentTime = Number(startTime) || 0;
    try {
        await audio.play();
        if (active !== owner) return; // Something else started or stopped while this one loaded.
        // A clip shorter than the cap still fills its ring exactly as it ends.
        const remaining = audio.duration - audio.currentTime;
        const seconds = Number.isFinite(remaining) && remaining > 0 ? Math.min(PREVIEW_SECONDS, remaining) : PREVIEW_SECONDS;
        owner.style.setProperty('--preview-duration', `${seconds}s`);
        setState(owner, 'playing');
        previewTimer = setTimeout(() => stop({ fade: true }), PREVIEW_SECONDS * 1000);
    } catch (error) {
        if (active !== owner) return;
        active = null;
        resetAudio();
        // A blocked hover autoplay is not the listener's doing; only report a preview they asked for.
        if (requireInteraction || error?.name === 'AbortError') setState(owner, 'idle');
        else markUnavailable(owner);
    }
}

window.addEventListener('pointerdown', () => { interacted = true; }, { once: true });
window.addEventListener('keydown', () => { interacted = true; }, { once: true });
audio.addEventListener('ended', () => stop());

export function initAudioPreviews(root) {
    root.querySelectorAll('[data-preview-card]').forEach((card) => {
        card.addEventListener('mouseenter', () => {
            if (matchMedia('(hover: hover) and (pointer: fine)').matches) play(card, card.dataset.audioUrl, card.dataset.startTime);
        });
        card.addEventListener('mouseleave', () => { if (active === card) stop({ fade: true }); });
    });
    root.querySelectorAll('[data-preview-button]').forEach((button) => {
        button.addEventListener('click', () => {
            interacted = true;
            const owner = ownerOf(button);
            if (active === owner) stop({ fade: true });
            else play(owner, button.dataset.audioUrl, button.dataset.startTime, { requireInteraction: false });
        });
    });
}

export const preview = { play, stop, hasInteracted: () => interacted };
