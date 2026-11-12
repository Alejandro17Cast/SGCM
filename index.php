<?php
session_start();
// Punto de entrada inicial. La autenticación se conectará en la siguiente etapa.
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SGCM - Sistema de Gestión de Citas Médicas">
  <title>Iniciar sesión | SGCM</title>
  <script>
    (() => {
      const saved = localStorage.getItem('sgcm-theme');
      const preferred = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      document.documentElement.dataset.theme = saved || preferred;
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="auth-layout">
    <section class="brand-panel" aria-label="Presentación de SGCM">
      <a class="brand" href="index.php" aria-label="SGCM, inicio">
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 64 64" role="img">
            <path d="M23 5h18v13h13v18H41v13H23V36H10V18h13z"/>
            <rect x="21" y="22" width="22" height="20" rx="3"/>
            <path d="M26 18v8M38 18v8M26 34l4 4 9-9"/>
          </svg>
        </span>
        <span class="brand-name">SGCM</span>
      </a>
      <div class="brand-copy">
        <span class="eyebrow">SALUD · ORGANIZACIÓN · CONFIANZA</span>
        <h1>Tu salud merece<br><em>una mejor gestión.</em></h1>
        <p>Administra tus citas médicas de forma sencilla, segura y organizada.</p>
      </div>
      <span class="panel-caption">Sistema de Gestión de Citas Médicas</span>
    </section>

    <section class="form-panel">
      <div class="top-actions">
        <span class="secure-label"><span class="secure-dot"></span> Acceso seguro</span>
        <button class="theme-toggle" type="button" id="themeToggle" aria-label="Cambiar tema">
          <span class="theme-icon" id="themeIcon">☾</span>
          <span id="themeText">Modo oscuro</span>
        </button>
      </div>

      <div class="login-card">
        <div class="mobile-brand brand">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 64 64"><path d="M23 5h18v13h13v18H41v13H23V36H10V18h13z"/><rect x="21" y="22" width="22" height="20" rx="3"/><path d="M26 18v8M38 18v8M26 34l4 4 9-9"/></svg>
          </span>
          <span class="brand-name">SGCM</span>
        </div>
        <span class="eyebrow">BIENVENIDO/A</span>
        <h2>Inicia sesión</h2>
        <p class="form-intro">Ingresa tus credenciales para continuar.</p>
<?php if (isset($_GET['error'])): ?>

    <p class="error-message" role="alert">

        <?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
        if ($_GET['error'] === 'datos') {
            echo 'Completa correctamente tus credenciales.';
        } else {
            echo 'Correo o contraseña incorrectos.';
        }

        ?>

    </p>

<?php endif; ?>


        <form action="auth/login.php" method="POST" class="login-form">
          <label for="email">Correo electrónico</label>
          <div class="input-wrap">
            <span aria-hidden="true">✉</span>
            <input id="email" name="email" type="email" placeholder="nombre@correo.com" autocomplete="username" required>
          </div>

          <label for="password">Contraseña</label>
          <div class="input-wrap">
            <span aria-hidden="true">⌑</span>
            <input id="password" name="password" type="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required>
            <button class="password-toggle" type="button" id="passwordToggle" aria-label="Mostrar contraseña">◎</button>
          </div>

          <button class="submit-button" type="submit">Iniciar sesión <span aria-hidden="true">→</span></button>
        </form>
        <p class="form-footnote">Si necesitas ayuda para acceder, contacta al administrador del sistema.</p>
      </div>
      <footer class="page-footer">© <?php echo date('Y'); ?> SGCM · Todos los derechos reservados</footer>
    </section>
  </main>
  <script src="assets/js/app.js"></script>
</body>
</html>
