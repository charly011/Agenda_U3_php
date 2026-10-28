
<?php
session_start();

require_once "conexion.php";

// 1. Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

// 2. Permitir únicamente solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// 3. Obtener el ID del usuario conectado
$usuario_id = (int) $_SESSION['uid'];

// 4. Obtener los datos del formulario
$titulo = trim($_POST["titulo"] ?? "");
$fecha = $_POST["fecha"] ?? "";
$hora = $_POST["hora"] ?? "";
$categoria_id = $_POST["categoria_id"] ?? "";
$descripcion = trim($_POST["descripcion"] ?? "");

// 5. Validar los campos obligatorios
if (
    $titulo === "" ||
    $fecha === "" ||
    $hora === "" ||
    $categoria_id === "" ||
    !ctype_digit((string) $categoria_id) ||
    (int) $categoria_id <= 0
) {
    $conn->close();
    header("Location: index.php?error=campos");
    exit;
}

$categoria_id = (int) $categoria_id;

// 6. Verificar que la categoría exista
$sqlCategoria = "SELECT id FROM categoria WHERE id = ?";
$stmtCategoria = $conn->prepare($sqlCategoria);

if (!$stmtCategoria) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

$stmtCategoria->bind_param("i", $categoria_id);
$stmtCategoria->execute();
$stmtCategoria->store_result();

if ($stmtCategoria->num_rows === 0) {
    $stmtCategoria->close();
    $conn->close();

    header("Location: index.php?error=categoria");
    exit;
}

$stmtCategoria->close();

// 7. Guardar el evento asociado al usuario conectado
$sql = "INSERT INTO eventos
        (usuario_id, titulo, fecha, hora, categoria_id, descripcion)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

// usuario_id, titulo, fecha, hora, categoria_id, descripcion
$stmt->bind_param(
    "isssis",
    $usuario_id,
    $titulo,
    $fecha,
    $hora,
    $categoria_id,
    $descripcion
);

// 8. Ejecutar y comprobar el resultado
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: index.php?guardado=1");
    exit;
}

$stmt->close();
$conn->close();

header("Location: index.php?error=1");
exit;
?>
