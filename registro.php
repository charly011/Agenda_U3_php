
<?php
session_start();
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '' || $correo === '' || $clave === '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe un correo electrónico válido.';
    } elseif (strlen($clave) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($clave !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($clave, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare(
                'INSERT INTO usuarios (nombre, correo, clave_hash)
                 VALUES (?, ?, ?)'
            );

            $stmt->bind_param('sss', $nombre, $correo, $hash);
            $stmt->execute();
            $stmt->close();

            header('Location: login.php?registro=exitoso');
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = 'Este correo ya está registrado.';
            } else {
                $error = 'Error al registrar el usuario. Verifica la conexión y la tabla usuarios.';
            }
        }
    }
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | AgendaWeb</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        .registro-contenedor {
            max-width: 450px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,.12);
        }

        .registro-contenedor input {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            margin: 8px 0 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .registro-contenedor button {
            width: 100%;
            padding: 12px;
            cursor: pointer;
        }

        .error {
            color: #a40000;
            background: #ffe5e5;
            padding: 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <main class="registro-contenedor">
        <h1>Crear cuenta</h1>
        <p>Regístrate para comenzar a administrar tus eventos.</p>

        <?php if ($error !== ''): ?>
            <p class="error"><?= escapar($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <label for="nombre">Nombre completo</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="80"
                required
            >

            <label for="correo">Correo electrónico</label>
            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="120"
                required
            >

            <label for="clave">Contraseña</label>
            <input
                type="password"
                id="clave"
                name="clave"
                minlength="8"
                required
            >

            <label for="confirmar">Confirmar contraseña</label>
            <input
                type="password"
                id="confirmar"
                name="confirmar"
                minlength="8"
                required
            >

            <button type="submit">Registrarme</button>
        </form>

        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
        <p><a href="login.php">Volver a la agenda</a></p>
    </main>
</body>
</html>
