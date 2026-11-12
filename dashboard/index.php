<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// =============================================
// PROTECCIÓN DE ACCESO
// =============================================

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rolUsuario = $usuario['rol'];

// =============================================
// ESTADÍSTICAS DEL SISTEMA
// =============================================

$estadisticas = [
    'pacientes' => 0,
    'medicos' => 0,
    'usuarios' => 0,
    'citas' => 0
];

try {

    // Total de pacientes activos
    $estadisticas['pacientes'] = (int) $pdo
        ->query("
            SELECT COUNT(*)
            FROM pacientes
            WHERE estado = 'activo'
        ")
        ->fetchColumn();

    // Total de médicos activos
    $estadisticas['medicos'] = (int) $pdo
        ->query("
            SELECT COUNT(*)
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE r.nombre = 'medico'
            AND u.estado = 'activo'
        ")
        ->fetchColumn();

    // Total de usuarios activos
    $estadisticas['usuarios'] = (int) $pdo
        ->query("
            SELECT COUNT(*)
            FROM usuarios
            WHERE estado = 'activo'
        ")
        ->fetchColumn();

} catch (PDOException $e) {

    error_log($e->getMessage());

}

// =============================================
// INFORMACIÓN DEL USUARIO
// =============================================

$nombre = htmlspecialchars(
    $usuario['nombre'],
    ENT_QUOTES,
    'UTF-8'
);

$rol = htmlspecialchars(
    ucfirst($rolUsuario),
    ENT_QUOTES,
    'UTF-8'
);

// =============================================
// CONFIGURACIÓN DE LA PÁGINA
// =============================================

$tituloPagina = 'Panel principal';
$paginaActiva = 'inicio';

?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- MENÚ LATERAL -->

<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>


<!-- CONTENIDO PRINCIPAL -->

<main class="app-main">

    <!-- ENCABEZADO -->

    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>


    <!-- BIENVENIDA -->

    <section class="dashboard-welcome">

        <div>
            <span class="welcome-tag">
                PANEL DE CONTROL
            </span>

            <h2>
                Resumen general
            </h2>

            <p>
                Aquí puedes supervisar la actividad y los recursos
                principales del Sistema de Gestión de Citas Médicas.
            </p>
        </div>

        <div class="welcome-decoration">
            <span>✚</span>
        </div>

    </section>


    <!-- ESTADÍSTICAS -->

    <section class="dashboard-section">

        <div class="section-heading">

            <div>
                <h2>Indicadores principales</h2>
                <p>Información actualizada de la plataforma.</p>
            </div>

            <span class="live-indicator">
                <span></span>
                Datos en tiempo real
            </span>

        </div>


        <div class="stats-grid">


            <!-- PACIENTES -->

            <article class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon patients-icon">
                        ♙
                    </div>

                    <span class="stat-category">
                        PACIENTES
                    </span>

                </div>

                <div class="stat-number">
                    <?= $estadisticas['pacientes']; ?>
                </div>

                <div class="stat-description">
                    Pacientes activos registrados
                </div>

                <?php if ($rolUsuario === 'administrador'): ?>

                    <a href="../modules/pacientes/index.php"
                       class="stat-link">

                        Gestionar pacientes
                        <span>→</span>

                    </a>

                <?php endif; ?>

            </article>


            <!-- MÉDICOS -->

            <article class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon doctors-icon">
                        ⚕
                    </div>

                    <span class="stat-category">
                        PROFESIONALES
                    </span>

                </div>

                <div class="stat-number">
                    <?= $estadisticas['medicos']; ?>
                </div>

                <div class="stat-description">
                    Médicos activos registrados
                </div>

                <span class="stat-pending">
                    Gestión próximamente
                </span>

            </article>


            <!-- USUARIOS -->

            <article class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon users-icon">
                        ♧
                    </div>

                    <span class="stat-category">
                        USUARIOS
                    </span>

                </div>

                <div class="stat-number">
                    <?= $estadisticas['usuarios']; ?>
                </div>

                <div class="stat-description">
                    Cuentas activas del sistema
                </div>

                <span class="stat-pending">
                    Administración de cuentas
                </span>

            </article>


            <!-- CITAS -->

            <article class="stat-card">

                <div class="stat-top">

                    <div class="stat-icon appointments-icon">
                        ▦
                    </div>

                    <span class="stat-category">
                        AGENDA
                    </span>

                </div>

                <div class="stat-number">
                    —
                </div>

                <div class="stat-description">
                    Citas médicas pendientes
                </div>

                <span class="stat-pending">
                    Módulo en desarrollo
                </span>

            </article>


        </div>

    </section>


    <!-- CONTENIDO SECUNDARIO -->

    <section class="dashboard-bottom">


        <!-- PRÓXIMAS CITAS -->

        <article class="content-card appointments-card">

            <div class="card-heading">

                <div>
                    <span class="card-eyebrow">
                        ORGANIZACIÓN
                    </span>

                    <h2>Próximas citas</h2>

                    <p>
                        Consulta la actividad de la agenda médica.
                    </p>
                </div>

                <div class="card-heading-icon">
                    ▦
                </div>

            </div>


            <div class="empty-state">

                <div class="empty-state-icon">
                    ▦
                </div>

                <h3>Tu agenda aparecerá aquí</h3>

                <p>
                    Cuando implementemos el módulo de citas,
                    podrás visualizar las consultas programadas,
                    sus horarios y los pacientes correspondientes.
                </p>

            </div>

        </article>


        <!-- ACTIVIDAD DEL SISTEMA -->

        <article class="content-card activity-card">

            <div class="card-heading">

                <div>
                    <span class="card-eyebrow">
                        SISTEMA
                    </span>

                    <h2>Actividad reciente</h2>

                    <p>
                        Información de tu sesión actual.
                    </p>
                </div>

                <div class="card-heading-icon">
                    ◷
                </div>

            </div>


            <div class="activity-list">

                <div class="activity-item">

                    <span class="activity-dot"></span>

                    <div>
                        <strong>Sesión iniciada</strong>

                        <p>
                            Has accedido correctamente a SGCM.
                        </p>
                    </div>

                    <span class="activity-time">
                        Ahora
                    </span>

                </div>


                <div class="activity-item">

                    <span class="activity-dot"></span>

                    <div>
                        <strong>Cuenta administrativa</strong>

                        <p>
                            Rol actual: <?= $rol; ?>.
                        </p>
                    </div>

                </div>

            </div>

        </article>


    </section>


    <!-- PIE DEL DASHBOARD -->

    <footer class="dashboard-footer">

        <span>
            © <?= date('Y'); ?> SGCM.
            Sistema de Gestión de Citas Médicas.
        </span>

        <span>
            Versión 1.0.0
        </span>

    </footer>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>