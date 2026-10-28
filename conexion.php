
<?php

$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "agenda";
$puerto = 3307;

$conn = new mysqli(
    $servidor,
    $usuario,
    $password,
    $base_datos,
    $puerto
);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
