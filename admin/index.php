
<?php

require_once __DIR__ . '/../partes/admin.php';
require_once __DIR__ . '/../conexion.php';

$totalUsuarios = 0;
$totalEventos = 0;

$resultadoUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios");
if ($resultadoUsuarios) {
    $totalUsuarios = $resultadoUsuarios->fetch_assoc()['total'];
}

$resultadoEventos = $conn->query("SELECT COUNT(*) AS total FROM eventos");
if ($resultadoEventos) {
    $totalEventos = $resultadoEventos->fetch_assoc()['total'];
}

$nombre = htmlspecialchars($_SESSION['nombre']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrador | AgendaWeb</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<header class="barra-superior">
    <div class="logo">Agenda<span>Web</span></div>

    <nav>
        <a href="index.php" class="activo">Panel</a>
        <a href="usuarios.php">Usuarios</a>
        <a href="eventos.php">Eventos</a>
        <a href="../logout.php" class="cerrar">Cerrar sesión</a>
    </nav>
</header>

<main class="contenido">

    <section class="bienvenida">
        <div>
            <span class="etiqueta">PANEL DE ADMINISTRACIÓN</span>
            <h1>¡Bienvenido, <?= $nombre ?>!</h1>
            <p>Desde aquí puedes administrar AgendaWeb.</p>
        </div>
        <div class="icono-admin">♙</div>
    </section>

    <section class="estadisticas">

        <article class="tarjeta">
            <div class="icono">♙</div>
            <div>
                <p>Usuarios registrados</p>
                <h2><?= $totalUsuarios ?></h2>
            </div>
        </article>

        <article class="tarjeta">
            <div class="icono">▣</div>
            <div>
                <p>Eventos registrados</p>
                <h2><?= $totalEventos ?></h2>
            </div>
        </article>

    </section>

    <section class="seccion-opciones">
        <h2>Administración</h2>

        <div class="opciones">

            <a href="usuarios.php" class="opcion">
                <div class="opcion-icono">♙</div>
                <h3>Administrar usuarios</h3>
                <p>Consultar, editar, cambiar roles y restablecer contraseñas.</p>
                <span>Gestionar usuarios →</span>
            </a>

            <a href="eventos.php" class="opcion">
                <div class="opcion-icono">▣</div>
                <h3>Administrar eventos</h3>
                <p>Consultar los eventos registrados por todos los usuarios.</p>
                <span>Consultar eventos →</span>
            </a>

        </div>
    </section>

</main>

</body>
</html>
