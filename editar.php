
<?php
session_start();
require_once "conexion.php";

// 1. Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["uid"])) {
    header("Location: login.php");
    exit;
}

// 2. Validar el ID recibido por la URL
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

// 3. Buscar el evento y comprobar que pertenezca al usuario
$usuario_id = (int) $_SESSION["uid"];

$sql = "SELECT id, titulo, fecha, hora, categoria_id, descripcion
        FROM eventos
        WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();
$evento = $resultado->fetch_assoc();

$stmt->close();

// Si el evento no existe o pertenece a otro usuario, se rechaza el acceso
if (!$evento) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

// 4. Consultar las categorías disponibles
$categorias = $conn->query(
    "SELECT id, nombre FROM categoria ORDER BY nombre ASC"
);

if (!$categorias) {
    $conn->close();
    die("Error al consultar las categorías.");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento | AgendaWeb</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/p1.css">

    <style>
        .formulario input,
        .formulario select,
        .formulario textarea {
            box-sizing: border-box;
            max-width: 100%;
        }

        .aviso-edicion {
            margin-bottom: 20px;
            padding: 14px;
            border-radius: 8px;
            background: #e5f1ed;
            color: #17312b;
        }

        .btn-cancelar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 8px;
            background: #e5f1ed;
            color: #17312b;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="logo">Agenda<span>Web</span></div>
        <a href="index.php" class="mi-agenda">▣ Mi Agenda</a>
    </header>

    <main>
        <section class="contenedor">

            <div class="encabezado">
                <h1>Editar Evento</h1>
                <p>Modifica los datos del evento seleccionado.</p>
            </div>

            <div class="aviso-edicion">
                Estás editando:
                <strong><?= htmlspecialchars($evento["titulo"], ENT_QUOTES, "UTF-8") ?></strong>
            </div>

            <form action="actualizar.php" method="POST" class="formulario">

                <input type="hidden" name="id" value="<?= (int) $evento["id"] ?>">

                <div class="campo">
                    <label for="titulo">Título del evento</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($evento["titulo"], ENT_QUOTES, "UTF-8") ?>"
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
                            value="<?= htmlspecialchars($evento["fecha"], ENT_QUOTES, "UTF-8") ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="hora">Hora</label>
                        <input
                            type="time"
                            id="hora"
                            name="hora"
                            required
                            value="<?= htmlspecialchars(substr($evento["hora"], 0, 5), ENT_QUOTES, "UTF-8") ?>"
                        >
                    </div>
                </div>

                <div class="campo">
                    <label for="categoria_id">Categoría</label>

                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">Selecciona una categoría</option>

                        <?php while ($categoria = $categorias->fetch_assoc()): ?>
                            <option
                                value="<?= (int) $categoria["id"] ?>"
                                <?= ((int) $evento["categoria_id"] === (int) $categoria["id"]) ? "selected" : "" ?>
                            >
                                <?= htmlspecialchars($categoria["nombre"], ENT_QUOTES, "UTF-8") ?>
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
                    ><?= htmlspecialchars($evento["descripcion"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
                </div>

                <div class="acciones">
                    <a href="index.php" class="btn-cancelar">Cancelar</a>

                    <button type="submit" class="btn-guardar">
                        Guardar cambios
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
$categorias->free();
$conn->close();
?>
