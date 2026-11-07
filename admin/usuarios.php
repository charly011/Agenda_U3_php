
<?php

require_once __DIR__ . '/../partes/admin.php';
require_once __DIR__ . '/../conexion.php';

$sql = "SELECT id, nombre, correo, rol, creado_en
        FROM usuarios
        ORDER BY creado_en DESC";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error al consultar usuarios: " . $conn->error);
}

$mensaje = $_SESSION['mensaje_admin'] ?? '';
unset($_SESSION['mensaje_admin']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | AgendaWeb</title>
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
            <h1>Usuarios registrados</h1>
            <p>Administra las cuentas de AgendaWeb.</p>
        </div>
        <div class="icono-admin">♙</div>
    </section>

    <?php if ($mensaje): ?>
        <div class="mensaje-exito">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <section class="tabla-contenedor">

        <div class="tabla-encabezado">
            <div>
                <h2>Lista de usuarios</h2>
                <p>Total de cuentas: <?= $resultado->num_rows ?></p>
            </div>

            <a href="index.php" class="boton-volver">Volver al panel</a>
        </div>

        <div class="tabla-scroll">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($usuario = $resultado->fetch_assoc()): ?>

                    <tr>
                        <td><?= (int)$usuario['id'] ?></td>

                        <td>
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($usuario['correo']) ?>
                        </td>

                        <td>
                            <span class="rol <?= $usuario['rol'] === 'admin' ? 'rol-admin' : 'rol-usuario' ?>">
                                <?= htmlspecialchars($usuario['rol']) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($usuario['creado_en']) ?>
                        </td>

                        <td>
                            <div class="acciones">

                                <a
                                    href="editar_usuario.php?id=<?= (int)$usuario['id'] ?>"
                                    class="accion-editar"
                                >
                                    Editar
                                </a>

                                <<form action="eliminar_usuario.php" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este usuario y sus eventos?')">

                                    <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">

                                    <button type="submit" class="accion-eliminar">
                                        Eliminar
                                    </button>

                                </form>

                            </div>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>

        </div>

    </section>

</main>

</body>
</html>
