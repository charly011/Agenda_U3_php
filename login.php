
<?php
session_start();

require_once 'conexion.php';

// Si ya inició sesión, ir a la agenda
if (isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $clave = $_POST['clave'] ?? '';

    if ($correo === '' || $clave === '') {
        $error = 'Ingresa tu correo y contraseña.';
    } else {

        $stmt = $conn->prepare(
            'SELECT id, nombre, correo, clave_hash
             FROM usuarios
             WHERE correo = ?
             LIMIT 1'
        );

        if (!$stmt) {
            die('Error al preparar la consulta: ' . $conn->error);
        }

        $stmt->bind_param('s', $correo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        $stmt->close();

        if ($usuario && password_verify($clave, $usuario['clave_hash'])) {

            // Crear una nueva sesión segura
            session_regenerate_id(true);

            $_SESSION['uid'] = (int)$usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];

            header('Location: index.php');
            exit;

        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}

function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | AgendaWeb</title>

    <link rel="stylesheet" href="css/index.css">

    <style>
        .login-contenedor {
            max-width: 420px;
            margin: 60px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        }

        .login-contenedor h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .login-contenedor p {
            text-align: center;
        }

        .login-contenedor label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }

        .login-contenedor input {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .login-contenedor button {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            cursor: pointer;
        }

        .login-error {
            color: #a40000;
            background: #ffe5e5;
            padding: 12px;
            border-radius: 6px;
        }

        .login-exito {
            color: #176534;
            background: #e5f8eb;
            padding: 12px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <main class="login-contenedor">

        <h1>Iniciar sesión</h1>

        <p>Bienvenido a AgendaWeb</p>

        <?php if (isset($_GET['registro']) &&
                  $_GET['registro'] === 'exitoso'): ?>
            <p class="login-exito">
                Tu cuenta se creó correctamente.
                Ahora puedes iniciar sesión.
            </p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="login-error">
                <?= escapar($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <label for="correo">Correo electrónico</label>

            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="120"
                value="<?= escapar($correo) ?>"
                autocomplete="username"
                required
            >

            <label for="clave">Contraseña</label>

            <input
                type="password"
                id="clave"
                name="clave"
                autocomplete="current-password"
                required
            >

            <button type="submit">
                Iniciar sesión
            </button>

        </form>

        <p>
            ¿Todavía no tienes cuenta?
            <a href="registro.php">Crear una cuenta</a>
        </p>

        <p>
            <a href="index.php">Volver a la agenda</a>
        </p>

    </main>

</body>
</html>
