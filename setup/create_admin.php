<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Comprobar si ya existe un administrador
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE r.nombre = 'administrador'
");

$stmt->execute();
$adminExiste = (int) $stmt->fetchColumn() > 0;

$mensaje = '';
$tipoMensaje = '';

if (!$adminExiste) {

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        $token = $_POST['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'], $token)) {

            $mensaje = 'Solicitud no válida.';
            $tipoMensaje = 'error';

        } elseif (!$nombre || !$apellido || !$correo || strlen($password) < 10) {

            $mensaje = 'Verifica los datos. La contraseña debe tener al menos 10 caracteres.';
            $tipoMensaje = 'error';

        } else {

            try {

                $stmt = $pdo->prepare("
                    SELECT id FROM roles
                    WHERE nombre = 'administrador'
                ");

                $stmt->execute();

                $rolId = $stmt->fetchColumn();

                if (!$rolId) {
                    throw new Exception('No existe el rol administrador.');
                }

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios
                    (rol_id, nombre, apellido, correo, password_hash)
                    VALUES
                    (:rol, :nombre, :apellido, :correo, :password)
                ");

                $stmt->execute([
                    'rol' => $rolId,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo,
                    'password' => $passwordHash
                ]);

                $mensaje = '¡Administrador creado correctamente!';
                $tipoMensaje = 'success';

                $adminExiste = true;

            } catch (PDOException $e) {

                error_log($e->getMessage());

                $mensaje = 'No se pudo crear la cuenta. Comprueba que el correo no esté registrado.';
                $tipoMensaje = 'error';

            } catch (Exception $e) {

                error_log($e->getMessage());

                $mensaje = 'No se pudo completar el registro.';
                $tipoMensaje = 'error';

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear administrador | SGCM</title>

    <link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>

<main class="dashboard-wrap">

    <section class="dash-card" style="max-width:500px;margin:60px auto;">

        <span class="eyebrow">CONFIGURACIÓN INICIAL</span>

        <h2>Crear administrador</h2>

        <?php if ($adminExiste): ?>

            <p>Ya existe un administrador registrado o la cuenta se acaba de crear.</p>

            <a href="../index.php" class="submit-button" style="text-decoration:none;">
                Ir al inicio de sesión →
            </a>

        <?php else: ?>

            <p>Registra la cuenta que tendrá el control administrativo de SGCM.</p>

            <?php if ($mensaje): ?>

                <p role="alert">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                </p>

            <?php endif; ?>

            <form method="POST" class="login-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                >

                <label for="nombre">Nombre</label>

                <input
                    class="input-wrap"
                    type="text"
                    id="nombre"
                    name="nombre"
                    maxlength="100"
                    required
                >

                <label for="apellido">Apellido</label>

                <input
                    class="input-wrap"
                    type="text"
                    id="apellido"
                    name="apellido"
                    maxlength="100"
                    required
                >

                <label for="correo">Correo electrónico</label>

                <input
                    class="input-wrap"
                    type="email"
                    id="correo"
                    name="correo"
                    maxlength="150"
                    required
                >

                <label for="password">Contraseña</label>

                <input
                    class="input-wrap"
                    type="password"
                    id="password"
                    name="password"
                    minlength="10"
                    required
                >

                <button type="submit" class="submit-button">
                    Crear administrador →
                </button>

            </form>

        <?php endif; ?>

    </section>

</main>

</body>
</html>