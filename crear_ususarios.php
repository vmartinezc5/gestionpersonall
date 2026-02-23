<?php
// crear_usuarios_masivo.php
require_once 'config/db.php';

$usuarios_nuevos = [
	 [
        'user' => 'JEFATURA',
        'pass' => 'Jefatura2026',
        'nombre' => 'Jefatura Principal',
        'rol' => 'Jefatura '
    ],
    [
        'user' => 'CANALY',
        'pass' => '6695',
        'nombre' => 'Secretaria Carmen',
        'rol' => 'Secretaria'
    ],
    [
        'user' => 'VESMERALDA',
        'pass' => '3330',
        'nombre' => 'Secretaria Vilma',
        'rol' => 'Secretaria'
    ],
    [
        'user' => 'MMILTON',
        'pass' => '7761',
        'nombre' => 'Jefe Milton',
        'rol' => 'Jefe de Departamento'
    ]
];

echo "<h2>Procesando creación de usuarios...</h2>";

try {
    // 1. Verificamos primero si el usuario ya existe para evitar errores
    $check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = ?");
    $sql = "INSERT INTO usuarios (nombre_usuario, password, nombre_completo, rol) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    foreach ($usuarios_nuevos as $u) {
        $check->execute([$u['user']]);
        if ($check->fetchColumn() > 0) {
            echo "⚠️ El usuario <b>{$u['user']}</b> ya existe en la base de datos. Saltando...<br>";
            continue;
        }

        $hash = password_hash($u['pass'], PASSWORD_DEFAULT);
        
        if ($stmt->execute([$u['user'], $hash, $u['nombre'], $u['rol']])) {
            echo "✅ Usuario <b>{$u['user']}</b> creado con éxito.<br>";
        } else {
            echo "❌ Error al crear usuario <b>{$u['user']}</b>.<br>";
        }
    }

    echo "<br><p style='color:red;'><b>IMPORTANTE:</b> Borra este archivo del servidor una vez termines.</p>";

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}
?>