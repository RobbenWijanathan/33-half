function restoreFilterFocus(root, previous) {
    if (!previous) return;

    if (previous instanceof HTMLInputElement || previous instanceof HTMLSelectElement) {
        const controls = root.querySelectorAll('[data-filter-form] [name]');
        const match = [...controls].find((control) => control.name === previous.name
            && (control.type !== 'radio' || control.value === previous.value));
        match?.focus({ preventScroll: true });
    } else if (previous.dataset.filterLink) {
        root.querySelector('[data-filter-link="' + CSS.escape(previous.dataset.filterLink) + '"]')
            ?.focus({ preventScroll: true });
    }
}

export function initLiveFilters(onRefresh) {
    let root = document.querySelector('[data-live-filters]');
    const announcement = document.querySelector('[data-filter-announcement]');
    if (!root) return;

    let pending;

    async function update(url, { fromHistory = false, paginate = false } = {}) {
        pending?.abort();
        const request = new AbortController();
        pending = request;
        root.setAttribute('aria-busy', 'true');
        const search = document.getElementById('site-search');
        const originalSearch = search?.value;

        try {
            const response = await fetch(url, {
                headers: { Accept: 'text/html' },
                signal: request.signal,
            });
            if (!response.ok) throw new Error('Could not load the results');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const next = page.querySelector('[data-live-filters]');
            if (!next) throw new Error('Could not find the results');
            if (request.signal.aborted) return;

            const focused = root.contains(document.activeElement) ? document.activeElement : null;
            root.replaceWith(next);
            root = next;
            if (search && search !== document.activeElement && search.value === originalSearch) {
                search.value = page.getElementById('site-search')?.value ?? '';
            }
            onRefresh(root);
            if (!fromHistory && url.href !== location.href) history.pushState(null, '', url);

            if (paginate) {
                const heading = document.querySelector('main h1');
                heading?.setAttribute('tabindex', '-1');
                heading?.focus({ preventScroll: true });
                heading?.scrollIntoView({ behavior: 'instant' });
            } else {
                restoreFilterFocus(root, focused);
            }

            const count = root.querySelector('.results-count')?.textContent.trim();
            announcement.textContent = count ? count + ' in the results.' : 'Results updated.';
        } catch {
            if (request.signal.aborted) return;
            if (fromHistory) location.replace(url);
            else location.assign(url);
        } finally {
            if (pending === request) root.removeAttribute('aria-busy');
        }
    }

    document.addEventListener('change', (event) => {
        const form = event.target.closest('[data-filter-form]');
        if (form && root.contains(form)) form.requestSubmit();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('[data-filter-form]') || !root.contains(form)) return;

        event.preventDefault();
        const url = new URL(form.action);
        const entries = [...new FormData(form)].filter(([, value]) => value !== '');
        url.search = new URLSearchParams(entries);
        update(url);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-filter-link], .pagination a');
        if (!link || !root.contains(link) || event.defaultPrevented || event.button !== 0
            || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const url = new URL(link.href);
        if (url.origin !== location.origin || url.pathname !== location.pathname) return;
        event.preventDefault();
        update(url, { paginate: Boolean(link.closest('.pagination')) });
    });

    window.addEventListener('popstate', () => update(new URL(location.href), { fromHistory: true }));
}
