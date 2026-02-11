<?php
// test_db.php

// Incluimos el archivo de configuración
require_once 'config/db.php';

try {
    if ($pdo) {
        echo "<h1>¡Conexión Exitosa! 🚀</h1>";
        echo "<p>La base de datos <strong>'sistema_rrhh'</strong> está lista para recibir comandos.</p>";
    }
} catch (Exception $e) {
    echo "<h1>Error de Conexión ❌</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>