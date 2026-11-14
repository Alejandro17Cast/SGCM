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

// =============================================
// FILTROS
// =============================================

$busqueda = trim($_GET['q'] ?? '');
$estado = $_GET['estado'] ?? 'todos';
$fecha = $_GET['fecha'] ?? '';

$estadosPermitidos = [
    'pendiente',
    'confirmada',
    'completada',
    'cancelada'
];

if (!in_array($estado, $estadosPermitidos, true) && $estado !== 'todos') {
    $estado = 'todos';
}

if ($fecha !== '') {
    $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);

    if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
        $fecha = '';
    }
}

// =============================================
// PAGINACIÓN
// =============================================

$porPagina = 10;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$inicio = ($pagina - 1) * $porPagina;

// =============================================
// CONDICIONES SQL
// =============================================

$condiciones = [];
$parametros = [];

if ($busqueda !== '') {
    $condiciones[] = "(
        p.nombres LIKE :busqueda1
        OR p.apellidos LIKE :busqueda2
        OR p.identificacion LIKE :busqueda3
        OR u.nombre LIKE :busqueda4
        OR u.apellido LIKE :busqueda5
        OR c.motivo LIKE :busqueda6
    )";

    $termino = "%{$busqueda}%";

    $parametros['busqueda1'] = $termino;
    $parametros['busqueda2'] = $termino;
    $parametros['busqueda3'] = $termino;
    $parametros['busqueda4'] = $termino;
    $parametros['busqueda5'] = $termino;
    $parametros['busqueda6'] = $termino;
}

if ($estado !== 'todos') {
    $condiciones[] = "c.estado = :estado";
    $parametros['estado'] = $estado;
}

if ($fecha !== '') {
    $condiciones[] = "c.fecha = :fecha";
    $parametros['fecha'] = $fecha;
}

$where = $condiciones
    ? 'WHERE ' . implode(' AND ', $condiciones)
    : '';

// =============================================
// ESTADÍSTICAS
// =============================================

$estadisticas = [
    'total' => 0,
    'pendientes' => 0,
    'confirmadas' => 0,
    'completadas' => 0
];

try {
    $estadisticas['total'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM citas"
    )->fetchColumn();

    $estadisticas['pendientes'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM citas WHERE estado = 'pendiente'"
    )->fetchColumn();

    $estadisticas['confirmadas'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM citas WHERE estado = 'confirmada'"
    )->fetchColumn();

    $estadisticas['completadas'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM citas WHERE estado = 'completada'"
    )->fetchColumn();

    // =============================================
    // TOTAL DE RESULTADOS
    // =============================================

    $sqlTotal = "
        SELECT COUNT(*)
        FROM citas c
        INNER JOIN pacientes p ON c.paciente_id = p.id
        INNER JOIN usuarios u ON c.medico_id = u.id
        $where
    ";

    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute($parametros);

    $totalResultados = (int) $stmtTotal->fetchColumn();

    $totalPaginas = max(
        1,
        (int) ceil($totalResultados / $porPagina)
    );

    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
        $inicio = ($pagina - 1) * $porPagina;
    }

    // =============================================
    // OBTENER CITAS
    // =============================================

    $sql = "
        SELECT
            c.id,
            c.fecha,
            c.hora,
            c.motivo,
            c.estado,
            p.nombres AS paciente_nombres,
            p.apellidos AS paciente_apellidos,
            p.identificacion,
            u.nombre AS medico_nombre,
            u.apellido AS medico_apellido
        FROM citas c
        INNER JOIN pacientes p ON c.paciente_id = p.id
        INNER JOIN usuarios u ON c.medico_id = u.id
        $where
        ORDER BY c.fecha DESC, c.hora DESC
        LIMIT $porPagina OFFSET $inicio
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    $citas = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());

    $citas = [];
    $totalResultados = 0;
    $totalPaginas = 1;

    $errorCarga = 'No fue posible cargar las citas médicas.';
}

// =============================================
// FUNCIONES
// =============================================

