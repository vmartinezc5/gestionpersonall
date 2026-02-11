<?php
// actualizar_empleado.php
require_once 'seguridad.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id = $_POST['id'];
    $foto_nombre = $_POST['foto_actual']; // Mantenemos la foto vieja por defecto

    // 1. Manejo de Nueva Foto (si se subió)
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $directorio = "uploads/";
        $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        // Creamos un nombre único para evitar caché o duplicados
        $nuevo_nombre_foto = "empleado_" . $id . "_" . time() . "." . $extension;
        
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $directorio . $nuevo_nombre_foto)) {
            // Borramos la foto anterior si existe y no es la default
            if (!empty($foto_nombre) && file_exists($directorio . $foto_nombre)) {
                unlink($directorio . $foto_nombre);
            }
            $foto_nombre = $nuevo_nombre_foto;
        }
    }

    try {
        $sql = "UPDATE empleados SET 
                dpi = ?, nit = ?, nombres = ?, apellidos = ?, fecha_nacimiento = ?, 
                genero = ?, tipo_sangre = ?, telefono = ?, correo_electronico = ?, 
                direccion = ?, contacto_emergencia_nombre = ?, contacto_emergencia_telefono = ?, 
                id_renglon = ?, id_area = ?, fecha_inicio_labores = ?, 
                id_puesto_nominal = ?, id_puesto_funcional = ?, 
                foto_perfil = ?, estado = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $_POST['dpi'], $_POST['nit'], $_POST['nombres'], $_POST['apellidos'], $_POST['fecha_nacimiento'],
            $_POST['genero'], $_POST['tipo_sangre'], $_POST['telefono'], $_POST['correo_electronico'],
            $_POST['direccion'], $_POST['contacto_emergencia_nombre'], $_POST['contacto_emergencia_telefono'],
            $_POST['id_renglon'], $_POST['id_area'], $_POST['fecha_inicio_labores'],
            $_POST['id_puesto_nominal'], $_POST['id_puesto_funcional'],
            $foto_nombre, $_POST['estado'], 
            $id
        ]);

        // Redireccionar con éxito
        header("Location: index.php?status=success");
        exit;

    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
}
?>