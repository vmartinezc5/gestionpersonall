<?php
// actualizar_empleado.php
require_once 'seguridad.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id = $_POST['id'];
    $foto_nombre = $_POST['foto_actual']; // Mantenemos la foto vieja por defecto

    // 1. Manejo de Nueva Foto (si se subió)
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $directorio = "imagenes/perfiles/"; 
        
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true); 
        }

        $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];

        if (in_array($extension, $extensiones_permitidas)) {
            $nuevo_nombre_foto = "empleado_" . $id . "_" . time() . "." . $extension;
            
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $directorio . $nuevo_nombre_foto)) {
                if (!empty($foto_nombre) && file_exists($directorio . $foto_nombre)) {
                    unlink($directorio . $foto_nombre);
                }
                $foto_nombre = $nuevo_nombre_foto;
            }
        } else {
            die("Error: Solo se permiten imágenes en formato JPG, JPEG o PNG.");
        }
    }

    // 2. Sanitizar y estandarizar datos (Aplicando MAYÚSCULAS)
    $dpi = trim($_POST['dpi']);
    $nit = !empty($_POST['nit']) ? mb_strtoupper(trim($_POST['nit']), 'UTF-8') : null;
    $nombres = mb_strtoupper(trim($_POST['nombres']), 'UTF-8');
    $apellidos = mb_strtoupper(trim($_POST['apellidos']), 'UTF-8');
    
    $tipo_sangre = !empty($_POST['tipo_sangre']) ? $_POST['tipo_sangre'] : null;
   // ... otros campos ...
    $correo_electronico = !empty($_POST['correo_electronico']) ? trim($_POST['correo_electronico']) : null;
    $direccion = mb_strtoupper(trim($_POST['direccion']), 'UTF-8');
    // AGREGAR ESTO:
    $municipio = mb_strtoupper(trim($_POST['municipio']), 'UTF-8');
    $departamento = mb_strtoupper(trim($_POST['departamento']), 'UTF-8');
    // Contactos de emergencia en Mayúsculas
    $contacto_emergencia_nombre = mb_strtoupper(trim($_POST['contacto_emergencia_nombre']), 'UTF-8');
    $contacto_emergencia_telefono = !empty($_POST['contacto_emergencia_telefono']) ? trim($_POST['contacto_emergencia_telefono']) : null;
    
    $contacto_emergencia_nombre2 = !empty($_POST['contacto_emergencia_nombre2']) 
        ? mb_strtoupper(trim($_POST['contacto_emergencia_nombre2']), 'UTF-8') 
        : null;
    $contacto_emergencia_telefono2 = !empty($_POST['contacto_emergencia_telefono2']) ? trim($_POST['contacto_emergencia_telefono2']) : null;

    try {
        // Consulta SQL sin el campo fecha_ultimo_ascenso
        $sql = "UPDATE empleados SET 
                dpi = ?, nit = ?, nombres = ?, apellidos = ?, fecha_nacimiento = ?, 
                genero = ?, tipo_sangre = ?, telefono = ?, correo_electronico = ?, 
                direccion = ?, municipio = ?, departamento = ?, contacto_emergencia_nombre = ?, contacto_emergencia_telefono = ?, 
                contacto_emergencia_nombre2 = ?, contacto_emergencia_telefono2 = ?, 
                id_renglon = ?, id_area = ?, fecha_inicio_labores = ?, 
                id_puesto_nominal = ?, id_puesto_funcional = ?, 
                foto_perfil = ?, estado = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $dpi, 
            $nit, 
            $nombres, 
            $apellidos, 
            $_POST['fecha_nacimiento'],
            $_POST['genero'], 
            $tipo_sangre, 
            trim($_POST['telefono']), 
            $correo_electronico,
            $direccion, $municipio, $departamento,
            $contacto_emergencia_nombre, 
            $contacto_emergencia_telefono,
            $contacto_emergencia_nombre2, 
            $contacto_emergencia_telefono2,
            $_POST['id_renglon'], 
            $_POST['id_area'], 
            $_POST['fecha_inicio_labores'],
            $_POST['id_puesto_nominal'], 
            $_POST['id_puesto_funcional'],
            $foto_nombre, 
            $_POST['estado'], 
            $id
        ]);

        header("Location: index.php?status=success");
        exit;

    } catch (PDOException $e) {
        die("Error al actualizar: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}
?>
