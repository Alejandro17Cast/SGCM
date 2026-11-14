<?php

$rolUsuario = $_SESSION['usuario']['rol'] ?? '';

$paginaActiva = $paginaActiva ?? '';

$baseUrl = '/SGCM';

?>

<aside class="app-sidebar">

    <!-- MARCA -->

    <a href="<?= $baseUrl ?>/dashboard/index.php"
       class="sidebar-brand">

        <span class="brand-icon">✚</span>

        <span>SGCM</span>

    </a>


    <!-- MENÚ PRINCIPAL -->

    <div class="sidebar-label">
        MENÚ PRINCIPAL
    </div>

    <nav class="sidebar-nav">

        <a href="<?= $baseUrl ?>/dashboard/index.php"
           class="nav-link <?= $paginaActiva === 'inicio' ? 'active' : '' ?>">

            <span class="nav-icon">⌂</span>

            <span>Inicio</span>

        </a>


        <?php if ($rolUsuario === 'administrador'): ?>

            <a href="<?= $baseUrl ?>/modules/pacientes/index.php"
               class="nav-link <?= $paginaActiva === 'pacientes' ? 'active' : '' ?>">

                <span class="nav-icon">♙</span>

                <span>Pacientes</span>

            </a>

        <?php endif; ?>


       <a href="<?= $baseUrl ?>/modules/citas/index.php"
   class="nav-link <?= $paginaActiva === 'citas' ? 'active' : '' ?>">

    <span class="nav-icon">▦</span>

    <span>Citas</span>

</a>


        <a href="#"
           class="nav-link disabled"
           aria-disabled="true">

            <span class="nav-icon">⚕</span>

            <span>Médicos</span>

        </a>


        <a href="#"
           class="nav-link disabled"
           aria-disabled="true">

            <span class="nav-icon">▤</span>

            <span>Reportes</span>

        </a>

    </nav>


    <!-- CONFIGURACIÓN -->

    <div class="sidebar-label">
        CONFIGURACIÓN
    </div>

    <nav class="sidebar-nav">

        <a href="#"
           class="nav-link disabled"
           aria-disabled="true">

            <span class="nav-icon">⚙</span>

            <span>Configuración</span>

        </a>

    </nav>


    <!-- PIE DEL MENÚ -->

    <div class="sidebar-bottom">

        <span class="sidebar-version">
            SGCM · Versión 1.0.0
        </span>

        <span>
            Sistema de Gestión de Citas Médicas
        </span>

    </div>

</aside>