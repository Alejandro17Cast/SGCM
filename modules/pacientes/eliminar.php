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

// Solo aceptar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: index.php?error=id_invalido');
    exit;
}

try {
    // Verificar que el paciente exista
    $stmt = $pdo->prepare(
        "SELECT id, estado FROM pacientes WHERE id = :id LIMIT 1"
    );

    $stmt->execute(['id' => $id]);
    $paciente = $stmt->fetch();

    if (!$paciente) {
        header('Location: index.php?error=no_encontrado');
        exit;
    }

    if ($paciente['estado'] === 'inactivo') {
        header('Location: index.php?mensaje=ya_inactivo');
        exit;
    }

    // Desactivación lógica
    $stmt = $pdo->prepare(
        "UPDATE pacientes SET estado = 'inactivo' WHERE id = :id"
    );

    $stmt->execute(['id' => $id]);

    header('Location: index.php?mensaje=desactivado');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header('Location: index.php?error=operacion');
    exit;
}