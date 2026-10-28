
<?php

session_start();
require_once "conexion.php";

// 1. Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["uid"])) {
    $conn->close();
    header("Location: login.php");
    exit;
}

// 2. Aceptar únicamente solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $conn->close();
    header("Location: index.php");
    exit;
}

// Obtener el usuario que inició sesión
$usuario_id = (int) $_SESSION["uid"];

// 3. Obtener los datos del formulario
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$titulo = trim($_POST["titulo"] ?? "");
$fecha = $_POST["fecha"] ?? "";
$hora = $_POST["hora"] ?? "";
$categoria_id = $_POST["categoria_id"] ?? "";
$descripcion = trim($_POST["descripcion"] ?? "");

// 4. Validar los datos
$fechaValida = DateTime::createFromFormat("!Y-m-d", $fecha);
$horaValida = DateTime::createFromFormat("!H:i", $hora);

if (
    !$id ||
    $id < 1 ||
    $titulo === "" ||
    strlen($titulo) > 150 ||
    !$fechaValida ||
    $fechaValida->format("Y-m-d") !== $fecha ||
    !$horaValida ||
    $horaValida->format("H:i") !== $hora ||
    !ctype_digit((string) $categoria_id) ||
    (int) $categoria_id < 1
) {
    $conn->close();
    header("Location: index.php?error=datos");
    exit;
}

$categoria_id = (int) $categoria_id;

// 5. Verificar que la categoría exista
$stmtCategoria = $conn->prepare(
    "SELECT id FROM categoria WHERE id = ?"
);

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

// 6. Actualizar únicamente si el evento pertenece al usuario actual
$sql = "UPDATE eventos
        SET titulo = ?,
            fecha = ?,
            hora = ?,
            categoria_id = ?,
            descripcion = ?
        WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

$stmt->bind_param(
    "sssisii",
    $titulo,
    $fecha,
    $hora,
    $categoria_id,
    $descripcion,
    $id,
    $usuario_id
);


// Ejecutar la actualización
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    header("Location: index.php?actualizado=1");
    exit;
}

$stmt->close();
$conn->close();

header("Location: index.php?error=1");
exit;

?>