function e($valor) {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$tituloPagina = 'Gestión de citas';
$paginaActiva = 'citas';

?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>

<main class="app-main">

    <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="appointments-page">

        <!-- ENCABEZADO -->

        <section class="appointments-heading">

            <div>
                <span class="appointments-eyebrow">
                    GESTIÓN MÉDICA
                </span>

                <h2>Agenda de citas</h2>

                <p>
                    Administra y supervisa las consultas médicas
                    programadas en el sistema.
                </p>
            </div>

            <a href="crear.php" class="appointment-primary-button">
                <span>＋</span>
                Nueva cita
            </a>

        </section>

        <!-- ESTADÍSTICAS -->

        <section class="appointments-stats">

            <article class="appointment-stat-card">
                <div class="appointment-stat-icon">▦</div>
                <span>Total de citas</span>
                <strong><?= $estadisticas['total'] ?></strong>
                <small>Registros en el sistema</small>
            </article>

            <article class="appointment-stat-card">
                <div class="appointment-stat-icon pending-icon">◷</div>
                <span>Pendientes</span>
                <strong><?= $estadisticas['pendientes'] ?></strong>
                <small>Esperando confirmación</small>
            </article>

            <article class="appointment-stat-card">
                <div class="appointment-stat-icon confirmed-icon">✓</div>
                <span>Confirmadas</span>
                <strong><?= $estadisticas['confirmadas'] ?></strong>
                <small>Consultas programadas</small>
            </article>

            <article class="appointment-stat-card">
                <div class="appointment-stat-icon completed-icon">✚</div>
                <span>Completadas</span>
                <strong><?= $estadisticas['completadas'] ?></strong>
                <small>Consultas realizadas</small>
            </article>

        </section>

        <!-- TABLA DE CITAS -->

        <section class="appointments-panel">

            <div class="appointments-panel-heading">

                <div>
                    <h3>Registro de citas</h3>
                    <p>Consulta y administra las citas médicas.</p>
                </div>

                <span class="appointments-live">
                    <span></span>
                    Sistema activo
                </span>

            </div>

            <?php if (isset($errorCarga)): ?>

                <div class="appointments-error">
                    <?= e($errorCarga) ?>
                </div>

            <?php endif; ?>

            <!-- FILTROS -->

            <form method="GET" class="appointments-filters">

                <input
                    type="search"
                    name="q"
                    placeholder="Buscar paciente, médico o motivo..."
                    value="<?= e($busqueda) ?>"
                >

                <select name="estado">
                    <option value="todos">Todos los estados</option>

                    <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>
                        Pendientes
                    </option>

                    <option value="confirmada" <?= $estado === 'confirmada' ? 'selected' : '' ?>>
                        Confirmadas
                    </option>

                    <option value="completada" <?= $estado === 'completada' ? 'selected' : '' ?>>
                        Completadas
                    </option>

                    <option value="cancelada" <?= $estado === 'cancelada' ? 'selected' : '' ?>>
                        Canceladas
                    </option>
                </select>

                <input
                    type="date"
                    name="fecha"
                    value="<?= e($fecha) ?>"
                >

                <button type="submit" class="appointment-filter-button">
                    Buscar
                </button>

                <a href="index.php" class="appointment-clear-button">
                    Limpiar
                </a>

            </form>

            <!-- TABLA -->

            <div class="appointments-table-wrapper">

                <table class="appointments-table">

                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Fecha y hora</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($citas)): ?>

                        <?php foreach ($citas as $cita): ?>

                            <tr>

                                <td>
                                    <div class="appointment-person">
                                        <strong>
                                            <?= e(
                                                $cita['paciente_nombres'] . ' ' .
                                                $cita['paciente_apellidos']
                                            ) ?>
                                        </strong>

                                        <small>
                                            <?= e($cita['identificacion']) ?>
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    Dr. <?= e(
                                        $cita['medico_nombre'] . ' ' .
                                        $cita['medico_apellido']
                                    ) ?>
                                </td>

                                <td>
                                    <div class="appointment-date">
                                        <strong>
                                            <?= date(
                                                'd/m/Y',
                                                strtotime($cita['fecha'])
                                            ) ?>
                                        </strong>

                                        <small>
                                            <?= date(
                                                'h:i A',
                                                strtotime($cita['hora'])
                                            ) ?>
                                        </small>
                                    </div>
                                </td>

                                <td><?= e($cita['motivo']) ?></td>

                                <td>
                                    <span class="appointment-status <?= e($cita['estado']) ?>">
                                        <?= ucfirst(e($cita['estado'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="ver.php?id=<?= (int) $cita['id'] ?>"
                                        class="appointment-action"
                                    >
                                        Ver detalle
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="appointments-empty">
                                <span>▦</span>
                                <strong>No hay citas para mostrar</strong>
                                <small>
                                    Prueba modificando los filtros o registra una nueva cita.
                                </small>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- PIE Y PAGINACIÓN -->

            <div class="appointments-table-footer">

                <span>
                    Mostrando <?= count($citas) ?>
                    de <?= $totalResultados ?> citas
                </span>

                <div class="appointments-pagination">

                    <?php if ($pagina > 1): ?>
                        <a href="?q=<?= urlencode($busqueda) ?>&estado=<?= urlencode($estado) ?>&fecha=<?= urlencode($fecha) ?>&pagina=<?= $pagina - 1 ?>">
                            Anterior
                        </a>
                    <?php endif; ?>

                    <span>
                        Página <?= $pagina ?> de <?= $totalPaginas ?>
                    </span>

                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?q=<?= urlencode($busqueda) ?>&estado=<?= urlencode($estado) ?>&fecha=<?= urlencode($fecha) ?>&pagina=<?= $pagina + 1 ?>">
                            Siguiente
                        </a>
                    <?php endif; ?>

                </div>

            </div>

        </section>

    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>