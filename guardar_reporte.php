<?php
// guardar_reporte.php
require_once 'config/db.php';
require_once 'seguridad.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_empleado = $_POST['id_empleado'];
    $tipo = $_POST['tipo'];
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $archivo_nombre = null;

    // Manejo de Archivo Adjunto (Upload)
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $directorio = "uploads/";
        // Verificamos si existe la carpeta, si no, la creamos
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        // Generamos un nombre único para evitar sobreescribir archivos (ID_Timestamp_NombreOriginal)
        $archivo_nombre = $id_empleado . "_" . time() . "_" . basename($_FILES['archivo']['name']);
        $ruta_final = $directorio . $archivo_nombre;

        // Mover el archivo de temporal a la carpeta final
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_final)) {
            // Si falla la subida, guardamos sin archivo o mostramos error
            $archivo_nombre = null; 
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO historial_reportes (id_empleado, tipo, motivo, fecha_incidente, archivo_adjunto) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_empleado, $tipo, $motivo, $fecha, $archivo_nombre]);
        
        // Redirigir de vuelta al perfil
        header("Location: perfil_empleado.php?id=$id_empleado&msg=reporte_guardado");
    } catch (PDOException $e) {
        die("Error al guardar reporte: " . $e->getMessage());
    }
}
?>