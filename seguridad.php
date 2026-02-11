<?php
// seguridad.php
session_start();

// Si NO existe la variable de sesión, lo mandamos al login
if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: identificador.php");
    exit;
}
?>