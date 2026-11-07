
<?php

require_once __DIR__ . '/../partes/admin.php';
require_once __DIR__ . '/../conexion.php';

$sql = "SELECT
            e.id,
            e.titulo,
            e.fecha,
            e.hora,
            e.descripcion,
            u.nombre AS usuario,
            u.correo,
            c.nombre AS categoria
        FROM eventos e
        INNER JOIN usuarios u ON u.id = e.usuario_id
        LEFT JOIN categoria c ON c.id = e.categoria_id
        ORDER BY e.fecha DESC, e.hora DESC";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error al consultar eventos: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos | AgendaWeb</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<header class="barra-superior">
    <div class="logo">Agenda<span>Web</span></div>

    <nav>
        <a href="index.php">Panel</a>
        <a href="usuarios.php">Usuarios</a>
        <a href="eventos.php" class="activo">Eventos</a>
        <a href="../logout.php" class="cerrar">Cerrar sesión</a>
    </nav>
</header>

<main class="contenido">

    <section class="bienvenida">
        <div>
            <span class="etiqueta">GESTIÓN DE EVENTOS</span>
            <h1>Todos los eventos</h1>
            <p>Consulta los eventos registrados por los usuarios.</p>
        </div>

        <div class="icono-admin">▣</div>
    </section>

    <section class="tabla-contenedor">

        <div class="tabla-encabezado">
            <div>
                <h2>Eventos registrados</h2>
                <p>Total de eventos: <?= $resultado->num_rows ?></p>
            </div>

            <a href="index.php" class="boton-volver">Volver al panel</a>
        </div>

        <div class="tabla-scroll">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Evento</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Categoría</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Descripción</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($resultado->num_rows > 0): ?>

                    <?php while ($evento = $resultado->fetch_assoc()): ?>

                        <tr>
                            <td><?= (int)$evento['id'] ?></td>

                            <td>
                                <?= htmlspecialchars($evento['titulo']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($evento['fecha']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($evento['hora']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($evento['categoria'] ?? 'Sin categoría') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($evento['usuario']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($evento['correo']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($evento['descripcion'] ?? '') ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8">Todavía no hay eventos registrados.</td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </section>

</main>

</body>
</html>
