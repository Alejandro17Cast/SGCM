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
// VALIDAR ID
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
// DATOS ORIGINALES
// =============================================

$datos = [
    'nombres' => $paciente['nombres'],
    'apellidos' => $paciente['apellidos'],
    'identificacion' => $paciente['identificacion'],
    'fecha_nacimiento' => $paciente['fecha_nacimiento'],
    'sexo' => $paciente['sexo'],
    'telefono' => $paciente['telefono'],
    'correo' => $paciente['correo'] ?? '',
    'direccion' => $paciente['direccion'],
    'contacto_emergencia' => $paciente['contacto_emergencia'] ?? '',
    'telefono_emergencia' => $paciente['telefono_emergencia'] ?? '',
    'estado' => $paciente['estado']
];

$errores = [];

// =============================================
// PROCESAR FORMULARIO
// =============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim($_POST[$campo] ?? '');
    }

    // =========================================
    // VALIDAR CAMPOS OBLIGATORIOS
    // =========================================

    $obligatorios = [
        'nombres',
        'apellidos',
        'identificacion',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'direccion'
    ];

    foreach ($obligatorios as $campo) {

        if ($datos[$campo] === '') {
            $errores[] = "El campo $campo es obligatorio.";
        }

    }

    // =========================================
    // VALIDAR CORREO
    // =========================================

    if (
        $datos['correo'] !== '' &&
        !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)
    ) {

        $errores[] = 'El correo electrónico no tiene un formato válido.';

    }

    // =========================================
    // VALIDAR SEXO
    // =========================================

    $sexosPermitidos = [
        'masculino',
        'femenino',
        'otro',
        'prefiero_no_decir'
    ];

    if (!in_array($datos['sexo'], $sexosPermitidos, true)) {

        $errores[] = 'Selecciona una opción de sexo válida.';

    }

    // =========================================
    // VALIDAR ESTADO
    // =========================================

    if (!in_array($datos['estado'], ['activo', 'inactivo'], true)) {

        $errores[] = 'El estado seleccionado no es válido.';

    }

    // =========================================
    // VALIDAR FECHA
    // =========================================

    $fecha = DateTime::createFromFormat(
        'Y-m-d',
        $datos['fecha_nacimiento']
    );

    if (
        !$fecha ||
        $fecha->format('Y-m-d') !== $datos['fecha_nacimiento'] ||
        $fecha > new DateTime('today')
    ) {

        $errores[] = 'La fecha de nacimiento no es válida.';

    }

    // =========================================
    // ACTUALIZAR PACIENTE
    // =========================================

    if (empty($errores)) {

        try {

            $sql = "UPDATE pacientes SET

                nombres = :nombres,
                apellidos = :apellidos,
                identificacion = :identificacion,
                fecha_nacimiento = :fecha_nacimiento,
                sexo = :sexo,
                telefono = :telefono,
                correo = :correo,
                direccion = :direccion,
                contacto_emergencia = :contacto_emergencia,
                telefono_emergencia = :telefono_emergencia,
                estado = :estado

                WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([

                'nombres' => $datos['nombres'],

                'apellidos' => $datos['apellidos'],

                'identificacion' => $datos['identificacion'],

                'fecha_nacimiento' => $datos['fecha_nacimiento'],

                'sexo' => $datos['sexo'],

                'telefono' => $datos['telefono'],

                'correo' => $datos['correo'] ?: null,

                'direccion' => $datos['direccion'],

                'contacto_emergencia' => $datos['contacto_emergencia'] ?: null,

                'telefono_emergencia' => $datos['telefono_emergencia'] ?: null,

                'estado' => $datos['estado'],

                'id' => $id

            ]);

            header('Location: ver.php?id=' . $id . '&actualizado=1');
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                $errores[] = 'La identificación o el correo ya pertenecen a otro paciente.';

            } else {

                error_log($e->getMessage());

                $errores[] = 'Ocurrió un error al actualizar el paciente.';

            }

        }

    }

}

// =============================================
// FUNCIÓN PARA ESCAPAR HTML
// =============================================

function e($valor) {

    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );

}

// =============================================
// CONFIGURACIÓN DE LA PÁGINA
// =============================================

