import { initAudioPreviews, preview } from './audio-preview';
import { initCarousels } from './crate-digging';
import { initLiveFilters } from './live-filters';
import './cart';
import './record-gallery';
import './commerce';

const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#main-navigation');

if (menuButton && navigation) {
    document.documentElement.classList.add('js-ready');
    menuButton.addEventListener('click', () => {
        const expanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!expanded));
        navigation.classList.toggle('is-open', !expanded);
    });
    navigation.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            navigation.classList.remove('is-open');
            menuButton.setAttribute('aria-expanded', 'false');
            menuButton.focus();
        }
    });
}

let disposeCarousels = () => {};

function enhanceRecords(root) {
    disposeCarousels();
    disposeCarousels = initCarousels(root);
    initAudioPreviews(root);

    root.querySelectorAll('[data-art-fallback]').forEach((image) => {
        const useFallback = () => {
            if (image.src !== image.dataset.artFallback) image.src = image.dataset.artFallback;
        };
        image.addEventListener('error', useFallback);
        if (image.complete && !image.naturalWidth) useFallback();
    });
}

enhanceRecords(document);

initLiveFilters((root) => {
    preview.stop();
    enhanceRecords(root);
});

document.querySelectorAll('[data-auto-submit]').forEach((select) => {
    select.addEventListener('change', () => select.form.requestSubmit());
});
