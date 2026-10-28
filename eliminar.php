
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

// 3. Obtener y validar el ID del evento
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

// 4. Obtener el ID del usuario que inició sesión
$usuario_id = (int) $_SESSION["uid"];

// 5. Eliminar únicamente eventos pertenecientes a ese usuario
$sql = "DELETE FROM eventos
        WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $conn->close();
    header("Location: index.php?error=1");
    exit;
}

$stmt->bind_param("ii", $id, $usuario_id);

// 6. Ejecutar la eliminación
if ($stmt->execute() && $stmt->affected_rows === 1) {
    $stmt->close();
    $conn->close();

    header("Location: index.php?eliminado=1");
    exit;
}

$stmt->close();
$conn->close();

header("Location: index.php?error=1");
exit;

?>
