<?php
session_start();

// Si el usuario ya está logueado, redirigir al dashboard en la carpeta public
if (isset($_SESSION['usuario'])) {
    header("Location: public/Dashboard/opt.php");
    exit;
}

// Si no está logueado, redirigir al login en la carpeta public
header("Location: public/login.php");
exit;
?>
