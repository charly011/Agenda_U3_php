
<?php
session_start();

// Vaciar las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Regresar al inicio de sesión
header('Location: login.php');
exit;
