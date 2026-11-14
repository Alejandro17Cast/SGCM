<?php
session_start();

// =============================================
// PROTECCIÓN DE ACCESO
// =============================================

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

if ($_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../../dashboard/index.php');
    exit;
}

// =============================================
// CONEXIÓN A LA BASE DE DATOS
// =============================================

require_once __DIR__ . '/../../config/database.php';

// =============================================
// VALIDAR ID DEL PACIENTE
// =============================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}

// =============================================
// CONSULTAR PACIENTE
// =============================================

$stmt = $pdo->prepare(
    "SELECT * FROM pacientes WHERE id = :id LIMIT 1"
);

$stmt->execute(['id' => $id]);

$paciente = $stmt->fetch();

if (!$paciente) {
    header('Location: index.php?error=no_encontrado');
    exit;
}

// =============================================
// FUNCIONES AUXILIARES
// =============================================

function e($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function mostrar($valor) {
    return $valor !== null && $valor !== ''
        ? e($valor)
        : 'No registrado';
}

// =============================================
// CALCULAR EDAD
// =============================================

$fechaNacimiento = new DateTime($paciente['fecha_nacimiento']);
$hoy = new DateTime();

$edad = $hoy->diff($fechaNacimiento)->y;

// =============================================
// FECHA DE REGISTRO
// =============================================

$fechaRegistro = date(
    'd/m/Y',
    strtotime($paciente['creado_en'])
);

// =============================================
// CONFIGURACIÓN DE LA PÁGINA
// =============================================

$tituloPagina = 'Expediente del paciente';
$paginaActiva = 'pacientes';

?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<main class="app-main">

    <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="patients-page">

        <!-- =====================================
             ENCABEZADO
        ====================================== -->

        <header class="patients-header">

            <div>

                <a href="index.php" class="back-link">
                    ← Volver a pacientes
                </a>

                <span class="eyebrow">
                    EXPEDIENTE DEL PACIENTE
                </span>

                <h1>Información del paciente</h1>

                <p class="subtitle">
                    Consulta y administra la información registrada.
                </p>

            </div>

            <div class="profile-actions">

                <a
                    href="editar.php?id=<?= (int) $paciente['id'] ?>"
                    class="btn-primary"
                >
                    Editar información
                </a>

                <?php if ($paciente['estado'] === 'activo'): ?>

                    <form
                        action="eliminar.php"
                        method="POST"
                        onsubmit="return confirm('¿Estás seguro de desactivar este paciente?');"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $paciente['id'] ?>"
                        >

                        <button type="submit" class="btn-danger">
                            Desactivar paciente
                        </button>

                    </form>

                <?php endif; ?>

            </div>

        </header>

        <!-- =====================================
             MENSAJE DE ACTUALIZACIÓN
        ====================================== -->

        <?php if (isset($_GET['actualizado']) && $_GET['actualizado'] === '1'): ?>

            <div class="patient-success" role="status">
                La información del paciente se actualizó correctamente.
            </div>

        <?php endif; ?>

        <!-- =====================================
             EXPEDIENTE DEL PACIENTE
        ====================================== -->

        <section class="patient-profile">

            <!-- IDENTIDAD DEL PACIENTE -->

            <div class="profile-heading">

                <div class="profile-avatar">

                    <?= e(
                        mb_strtoupper(
                            mb_substr($paciente['nombres'], 0, 1, 'UTF-8'),
                            'UTF-8'
                        )
                    ) ?>

                </div>

                <div>

                    <h2>
                        <?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?>
                    </h2>

                    <span class="status <?= e($paciente['estado']) ?>">
                        <?= e(ucfirst($paciente['estado'])) ?>
                    </span>

                </div>

            </div>

            <!-- INFORMACIÓN PERSONAL -->

            <div class="profile-section">

                <h3>01. Información personal</h3>

                <div class="profile-grid">

                    <div class="profile-field">

                        <span>Identificación</span>

                        <strong>
                            <?= mostrar($paciente['identificacion']) ?>
                        </strong>

                    </div>

                    <div class="profile-field">

                        <span>Fecha de nacimiento</span>

                        <strong>
                            <?= e($fechaNacimiento->format('d/m/Y')) ?>
                        </strong>

                    </div>

                    <div class="profile-field">

                        <span>Edad</span>

                        <strong>
                            <?= (int) $edad ?> años
                        </strong>

                    </div>

                    <div class="profile-field">

                        <span>Sexo</span>

                        <strong>
                            <?= e(
                                ucfirst(
                                    str_replace('_', ' ', $paciente['sexo'])
                                )
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>

            <!-- INFORMACIÓN DE CONTACTO -->

            <div class="profile-section">

                <h3>02. Información de contacto</h3>

                <div class="profile-grid">

                    <div class="profile-field">

                        <span>Teléfono</span>

                        <strong>
                            <?= mostrar($paciente['telefono']) ?>
                        </strong>

                    </div>

                    <div class="profile-field">

                        <span>Correo electrónico</span>

                        <strong>
                            <?= mostrar($paciente['correo']) ?>
                        </strong>

                    </div>

                    <div class="profile-field full-width">

                        <span>Dirección</span>

                        <strong>
                            <?= mostrar($paciente['direccion']) ?>
                        </strong>

                    </div>

                </div>

            </div>

            <!-- CONTACTO DE EMERGENCIA -->

            <div class="profile-section">

                <h3>03. Contacto de emergencia</h3>

                <div class="profile-grid">

                    <div class="profile-field">

                        <span>Nombre del contacto</span>

                        <strong>
                            <?= mostrar($paciente['contacto_emergencia']) ?>
                        </strong>

                    </div>

                    <div class="profile-field">

                        <span>Teléfono de emergencia</span>

                        <strong>
                            <?= mostrar($paciente['telefono_emergencia']) ?>
                        </strong>

                    </div>

                </div>

            </div>

            <!-- PIE DEL EXPEDIENTE -->

            <div class="profile-footer">

                <span>
                    Paciente registrado el <?= e($fechaRegistro) ?>
                </span>

                <span>
                    Código de paciente: #<?= (int) $paciente['id'] ?>
                </span>

            </div>

        </section>

    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>