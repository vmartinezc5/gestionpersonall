<?php
// config/db.php

// 1. Credenciales de la Base de Datos
$host = 'localhost';
$port = '3306';        // Puerto específico de tu XAMPP
$db   = 'sistema_rrhh'; // Nombre de base de datos. 
$user = 'root';        // Usuario por defecto
$pass = '';            // Contraseña por defecto
$charset = 'utf8mb4';

// 2. Data Source Name (DSN)
// AGREGADO: "port=$port" para decirle a PDO dónde tocar la puerta.
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// 3. Opciones de configuración de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 4. Intento de conexión (Try-Catch)
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Si no hay error aquí, la conexión fue exitosa.
} catch (\PDOException $e) {
    // Capturamos el error y lanzamos la excepción
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>