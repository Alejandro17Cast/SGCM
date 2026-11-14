<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

// Verificar permisos
if ($_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../../dashboard/index.php');
    exit;
}

// Conexión a la base de datos
require_once __DIR__ . '/../../config/database.php';

// Configuración del layout
$tituloPagina = 'Gestión de pacientes | SGCM';
$paginaActiva = 'pacientes';

// Función para escapar datos
function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Parámetros de búsqueda
$busqueda = trim($_GET['q'] ?? '');
$estado = $_GET['estado'] ?? 'todos';

// Validar estado
if (!in_array($estado, ['todos', 'activo', 'inactivo'], true)) {
    $estado = 'todos';
}

// Configuración de paginación
$porPagina = 10;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));

// Construir condiciones SQL
$condiciones = [];
$parametros = [];

if ($busqueda !== '') {
    $condiciones[] = "(
        nombres LIKE :busqueda1
        OR apellidos LIKE :busqueda2
        OR identificacion LIKE :busqueda3
        OR telefono LIKE :busqueda4
    )";

    $termino = "%{$busqueda}%";

    $parametros['busqueda1'] = $termino;
    $parametros['busqueda2'] = $termino;
    $parametros['busqueda3'] = $termino;
    $parametros['busqueda4'] = $termino;
}

if ($estado !== 'todos') {
    $condiciones[] = "estado = :estado";
    $parametros['estado'] = $estado;
}

$where = $condiciones
    ? 'WHERE ' . implode(' AND ', $condiciones)
    : '';

// Contar pacientes según filtros
$sqlTotal = "SELECT COUNT(*) FROM pacientes $where";

$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($parametros);

$totalPacientes = (int) $stmtTotal->fetchColumn();

// Calcular paginación
$totalPaginas = max(1, (int) ceil($totalPacientes / $porPagina));

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}

$inicio = ($pagina - 1) * $porPagina;

// Obtener pacientes
$sql = "
    SELECT *
    FROM pacientes
    $where
    ORDER BY creado_en DESC
    LIMIT :limite OFFSET :inicio
";

$stmt = $pdo->prepare($sql);

// Vincular parámetros de búsqueda
foreach ($parametros as $nombre => $valor) {
    $stmt->bindValue(':' . $nombre, $valor, PDO::PARAM_STR);
}

$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);

$stmt->execute();

$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas generales
$totalActivos = (int) $pdo->query("
    SELECT COUNT(*)
    FROM pacientes
    WHERE estado = 'activo'
")->fetchColumn();

$totalInactivos = (int) $pdo->query("
    SELECT COUNT(*)
    FROM pacientes
    WHERE estado = 'inactivo'
")->fetchColumn();

// Preparar parámetros de paginación
$parametrosUrl = [
    'q' => $busqueda,
    'estado' => $estado
];

// Cargar encabezado y menú lateral
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<main class="app-main">

    <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="patients-page">

        <!-- Encabezado -->
        <section class="patients-header">

            <div>
                <p class="eyebrow">GESTIÓN DEL SISTEMA</p>

                <h1>Gestión de pacientes</h1>

                <p class="subtitle">
                    Administra y consulta la información de los pacientes registrados.
                </p>
            </div>

            <a href="crear.php" class="btn-primary">
                + Registrar paciente
            </a>

        </section>

        <!-- Estadísticas -->
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

        <!-- Contenido principal -->
        <section class="patients-content">

            <div class="section-heading">
                <div>
                    <h2>Listado de pacientes</h2>

                    <p>
                        Consulta, busca y administra los registros.
                    </p>
                </div>
            </div>

            <!-- Buscador y filtros -->
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

                    <option value="todos"
                        <?= $estado === 'todos' ? 'selected' : '' ?>>
                        Todos los estados
                    </option>

                    <option value="activo"
                        <?= $estado === 'activo' ? 'selected' : '' ?>>
                        Activos
                    </option>

                    <option value="inactivo"
                        <?= $estado === 'inactivo' ? 'selected' : '' ?>>
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

            <!-- Tabla -->
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

                    <?php if (!empty($pacientes)): ?>

                        <?php foreach ($pacientes as $paciente): ?>

                            <tr>

                                <td>
                                    <div class="patient-name">
                                        <?= e(
                                            $paciente['nombres'] . ' ' .
                                            $paciente['apellidos']
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= e($paciente['identificacion']) ?>
                                </td>

                                <td>
                                    <?= e($paciente['telefono'] ?: 'No registrado') ?>
                                </td>

                                <td>
                                    <?= e($paciente['correo'] ?: 'No registrado') ?>
                                </td>

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

            <!-- Pie de tabla y paginación -->
            <div class="table-footer">

                <span>
                    Mostrando <?= count($pacientes) ?>
                    de <?= $totalPacientes ?> pacientes
                </span>

                <div class="pagination">

                    <?php if ($pagina > 1): ?>

                        <a href="?<?= e(http_build_query(
                            array_merge($parametrosUrl, [
                                'pagina' => $pagina - 1
                            ])
                        )) ?>">
                            Anterior
                        </a>

                    <?php endif; ?>

                    <span>
                        Página <?= $pagina ?> de <?= $totalPaginas ?>
                    </span>

                    <?php if ($pagina < $totalPaginas): ?>

                        <a href="?<?= e(http_build_query(
                            array_merge($parametrosUrl, [
                                'pagina' => $pagina + 1
                            ])
                        )) ?>">
                            Siguiente
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </div>

</main>

<?php
// Cargar pie de página
require_once __DIR__ . '/../../includes/footer.php';
?>