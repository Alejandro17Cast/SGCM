<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

if ($_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../../dashboard/index.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM pacientes WHERE id = :id LIMIT 1"
);

$stmt->execute(['id' => $id]);
$paciente = $stmt->fetch();

if (!$paciente) {
    header('Location: index.php?error=no_encontrado');
    exit;
}

function e($valor) {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function mostrar($valor) {
    return $valor !== null && $valor !== ''
        ? e($valor)
        : 'No registrado';
}

$fechaNacimiento = new DateTime($paciente['fecha_nacimiento']);
$hoy = new DateTime();
$edad = $hoy->diff($fechaNacimiento)->y;

$fechaRegistro = date(
    'd/m/Y',
    strtotime($paciente['creado_en'])
);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle del paciente | SGCM</title>

    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="assets/pacientes.css">
</head>

<body>

<div class="patients-page">

    <header class="patients-header">
        <div>
            <a href="index.php" class="back-link">
                ← Volver a pacientes
            </a>

            <p class="eyebrow">EXPEDIENTE DEL PACIENTE</p>

            <h1>Información del paciente</h1>

            <p class="subtitle">
                Consulta los datos personales y de contacto registrados.
            </p>
        </div>

        <div class="profile-actions">

    <a href="editar.php?id=<?= (int) $paciente['id'] ?>"
       class="btn-primary">
        Editar información
    </a>

    <?php if ($paciente['estado'] === 'activo'): ?>

        <form action="eliminar.php" method="POST"
              onsubmit="return confirm('¿Estás seguro de desactivar este paciente?');">

            <input type="hidden" name="id"
                   value="<?= (int) $paciente['id'] ?>">

            <button type="submit" class="btn-danger">
                Desactivar paciente
            </button>

        </form>

    <?php endif; ?>

</div>
    </header>

    <section class="patient-profile">

        <div class="profile-heading">

            <div class="profile-avatar">
                <?= strtoupper(substr($paciente['nombres'], 0, 1)) ?>
            </div>

            <div>
                <h2>
                    <?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?>
                </h2>

                <span class="status <?= e($paciente['estado']) ?>">
                    <?= ucfirst(e($paciente['estado'])) ?>
                </span>
            </div>

        </div>

        <div class="profile-section">

            <h3>01. Información personal</h3>

            <div class="profile-grid">

                <div class="profile-field">
                    <span>Identificación</span>
                    <strong><?= mostrar($paciente['identificacion']) ?></strong>
                </div>

                <div class="profile-field">
                    <span>Fecha de nacimiento</span>
                    <strong><?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?></strong>
                </div>

                <div class="profile-field">
                    <span>Edad</span>
                    <strong><?= $edad ?> años</strong>
                </div>

                <div class="profile-field">
                    <span>Sexo</span>
                    <strong><?= ucfirst(str_replace('_', ' ', e($paciente['sexo']))) ?></strong>
                </div>

            </div>

        </div>

        <div class="profile-section">

            <h3>02. Información de contacto</h3>

            <div class="profile-grid">

                <div class="profile-field">
                    <span>Teléfono</span>
                    <strong><?= mostrar($paciente['telefono']) ?></strong>
                </div>

                <div class="profile-field">
                    <span>Correo electrónico</span>
                    <strong><?= mostrar($paciente['correo']) ?></strong>
                </div>

                <div class="profile-field full-width">
                    <span>Dirección</span>
                    <strong><?= mostrar($paciente['direccion']) ?></strong>
                </div>

            </div>

        </div>

        <div class="profile-section">

            <h3>03. Contacto de emergencia</h3>

            <div class="profile-grid">

                <div class="profile-field">
                    <span>Nombre del contacto</span>
                    <strong><?= mostrar($paciente['contacto_emergencia']) ?></strong>
                </div>

                <div class="profile-field">
                    <span>Teléfono de emergencia</span>
                    <strong><?= mostrar($paciente['telefono_emergencia']) ?></strong>
                </div>

            </div>

        </div>

        <div class="profile-footer">
            <span>Paciente registrado el <?= $fechaRegistro ?></span>
            <span>Código de paciente: #<?= (int) $paciente['id'] ?></span>
        </div>

    </section>

</div>

</body>
</html>