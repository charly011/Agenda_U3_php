
<?php

require_once __DIR__ . '/../partes/admin.php';
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: usuarios.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['mensaje_admin'] = "Usuario no válido.";
    header("Location: usuarios.php");
    exit;
}

if ($id === (int)$_SESSION['uid']) {
    $_SESSION['mensaje_admin'] = "No puedes eliminar tu propia cuenta.";
    header("Location: usuarios.php");
    exit;
}

$stmt = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION['mensaje_admin'] = "El usuario no existe.";
    header("Location: usuarios.php");
    exit;
}

if ($usuario['rol'] === 'admin') {

    $resultado = $conn->query(
        "SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'admin'"
    );

    $totalAdmins = (int)$resultado->fetch_assoc()['total'];

    if ($totalAdmins <= 1) {
        $_SESSION['mensaje_admin'] = "No puedes eliminar al último administrador.";
        header("Location: usuarios.php");
        exit;
    }
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['mensaje_admin'] = "Usuario eliminado correctamente.";
} else {
    $_SESSION['mensaje_admin'] = "No se pudo eliminar el usuario.";
}

$stmt->close();

header("Location: usuarios.php");
exit;
