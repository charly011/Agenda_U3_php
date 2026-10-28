
<?php

session_start();
require_once "conexion.php";

// 1. Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["uid"])) {
    $conn->close();
    header("Location: login.php");
    exit;
}

// 2. Consultar las categorías
$sql = "SELECT id, nombre FROM categoria ORDER BY nombre ASC";
$resultado = $conn->query($sql);

if (!$resultado) {
    $conn->close();
    die("Error al consultar las categorías.");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Evento | AgendaWeb</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/p1.css">
</head>

<body>

    <header class="header">

        <div class="logo">
            Agenda<span>Web</span>
        </div>

        <a href="index.php" class="mi-agenda">
            ▣ Mi Agenda
        </a>

    </header>

    <main>
        <section class="contenedor">

            <div class="encabezado">
                <h1>Registrar Nuevo Evento</h1>
                <p>Agrega un nuevo evento a tu agenda.</p>
            </div>

            <form action="guardar.php" method="POST" class="formulario">

                <div class="campo">
                    <label for="titulo">Título del evento</label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        placeholder="Ej. Reunión de equipo"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="fila">

                    <div class="campo">
                        <label for="fecha">Fecha</label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="hora">Hora</label>

                        <input
                            type="time"
                            id="hora"
                            name="hora"
                            required
                        >
                    </div>

                </div>

                <div class="campo">
                    <label for="categoria_id">Categoría</label>

                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">Selecciona una categoría</option>

                        <?php while ($categoria = $resultado->fetch_assoc()): ?>

                            <option value="<?= (int) $categoria["id"]; ?>">
                                <?= htmlspecialchars(
                                    $categoria["nombre"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="5"
                        placeholder="Agrega una descripción del evento..."
                    ></textarea>
                </div>

                <div class="acciones">

                    <button type="reset" class="btn-limpiar">
                        Limpiar
                    </button>

                    <button type="submit" class="btn-guardar">
                        ✓ &nbsp; Guardar Evento
                    </button>

                </div>

            </form>

        </section>
    </main>

    <footer>
        © 2026 AgendaWeb · Organiza tu tiempo
    </footer>

</body>
</html>

<?php
$resultado->free();
$conn->close();
?>
