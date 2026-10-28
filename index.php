
<?php
session_start();

// Verificar si el usuario inició sesión
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

require_once "conexion.php";

$buscar = trim($_GET["buscar"] ?? "");
$categoria_id = $_GET["categoria_id"] ?? "";
$fecha_inicio = $_GET["fecha_inicio"] ?? "";
$fecha_fin = $_GET["fecha_fin"] ?? "";

$sqlCategorias = "SELECT id, nombre FROM categoria ORDER BY nombre ASC";
$categorias = $conn->query($sqlCategorias);

$sql = "SELECT eventos.id, eventos.titulo, eventos.fecha, eventos.hora,
               eventos.descripcion, eventos.categoria_id,
               categoria.nombre AS nombre_categoria
        FROM eventos
        LEFT JOIN categoria ON eventos.categoria_id = categoria.id
        WHERE eventos.usuario_id = ?";

$tipos = "i";
$parametros = [(int) $_SESSION['uid']];

if ($buscar !== "") {
    $sql .= " AND (eventos.titulo LIKE ? OR eventos.descripcion LIKE ?)";
    $texto = "%" . $buscar . "%";
    $tipos .= "ss";
    $parametros[] = $texto;
    $parametros[] = $texto;
}

if (
    $categoria_id !== "" &&
    ctype_digit((string) $categoria_id) &&
    (int) $categoria_id > 0
) {
    $sql .= " AND eventos.categoria_id = ?";
    $tipos .= "i";
    $parametros[] = (int) $categoria_id;
}

if (
    $fecha_inicio !== "" &&
    $fecha_fin !== "" &&
    $fecha_inicio > $fecha_fin
) {
    $temporal = $fecha_inicio;
    $fecha_inicio = $fecha_fin;
    $fecha_fin = $temporal;
}

if ($fecha_inicio !== "") {
    $sql .= " AND eventos.fecha >= ?";
    $tipos .= "s";
    $parametros[] = $fecha_inicio;
}

if ($fecha_fin !== "") {
    $sql .= " AND eventos.fecha <= ?";
    $tipos .= "s";
    $parametros[] = $fecha_fin;
}

$sql .= " ORDER BY eventos.fecha ASC, eventos.hora ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

if ($tipos !== "") {
    $argumentos = [$tipos];

    foreach ($parametros as $clave => $valor) {
        $argumentos[] = &$parametros[$clave];
    }

    call_user_func_array([$stmt, "bind_param"], $argumentos);
}

