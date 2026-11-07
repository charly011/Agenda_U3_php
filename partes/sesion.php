
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requiere_login() {
    if (!isset($_SESSION['uid'])) {
        header("Location: login.php");
        exit;
    }
}

function requiere_rol($rol) {
    requiere_login();

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol) {
        http_response_code(403);
        exit("No tienes permiso para acceder a esta página.");
    }
}
