<?php
// crear_admin.php
require_once 'config/db.php';

$user = "JEFATURA";
$pass = "Jefatura2026";
$hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre_usuario, password, nombre_completo, rol) VALUES (?, ?, 'Jefatura Principal', 'Administrador')";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$user, $hash])) {
    echo "Usuario creado con éxito. Ya puedes borrar este archivo y loguearte.";
} else {
    echo "Error al crear usuario.";
}
?>