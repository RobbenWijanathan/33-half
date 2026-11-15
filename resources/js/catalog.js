// Catalog listing: the filter panel, the sort control, and the search shortcut.
document.documentElement.classList.add('js');

// Wide screens always show the filters; small screens keep them behind the summary
// so records come first on a phone. Applied filters stay visible as chips either way.
const panel = document.querySelector('[data-filter-panel]');
if (panel) {
    const wide = matchMedia('(min-width: 801px)');
    const sync = () => { panel.open = wide.matches || location.hash === '#catalog-search'; };
    sync();
    wide.addEventListener('change', sync);
    panel.querySelector('summary')?.addEventListener('click', (event) => {
        if (wide.matches) event.preventDefault(); // Nothing to collapse into on a wide layout.
    });
}

// Changing the sort applies it straight away; the submit button stays for no-JS use.
document.querySelectorAll('[data-auto-submit] select').forEach((select) => {
    select.addEventListener('change', () => select.form.requestSubmit());
});

// The header search link lands on #catalog-search; put the cursor in the field.
const focusSearch = () => {
    if (location.hash !== '#catalog-search') return;
    if (panel) panel.open = true;
    document.querySelector('#catalog-search input')?.focus();
};
focusSearch();
window.addEventListener('hashchange', focusSearch);
