<script>
    (() => {
        const root = document.documentElement;
        const storageKey = '33half-theme';
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
        const themeColor = document.querySelector('meta[name="theme-color"]');
        let preference = null;
        let toggle;

        function readPreference() {
            try {
                const saved = localStorage.getItem(storageKey);
                return saved === 'light' || saved === 'dark' ? saved : null;
            } catch {
                return preference;
            }
        }

        function applyTheme() {
            const theme = preference ?? (systemTheme.matches ? 'dark' : 'light');
            const isDark = theme === 'dark';
            root.dataset.theme = theme;
            themeColor.content = isDark ? '#231714' : '#350021';
            toggle?.setAttribute('aria-pressed', String(isDark));
            if (toggle) toggle.title = isDark ? 'Switch to light mode' : 'Switch to dark mode';
        }

        preference = readPreference();
        applyTheme();

        document.addEventListener('DOMContentLoaded', () => {
            toggle = document.querySelector('[data-theme-toggle]');
            if (!toggle) return;

            applyTheme();
            toggle.hidden = false;
            toggle.addEventListener('click', () => {
                preference = root.dataset.theme === 'dark' ? 'light' : 'dark';
                try {
                    localStorage.setItem(storageKey, preference);
                } catch {
                    // Keep the choice for the current page when storage is unavailable.
                }
                root.classList.add('theme-interactive');
                applyTheme();
            });
        });

        systemTheme.addEventListener('change', applyTheme);
        window.addEventListener('storage', (event) => {
            if (event.key !== storageKey && event.key !== null) return;
            preference = readPreference();
            applyTheme();
        });
        window.addEventListener('pageshow', (event) => {
            if (!event.persisted) return;
            preference = readPreference();
            applyTheme();
        });
    })();
</script>