$tituloPagina = 'Editar paciente';
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

                <a
                    href="ver.php?id=<?= (int) $id ?>"
                    class="back-link"
                >
                    ← Volver al expediente
                </a>

                <span class="eyebrow">
                    GESTIÓN DE PACIENTES
                </span>

                <h1>Editar paciente</h1>

                <p class="subtitle">
                    Actualiza los datos personales, de contacto y
                    de emergencia del paciente.
                </p>

            </div>

        </header>

        <!-- =====================================
             ERRORES
        ====================================== -->

        <?php if (!empty($errores)): ?>

            <div class="form-errors" role="alert">

                <strong>
                    No se pudo actualizar el registro:
                </strong>

                <ul>

                    <?php foreach ($errores as $error): ?>

                        <li>
                            <?= e($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <!-- =====================================
             FORMULARIO
        ====================================== -->

        <form method="POST" class="patient-form">

            <!-- INFORMACIÓN PERSONAL -->

            <section class="form-section">

                <h2>01. Información personal</h2>

                <p>
                    Datos generales de identificación del paciente.
                </p>

                <div class="form-grid">

                    <div class="form-group">

                        <label for="nombres">
                            Nombres *
                        </label>

                        <input
                            type="text"
                            id="nombres"
                            name="nombres"
                            value="<?= e($datos['nombres']) ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="apellidos">
                            Apellidos *
                        </label>

                        <input
                            type="text"
                            id="apellidos"
                            name="apellidos"
                            value="<?= e($datos['apellidos']) ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="identificacion">
                            Identificación *
                        </label>

                        <input
                            type="text"
                            id="identificacion"
                            name="identificacion"
                            value="<?= e($datos['identificacion']) ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="fecha_nacimiento">
                            Fecha de nacimiento *
                        </label>

                        <input
                            type="date"
                            id="fecha_nacimiento"
                            name="fecha_nacimiento"
                            value="<?= e($datos['fecha_nacimiento']) ?>"
                            max="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="sexo">
                            Sexo *
                        </label>

                        <select
                            id="sexo"
                            name="sexo"
                            required
                        >

                            <option value="">
                                Seleccionar
                            </option>

                            <?php

                            $sexos = [
                                'masculino' => 'Masculino',
                                'femenino' => 'Femenino',
                                'otro' => 'Otro',
                                'prefiero_no_decir' => 'Prefiero no decir'
                            ];

                            ?>

                            <?php foreach ($sexos as $valor => $etiqueta): ?>

                                <option
                                    value="<?= e($valor) ?>"
                                    <?= $datos['sexo'] === $valor ? 'selected' : '' ?>
                                >

                                    <?= e($etiqueta) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="form-group">

                        <label for="estado">
                            Estado del paciente *
                        </label>

                        <select
                            id="estado"
                            name="estado"
                            required
                        >

                            <option
                                value="activo"
                                <?= $datos['estado'] === 'activo' ? 'selected' : '' ?>
                            >
                                Activo
                            </option>

                            <option
                                value="inactivo"
                                <?= $datos['estado'] === 'inactivo' ? 'selected' : '' ?>
                            >
                                Inactivo
                            </option>

                        </select>

                    </div>

                </div>

            </section>

            <!-- INFORMACIÓN DE CONTACTO -->

            <section class="form-section">

                <h2>02. Información de contacto</h2>

                <p>
                    Medios de contacto y ubicación del paciente.
                </p>

                <div class="form-grid">

                    <div class="form-group">

                        <label for="telefono">
                            Teléfono *
                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                            value="<?= e($datos['telefono']) ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="correo">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            value="<?= e($datos['correo']) ?>"
                        >

                    </div>

                    <div class="form-group full-width">

                        <label for="direccion">
                            Dirección *
                        </label>

                        <textarea
                            id="direccion"
                            name="direccion"
                            rows="3"
                            required
                        ><?= e($datos['direccion']) ?></textarea>

                    </div>

                </div>

            </section>

            <!-- CONTACTO DE EMERGENCIA -->

            <section class="form-section">

                <h2>03. Contacto de emergencia</h2>

                <p>
                    Persona a contactar en caso de emergencia.
                </p>

                <div class="form-grid">

                    <div class="form-group">

                        <label for="contacto_emergencia">
                            Nombre del contacto
                        </label>

                        <input
                            type="text"
                            id="contacto_emergencia"
                            name="contacto_emergencia"
                            value="<?= e($datos['contacto_emergencia']) ?>"
                        >

                    </div>

                    <div class="form-group">

                        <label for="telefono_emergencia">
                            Teléfono de emergencia
                        </label>

                        <input
                            type="tel"
                            id="telefono_emergencia"
                            name="telefono_emergencia"
                            value="<?= e($datos['telefono_emergencia']) ?>"
                        >

                    </div>

                </div>

            </section>

            <!-- BOTONES -->

            <div class="form-actions">

                <a
                    href="ver.php?id=<?= (int) $id ?>"
                    class="btn-cancel"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Guardar cambios
                </button>

            </div>

        </form>

    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>