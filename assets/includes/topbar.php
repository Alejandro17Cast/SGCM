<?php

$nombreUsuario = $_SESSION['usuario']['nombre'] ?? 'Usuario';

$apellidoUsuario = $_SESSION['usuario']['apellido'] ?? '';

$nombreCompleto = htmlspecialchars(
    $nombreUsuario . ' ' . $apellidoUsuario,
    ENT_QUOTES,
    'UTF-8'
);

$inicial = strtoupper(
    substr($nombreUsuario, 0, 1)
);

?>

<header class="app-topbar">

    <div class="topbar-welcome">

        <p class="topbar-eyebrow">
            SISTEMA DE GESTIÓN MÉDICA
        </p>

        <h1>
            <?= $nombreCompleto ?>
        </h1>

        <p>
            Bienvenido de nuevo a SGCM.
        </p>

    </div>


    <div class="topbar-actions">

        <button
            class="theme-button"
            id="themeToggle"
            type="button"
            aria-label="Cambiar tema">

            <span id="themeIcon">☾</span>

            <span id="themeText">
                Modo oscuro
            </span>

        </button>


        <div class="user-avatar">

            <?= htmlspecialchars(
                $inicial,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>


        <a href="/SGCM/auth/logout.php"
           class="logout-button">

            Cerrar sesión

        </a>

    </div>

</header>