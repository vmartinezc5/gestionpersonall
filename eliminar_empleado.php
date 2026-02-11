<?php
// eliminar_empleado.php
require_once 'seguridad.php'; // Protegemos el archivo
require_once 'config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // PASO 1: Obtener el nombre de la foto antes de borrar el registro
        $stmt = $pdo->prepare("SELECT foto_perfil FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        // PASO 2: Borrar la foto de la carpeta 'uploads' si existe
        if ($empleado && !empty($empleado['foto_perfil'])) {
            $ruta_foto = "uploads/" . $empleado['foto_perfil'];
            if (file_exists($ruta_foto)) {
                unlink($ruta_foto); // unlink borra el archivo físico
            }
        }

        // PASO 3: Eliminar el registro de la base de datos
        // Nota: Gracias al ON DELETE CASCADE que pusimos en las tablas de sanciones/asistencia,
        // al borrar al empleado se borrarán automáticamente sus reportes y asistencias.
        $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = ?");
        $stmt->execute([$id]);

        // Redirigir con mensaje de éxito
        header("Location: index.php?status=deleted");
        exit;

    } catch (PDOException $e) {
        // Si hay error (ej: restricción de llave foránea mal configurada)
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>