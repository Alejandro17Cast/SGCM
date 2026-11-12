(() => {
    const root = document.documentElement;

    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');

    function updateThemeControl() {

        const dark = root.dataset.theme === 'dark';

        themeIcon.textContent = dark ? '☀' : '☾';

        themeText.textContent = dark
            ? 'Modo claro'
            : 'Modo oscuro';

    }

    themeToggle.addEventListener('click', () => {

        const nextTheme = root.dataset.theme === 'dark'
            ? 'light'
            : 'dark';

        root.dataset.theme = nextTheme;

        localStorage.setItem('sgcm-theme', nextTheme);

        updateThemeControl();

    });

    updateThemeControl();

})();