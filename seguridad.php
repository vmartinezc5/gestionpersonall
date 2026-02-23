<?php
// seguridad.php

// 1. Verificamos si la sesión ya fue iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- NUEVO: Control de Inactividad (40 minutos) ---
$tiempo_maximo_inactividad = 20 * 60; // 40 minutos multiplicados por 60 segundos (2400)

if (isset($_SESSION['ultimo_acceso'])) {
    // Calculamos cuánto tiempo ha pasado desde el último clic
    $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];
    
    if ($tiempo_inactivo > $tiempo_maximo_inactividad) {
        // Si el tiempo superó los 40 min, destruimos la sesión por seguridad
        session_unset();
        session_destroy();
        
        // Redirigimos al login con una alerta por la URL
        header("Location: identificador.php?error=expirado");
        exit;
    }
}
// Si el usuario acaba de dar clic en algo, reiniciamos el cronómetro a la hora actual
$_SESSION['ultimo_acceso'] = time();
// ---------------------------------------------------

// 2. Si NO existe la variable de sesión, lo mandamos al login
if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: identificador.php");
    exit;
}
?>