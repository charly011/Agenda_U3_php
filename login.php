
<?php

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/partes/sesion.php';

if (isset($_SESSION['uid'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $clave = $_POST['clave'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, nombre, correo, clave_hash, rol
         FROM usuarios
         WHERE correo = ?
         LIMIT 1"
    );

    $stmt->bind_param("s", $correo);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($clave, $usuario['clave_hash'])) {

        session_regenerate_id(true);

        $_SESSION['uid'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];

        if ($usuario['rol'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }

        exit;

    } else {
        $error = "Correo o contraseña incorrectos.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | AgendaWeb</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="contenedor-login">

    <h1>Agenda<span>Web</span></h1>
    <p>Inicia sesión en tu cuenta</p>

    <?php if ($error): ?>
        <div class="mensaje-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label for="correo">Correo electrónico</label>
        <input
            type="email"
            id="correo"
            name="correo"
            required
            autocomplete="username"
        >

        <label for="clave">Contraseña</label>
        <input
            type="password"
            id="clave"
            name="clave"
            required
            autocomplete="current-password"
        >

        <button type="submit">Iniciar sesión</button>

    </form>

    <p class="registro">
        ¿No tienes cuenta?
        <a href="registro.php">Crear cuenta</a>
    </p>

</div>

</body>
</html>
