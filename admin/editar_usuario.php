
<?php

require_once __DIR__ . '/../partes/admin.php';
require_once __DIR__ . '/../conexion.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: usuarios.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, nombre, correo, rol FROM usuarios WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION['mensaje_admin'] = "El usuario no existe.";
    header("Location: usuarios.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $rol = $_POST['rol'] ?? 'usuario';
    $clave = $_POST['clave'] ?? '';

    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Escribe un nombre y un correo válido.";
    } elseif (!in_array($rol, ['admin', 'usuario'], true)) {
        $error = "El rol seleccionado no es válido.";
    } elseif ($id === (int)$_SESSION['uid'] && $rol !== 'admin') {
        $error = "No puedes quitarte tu propio rol de administrador.";
    } elseif ($clave !== '' && strlen($clave) < 8) {
        $error = "La nueva contraseña debe tener al menos 8 caracteres.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id FROM usuarios WHERE correo = ? AND id <> ?"
        );
        $stmt->bind_param("si", $correo, $id);
        $stmt->execute();
        $duplicado = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($duplicado) {
            $error = "Ese correo ya está registrado.";
        } else {

            if ($clave !== '') {
                $hash = password_hash($clave, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "UPDATE usuarios
                     SET nombre = ?, correo = ?, rol = ?, clave_hash = ?
                     WHERE id = ?"
                );
                $stmt->bind_param(
                    "ssssi",
                    $nombre,
                    $correo,
                    $rol,
                    $hash,
                    $id
                );

            } else {

                $stmt = $conn->prepare(
                    "UPDATE usuarios
                     SET nombre = ?, correo = ?, rol = ?
                     WHERE id = ?"
                );
                $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);
            }

            if ($stmt->execute()) {

                if ($id === (int)$_SESSION['uid']) {
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['rol'] = $rol;
                }

                $stmt->close();

                $_SESSION['mensaje_admin'] = "Usuario actualizado correctamente.";
                header("Location: usuarios.php");
                exit;

            } else {
                $error = "No se pudieron guardar los cambios.";
                $stmt->close();
            }
        }
    }

    $usuario['nombre'] = $nombre;
    $usuario['correo'] = $correo;
    $usuario['rol'] = $rol;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario | AgendaWeb</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<header class="barra-superior">
    <div class="logo">Agenda<span>Web</span></div>
    <nav>
        <a href="index.php">Panel</a>
        <a href="usuarios.php" class="activo">Usuarios</a>
        <a href="eventos.php">Eventos</a>
        <a href="../logout.php" class="cerrar">Cerrar sesión</a>
    </nav>
</header>

<main class="contenido">

    <section class="bienvenida">
        <div>
            <span class="etiqueta">GESTIÓN DE CUENTAS</span>
            <h1>Editar usuario</h1>
            <p>Modifica la información de esta cuenta.</p>
        </div>
    </section>

    <section class="formulario-admin">

        <?php if ($error): ?>
            <div class="mensaje-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="id" value="<?= $id ?>">

            <label>Nombre completo</label>
            <input
                type="text"
                name="nombre"
                value="<?= htmlspecialchars($usuario['nombre']) ?>"
                required
            >

            <label>Correo electrónico</label>
            <input
                type="email"
                name="correo"
                value="<?= htmlspecialchars($usuario['correo']) ?>"
                required
            >

            <label>Rol de usuario</label>
            <select name="rol" required>
                <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>
                    Usuario
                </option>
                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>
                    Administrador
                </option>
            </select>

            <label>Nueva contraseña (opcional)</label>
            <input
                type="password"
                name="clave"
                minlength="8"
                autocomplete="new-password"
                placeholder="Dejar vacío para conservar la actual"
            >

            <div class="nota-formulario">
                La contraseña anterior no se puede consultar. Si necesitas cambiarla,
                escribe una nueva de al menos 8 caracteres.
            </div>

            <div class="botones-formulario">
                <button type="submit">Guardar cambios</button>
                <a href="usuarios.php">Cancelar</a>
            </div>

        </form>

    </section>

</main>

</body>
</html>
