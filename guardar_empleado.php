<?php
// guardar_empleado.php
session_start(); // 1. IMPORTANTE: Iniciar sesión para obtener el usuario
require_once 'seguridad.php';
require_once 'config/db.php';

// Verificar si la petición es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crear_empleado.php');
    exit;
}

try {
    // ---------------------------------------------------------
    // 1. RECOLECCIÓN DE DATOS DEL FORMULARIO
    // ---------------------------------------------------------
    
    // Datos Personales
    $dpi = trim($_POST['dpi']);
    $nombres = mb_strtoupper(trim($_POST['nombres']), 'UTF-8');
    $apellidos = mb_strtoupper(trim($_POST['apellidos']), 'UTF-8');
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $tipo_sangre = !empty($_POST['tipo_sangre']) ? $_POST['tipo_sangre'] : null;
    $nit = !empty($_POST['nit']) ? trim($_POST['nit']) : null;
    
    // Datos de Contacto y Ubicación
    $telefono = trim($_POST['telefono']);
    $correo = !empty($_POST['correo_electronico']) ? trim($_POST['correo_electronico']) : null; 
    $direccion = trim($_POST['direccion']);
    $departamento = !empty($_POST['departamento']) ? mb_strtoupper(trim($_POST['departamento']), 'UTF-8') : null;
    $municipio = !empty($_POST['municipio']) ? mb_strtoupper(trim($_POST['municipio']), 'UTF-8') : null;
    
    // Contactos de Emergencia
    $contacto1_nom = !empty($_POST['contacto_emergencia_nombre']) ? mb_strtoupper(trim($_POST['contacto_emergencia_nombre']), 'UTF-8') : null;
    $contacto1_tel = !empty($_POST['contacto_emergencia_telefono']) ? trim($_POST['contacto_emergencia_telefono']) : null;
    $contacto2_nom = !empty($_POST['contacto_emergencia_nombre2']) ? mb_strtoupper(trim($_POST['contacto_emergencia_nombre2']), 'UTF-8') : null;
    $contacto2_tel = !empty($_POST['contacto_emergencia_telefono2']) ? trim($_POST['contacto_emergencia_telefono2']) : null;

    // Datos Laborales
    $fecha_inicio = $_POST['fecha_inicio_labores'];
    $id_renglon = $_POST['id_renglon'];
    $id_area = $_POST['id_area'];
    $id_puesto_nominal = $_POST['id_puesto_nominal'];
    $id_puesto_funcional = $_POST['id_puesto_funcional'];

    // ---------------------------------------------------------
    // 2. LÓGICA DE AUDITORÍA (Usuario Creador)
    // ---------------------------------------------------------
    $creado_por = $_SESSION['usuario_nombre'] ?? 'Sistema';

    // ---------------------------------------------------------
    // 4. VALIDACIONES INICIALES
    // ---------------------------------------------------------
    if (strlen($dpi) !== 13) {
        throw new Exception("El DPI debe tener exactamente 13 dígitos.");
    }

    // ---------------------------------------------------------
    // 3. MANEJO DE LA FOTO DE PERFIL (CORREGIDO)
    // ---------------------------------------------------------
    $nombre_foto = null;
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        // CORRECCIÓN 1: Cambiamos la ruta a la carpeta correcta
        $directorio = "imagenes/perfiles/"; 
        
        // Crear carpeta si no existe
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true); 
        }
        
        $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension); // Convertir a minúsculas por seguridad
        
        // CORRECCIÓN 2: Validar que sea realmente una imagen
        $extensiones_permitidas = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($extension, $extensiones_permitidas)) {
            throw new Exception("Solo se permiten imágenes en formato JPG, JPEG o PNG.");
        }

        // Nombre único: dpi_timestamp.jpg (ej: 1234567890123_1708452000.jpg)
        $nombre_foto = $dpi . "_" . time() . "." . $extension;
        
        // Mover el archivo
        if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $directorio . $nombre_foto)) {
            throw new Exception("Error al subir la imagen al servidor. Revisa los permisos de la carpeta.");
        }
    }

    // ---------------------------------------------------------
    // 5. INSERT EN BASE DE DATOS
    // ---------------------------------------------------------
    $sql = "INSERT INTO empleados (
        dpi, nombres, apellidos, fecha_nacimiento, genero, tipo_sangre, nit, 
        telefono, correo_electronico, direccion, departamento, municipio,
        contacto_emergencia_nombre, contacto_emergencia_telefono,
        contacto_emergencia_nombre2, contacto_emergencia_telefono2,
        fecha_inicio_labores, id_renglon, id_area, 
        id_puesto_nominal, id_puesto_funcional, 
        foto_perfil, creado_por, created_at
    ) VALUES (
        :dpi, :nombres, :apellidos, :fecha_nacimiento, :genero, :tipo_sangre, :nit, 
        :telefono, :correo, :direccion, :departamento, :municipio,
        :contacto1_nom, :contacto1_tel,
        :contacto2_nom, :contacto2_tel,
        :fecha_inicio, :id_renglon, :id_area, 
        :id_puesto_nominal, :id_puesto_funcional, 
        :foto_perfil, :creado_por, NOW()
    )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':dpi' => $dpi,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':fecha_nacimiento' => $fecha_nacimiento,
        ':genero' => $genero,
        ':tipo_sangre' => $tipo_sangre,
        ':nit' => $nit,
        ':telefono' => $telefono,
        ':correo' => $correo,
        ':direccion' => $direccion,
        ':departamento' => $departamento,
        ':municipio' => $municipio,
        ':contacto1_nom' => $contacto1_nom,
        ':contacto1_tel' => $contacto1_tel,
        ':contacto2_nom' => $contacto2_nom,
        ':contacto2_tel' => $contacto2_tel,
        ':fecha_inicio' => $fecha_inicio,
        ':id_renglon' => $id_renglon,
        ':id_area' => $id_area,
        ':id_puesto_nominal' => $id_puesto_nominal,
        ':id_puesto_funcional' => $id_puesto_funcional,
        ':foto_perfil' => $nombre_foto,
        ':creado_por' => $creado_por
    ]);

    // Redirigir con éxito
    header('Location: index.php?status=success');
    exit;

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { 
        $error = "Error: El DPI $dpi ya está registrado.";
    } else {
        $error = "Error de base de datos: " . $e->getMessage();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error al Guardar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="alert alert-danger shadow" role="alert">
            <h4 class="alert-heading">🚫 No se pudo guardar el empleado</h4>
            <p><?= $error ?></p>
            <hr>
            <div class="d-flex gap-2">
                <button onclick="history.back()" class="btn btn-danger">Volver al Formulario</button>
                <a href="index.php" class="btn btn-outline-danger">Ir al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>