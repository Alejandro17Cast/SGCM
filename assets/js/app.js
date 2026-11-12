(() => {
  const root = document.documentElement;
  const themeToggle = document.getElementById('themeToggle');
  const themeText = document.getElementById('themeText');
  const themeIcon = document.getElementById('themeIcon');
  const passwordToggle = document.getElementById('passwordToggle');
  const password = document.getElementById('password');

  function renderThemeControl() {
    const dark = root.dataset.theme === 'dark';
    themeText.textContent = dark ? 'Modo claro' : 'Modo oscuro';
    themeIcon.textContent = dark ? '☀' : '☾';
    themeToggle.setAttribute('aria-label', dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
  }

  themeToggle?.addEventListener('click', () => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
    root.dataset.theme = next;
    localStorage.setItem('sgcm-theme', next);
    renderThemeControl();
  });

  passwordToggle?.addEventListener('click', () => {
    const showing = password.type === 'text';
    password.type = showing ? 'password' : 'text';
    passwordToggle.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
  });

  renderThemeControl();
})();