$stmt->execute();
$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi Agenda | AgendaWeb</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/index.css">

    <style>
        .filtros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            padding: 20px;
            margin: 24px 0;
            background: #fff;
            border: 1px solid #e5f1ed;
            border-radius: 14px;
        }

        .filtros label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #17312b;
        }

        .filtros input,
        .filtros select {
            box-sizing: border-box;
            width: 100%;
            min-height: 42px;
            padding: 10px;
            border: 1px solid #c8d8d3;
            border-radius: 8px;
            background: #fff;
            color: #17312b;
            font: inherit;
        }

        .acciones-filtros {
            display: flex;
            align-items: end;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-buscar,
        .btn-limpiar-filtros {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-buscar {
            background: #176b5b;
            color: #fff;
        }

        .btn-limpiar-filtros {
            background: #e5f1ed;
            color: #17312b;
        }

        .acciones-evento {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .btn-editar,
        .btn-eliminar {
            padding: 8px 12px;
            border: none;
            border-radius: 7px;
            text-decoration: none;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-editar {
            background: #e5f1ed;
            color: #125648;
        }

        .btn-eliminar {
            background: #fff0f0;
            color: #a12828;
        }

        .sin-resultados {
            padding: 30px 20px;
            text-align: center;
        }

        /* Nuevo: nombre del usuario */
        .bienvenida-usuario {
            margin-top: 12px;
            color: #176b5b;
            font-size: 15px;
            font-weight: 600;
        }

        /* Nuevo: botones del encabezado */
        .acciones-header {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-cerrar-sesion {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 8px;
            background: #e5f1ed;
            color: #125648;
            text-decoration: none;
            font: inherit;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .btn-cerrar-sesion:hover {
            background: #d3e8e1;
        }

        @media (max-width: 600px) {
            .filtros {
                grid-template-columns: 1fr;
            }

            .acciones-header {
                gap: 8px;
            }
        }
    </style>
</head>

<body>

    <?php if (isset($_GET["guardado"])): ?>

        <div class="mensaje-flotante mensaje-exito">
            <span class="icono-mensaje">✓</span>
            <div>
                <strong>¡Evento guardado!</strong>
                <p>El evento se registró correctamente.</p>
            </div>
            <button type="button" onclick="cerrarMensaje()">×</button>
        </div>

    <?php elseif (isset($_GET["actualizado"])): ?>

        <div class="mensaje-flotante mensaje-exito">
            <span class="icono-mensaje">✓</span>
            <div>
                <strong>¡Evento actualizado!</strong>
                <p>Los cambios se guardaron correctamente.</p>
            </div>
            <button type="button" onclick="cerrarMensaje()">×</button>
        </div>

    <?php elseif (isset($_GET["eliminado"])): ?>

        <div class="mensaje-flotante mensaje-exito">
            <span class="icono-mensaje">✓</span>
            <div>
                <strong>¡Evento eliminado!</strong>
                <p>El evento se eliminó correctamente.</p>
            </div>
            <button type="button" onclick="cerrarMensaje()">×</button>
        </div>

    <?php elseif (isset($_GET["error"])): ?>

        <div class="mensaje-flotante mensaje-error">
            <span class="icono-mensaje">!</span>
            <div>
                <strong>No se pudo completar la operación</strong>
                <p>Verifica los datos e inténtalo nuevamente.</p>
            </div>
            <button type="button" onclick="cerrarMensaje()">×</button>
        </div>

    <?php endif; ?>

    <header class="header">

        <div class="logo">Agenda<span>Web</span></div>

        <div class="acciones-header">

            <a href="p1.php" class="btn-agregar">
                <span class="icono">+</span>
                Agregar Evento
            </a>

            <a href="logout.php" class="btn-cerrar-sesion">
                Cerrar sesión
            </a>

        </div>

    </header>

    <main>
        <section class="contenedor">

            <div class="encabezado-agenda">
                <div>

                    <h1>Mi Agenda</h1>

                    <p>
                        Consulta, busca y organiza tus próximos eventos.
                    </p>

                    <p class="bienvenida-usuario">
                        Bienvenido,
                        <?= htmlspecialchars(
                            $_SESSION['nombre'] ?? 'Usuario',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                </div>
            </div>

            <form action="index.php" method="GET" class="filtros">

                <div>
                    <label for="buscar">Buscar evento</label>

                    <input
                        type="search"
                        id="buscar"
                        name="buscar"
                        value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Título o descripción"
                    >
                </div>

                <div>
                    <label for="categoria_id">Categoría</label>

                    <select id="categoria_id" name="categoria_id">

                        <option value="">Todas las categorías</option>

                        <?php if ($categorias): ?>
                            <?php while ($categoria = $categorias->fetch_assoc()): ?>

                                <option
                                    value="<?= (int) $categoria["id"] ?>"
                                    <?= ((string) $categoria_id === (string) $categoria["id"]) ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars(
                                        $categoria["nombre"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </option>

                            <?php endwhile; ?>
                        <?php endif; ?>

                    </select>
                </div>

                <div>
                    <label for="fecha_inicio">Desde</label>

                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="<?= htmlspecialchars($fecha_inicio, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div>
                    <label for="fecha_fin">Hasta</label>

                    <input
                        type="date"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="<?= htmlspecialchars($fecha_fin, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="acciones-filtros">

                    <button type="submit" class="btn-buscar">
                        Buscar
                    </button>

                    <a href="index.php" class="btn-limpiar-filtros">
                        Limpiar
                    </a>

                </div>

            </form>

            <div class="eventos">

                <?php if ($resultado->num_rows > 0): ?>

                    <?php while ($evento = $resultado->fetch_assoc()): ?>

                        <article class="evento">

                            <div class="fecha-evento">

                                <span class="dia">
                                    <?= date("d", strtotime($evento["fecha"])) ?>
                                </span>

                                <span class="mes">
                                    <?= strtoupper(date("M", strtotime($evento["fecha"]))) ?>
                                </span>

                            </div>

                            <div class="informacion-evento">

                                <div class="evento-superior">

                                    <h2>
                                        <?= htmlspecialchars(
                                            $evento["titulo"],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h2>

                                    <span class="categoria">
                                        <?= htmlspecialchars(
                                            $evento["nombre_categoria"] ?? "Sin categoría",
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                </div>

                                <div class="hora">
                                    ▣
                                    <?= date("h:i A", strtotime($evento["hora"])) ?>
                                </div>

                                <?php if (!empty($evento["descripcion"])): ?>

                                    <p class="descripcion">
                                        <?= htmlspecialchars(
                                            $evento["descripcion"],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>

                                <?php endif; ?>

                                <div class="acciones-evento">

                                    <a
                                        href="editar.php?id=<?= (int) $evento["id"] ?>"
                                        class="btn-editar"
                                    >
                                        ✎ Editar
                                    </a>

                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        onsubmit="return confirm('¿Estás seguro de que deseas eliminar este evento? Esta acción no se puede deshacer.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $evento["id"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-eliminar"
                                        >
                                            Eliminar
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </article>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="sin-eventos sin-resultados">

                        <div class="icono-vacio">⌕</div>

                        <h2>No se encontraron eventos</h2>

                        <p>
                            Prueba con otra búsqueda o modifica los filtros.
                        </p>

                        <a href="index.php" class="btn-primer-evento">
                            Mostrar todos los eventos
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>
    </main>

    <footer>
        © 2026 AgendaWeb · Organiza tu tiempo · Juan Carlos Gutiérrez López
    </footer>

    <script>
        function cerrarMensaje() {
            const mensaje = document.querySelector(".mensaje-flotante");

            if (mensaje) {
                mensaje.classList.add("mensaje-saliendo");

                setTimeout(() => mensaje.remove(), 400);
            }
        }

        setTimeout(cerrarMensaje, 4000);
    </script>

</body>
</html>

<?php
$resultado->free();

if ($categorias) {
    $categorias->free();
}

$stmt->close();
$conn->close();
?>
