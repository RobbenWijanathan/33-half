document.querySelectorAll('[data-record-gallery]').forEach((gallery) => {
    const mainImage = gallery.querySelector('.artwork img');
    const thumbnails = [...gallery.querySelectorAll('[data-gallery-view]')];
    const recordName = mainImage.alt.replace(/ artwork$/, '');

    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            thumbnails.forEach((item) => item.setAttribute('aria-pressed', String(item === thumbnail)));

            const label = thumbnail.getAttribute('aria-label');
            mainImage.alt = recordName + ' — ' + label;
            if (mainImage.getAttribute('src') === thumbnail.dataset.image) return;

            mainImage.src = thumbnail.dataset.image;
            if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
                mainImage.animate([{ opacity: 0.65 }, { opacity: 1 }], { duration: 180 });
            }
        });
    });
});
