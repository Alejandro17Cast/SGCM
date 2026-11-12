<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Evitar acceso mediante GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Recibir credenciales
$correo = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$correo || $password === '') {
    header('Location: ../index.php?error=datos');
    exit;
}

try {

    // Buscar usuario y su rol
    $sql = "
        SELECT
            u.id,
            u.nombre,
            u.apellido,
            u.correo,
            u.password_hash,
            u.estado,
            r.nombre AS rol

        FROM usuarios u

        INNER JOIN roles r
            ON u.rol_id = r.id

        WHERE u.correo = :correo

        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'correo' => $correo
    ]);

    $usuario = $stmt->fetch();

    // Validar credenciales
    if (
        !$usuario ||
        $usuario['estado'] !== 'activo' ||
        !password_verify($password, $usuario['password_hash'])
    ) {

        header('Location: ../index.php?error=credenciales');
        exit;
    }

    // Regenerar identificador de sesión
    session_regenerate_id(true);

    // Guardar información del usuario
    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'nombre' => $usuario['nombre'],
        'apellido' => $usuario['apellido'],
        'correo' => $usuario['correo'],
        'rol' => $usuario['rol']
    ];

    // Redirigir al dashboard
    header('Location: ../dashboard/index.php');
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit('No fue posible procesar el inicio de sesión.');

}