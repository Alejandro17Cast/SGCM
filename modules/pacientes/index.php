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

// Parámetros de búsqueda
$busqueda = trim($_GET['q'] ?? '');
$estado = $_GET['estado'] ?? 'todos';

// Paginación
$porPagina = 10;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$inicio = ($pagina - 1) * $porPagina;

// Construir condiciones
$condiciones = [];
$parametros = [];

if ($busqueda !== '') {
    $condiciones[] = "(
        nombres LIKE :busqueda1
        OR apellidos LIKE :busqueda2
        OR identificacion LIKE :busqueda3
        OR telefono LIKE :busqueda4
    )";

    $termino = "%$busqueda%";

    $parametros['busqueda1'] = $termino;
    $parametros['busqueda2'] = $termino;
    $parametros['busqueda3'] = $termino;
    $parametros['busqueda4'] = $termino;
}

if (in_array($estado, ['activo', 'inactivo'], true)) {
    $condiciones[] = "estado = :estado";
    $parametros['estado'] = $estado;
}

$where = $condiciones
    ? 'WHERE ' . implode(' AND ', $condiciones)
    : '';

// Total de pacientes
$sqlTotal = "SELECT COUNT(*) FROM pacientes $where";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($parametros);

$totalPacientes = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalPacientes / $porPagina));

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
    $inicio = ($pagina - 1) * $porPagina;
}

// Obtener pacientes
$sql = "SELECT *
        FROM pacientes
        $where
        ORDER BY creado_en DESC
        LIMIT $porPagina OFFSET $inicio";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$pacientes = $stmt->fetchAll();

// Estadísticas
$totalActivos = (int) $pdo->query(
    "SELECT COUNT(*) FROM pacientes WHERE estado = 'activo'"
)->fetchColumn();

$totalInactivos = (int) $pdo->query(
    "SELECT COUNT(*) FROM pacientes WHERE estado = 'inactivo'"
)->fetchColumn();

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pacientes | SGCM</title>

    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="assets/pacientes.css">
</head>

<body>

<div class="patients-page">

    <header class="patients-header">
        <div>
            <a href="../../dashboard/index.php" class="back-link">
                ← Volver al panel
            </a>

            <p class="eyebrow">GESTIÓN DEL SISTEMA</p>
            <h1>Pacientes</h1>
            <p class="subtitle">
                Administra y consulta la información de los pacientes registrados.
            </p>
        </div>

        <a href="crear.php" class="btn-primary">
            + Registrar paciente
        </a>
    </header>

    <section class="patients-stats">

        <article class="stat-card">
            <span>Total de pacientes</span>
            <strong><?= $totalPacientes ?></strong>
            <small>Resultados de esta búsqueda</small>
        </article>

        <article class="stat-card">
            <span>Pacientes activos</span>
            <strong><?= $totalActivos ?></strong>
            <small>Registros habilitados</small>
        </article>

        <article class="stat-card">
            <span>Pacientes inactivos</span>
            <strong><?= $totalInactivos ?></strong>
            <small>Registros deshabilitados</small>
        </article>

    </section>

    <section class="patients-content">

        <form method="GET" class="filters">

            <div class="search-box">
                <input
                    type="text"
                    name="q"
                    placeholder="Buscar por nombre, identificación o teléfono..."
                    value="<?= e($busqueda) ?>"
                >
            </div>

            <select name="estado">
                <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>
                    Todos los estados
                </option>

                <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>
                    Activos
                </option>

                <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>
                    Inactivos
                </option>
            </select>

            <button type="submit" class="btn-filter">
                Buscar
            </button>

            <a href="index.php" class="btn-clear">
                Limpiar
            </a>

        </form>

        <div class="table-container">

            <table class="patients-table">

                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Identificación</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($pacientes) > 0): ?>

                    <?php foreach ($pacientes as $paciente): ?>

                        <tr>
                            <td>
                                <div class="patient-name">
                                    <?= e($paciente['nombres'] . ' ' . $paciente['apellidos']) ?>
                                </div>
                            </td>

                            <td><?= e($paciente['identificacion']) ?></td>

                            <td><?= e($paciente['telefono']) ?></td>

                            <td><?= e($paciente['correo'] ?: 'No registrado') ?></td>

                            <td>
                                <span class="status <?= e($paciente['estado']) ?>">
                                    <?= ucfirst(e($paciente['estado'])) ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="ver.php?id=<?= (int) $paciente['id'] ?>">
                                        Ver
                                    </a>

                                    <a href="editar.php?id=<?= (int) $paciente['id'] ?>">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="empty-state">
                            No se encontraron pacientes registrados.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="table-footer">
            <span>
                Mostrando <?= count($pacientes) ?> de <?= $totalPacientes ?> pacientes
            </span>

            <div class="pagination">

                <?php if ($pagina > 1): ?>
                    <a href="?q=<?= urlencode($busqueda) ?>&estado=<?= urlencode($estado) ?>&pagina=<?= $pagina - 1 ?>">
                        Anterior
                    </a>
                <?php endif; ?>

                <span>
                    Página <?= $pagina ?> de <?= $totalPaginas ?>
                </span>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?q=<?= urlencode($busqueda) ?>&estado=<?= urlencode($estado) ?>&pagina=<?= $pagina + 1 ?>">
                        Siguiente
                    </a>
                <?php endif; ?>

            </div>
        </div>

    </section>

</div>

</body>
</html>