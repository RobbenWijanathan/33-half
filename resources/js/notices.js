const toast = document.querySelector('[data-notice]');
let hideTimer;
let clearTimer;

export function showNotice(message, kind = 'success') {
    if (!toast) return;

    clearTimeout(hideTimer);
    clearTimeout(clearTimer);
    toast.textContent = message;
    toast.dataset.kind = kind;
    toast.dataset.visible = '';
    hideTimer = setTimeout(() => {
        delete toast.dataset.visible;
        clearTimer = setTimeout(() => { toast.textContent = ''; }, 180);
    }, kind === 'error' ? 5200 : 3200);
}

if (toast?.hasAttribute('data-visible')) {
    showNotice(toast.textContent.trim(), toast.dataset.kind);
}
