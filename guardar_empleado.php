<?php
// guardar_empleado.php
require_once 'seguridad.php';
require_once 'config/db.php';

// 1. Verificar si la petición es POST (evitar acceso directo por URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crear_empleado.php');
    exit;
}

try {
    // 2. Recibir datos del formulario
    // Usamos trim() para limpiar espacios vacíos accidentales al inicio o final
    $dpi = trim($_POST['dpi']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $nit = !empty($_POST['nit']) ? trim($_POST['nit']) : null; // Si está vacío, guardar NULL
    $telefono = trim($_POST['telefono']);
    $correo = !empty($_POST['correo']) ? trim($_POST['correo']) : null;
    $direccion = trim($_POST['direccion']);
    
    // Datos laborales
    $fecha_inicio = $_POST['fecha_inicio_labores'];
    $id_renglon = $_POST['id_renglon'];
    $id_area = $_POST['id_area'];
    $id_puesto_nominal = $_POST['id_puesto_nominal'];
    $id_puesto_funcional = $_POST['id_puesto_funcional'];

    // 3. Validaciones básicas de servidor
    if (strlen($dpi) !== 13) {
        throw new Exception("El DPI debe tener exactamente 13 dígitos.");
    }

    // 4. Preparar la consulta SQL (INSERT)
    $sql = "INSERT INTO empleados (
        dpi, nombres, apellidos, fecha_nacimiento, genero, nit, 
        telefono, correo_electronico, direccion, 
        fecha_inicio_labores, id_renglon, id_area, 
        id_puesto_nominal, id_puesto_funcional, created_at
    ) VALUES (
        :dpi, :nombres, :apellidos, :fecha_nacimiento, :genero, :nit, 
        :telefono, :correo, :direccion, 
        :fecha_inicio, :id_renglon, :id_area, 
        :id_puesto_nominal, :id_puesto_funcional, NOW()
    )";

    // 5. Preparar la sentencia PDO
    $stmt = $pdo->prepare($sql);

    // 6. Ejecutar con los parámetros (Binding)
    $stmt->execute([
        ':dpi' => $dpi,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':fecha_nacimiento' => $fecha_nacimiento,
        ':genero' => $genero,
        ':nit' => $nit,
        ':telefono' => $telefono,
        ':correo' => $correo,
        ':direccion' => $direccion,
        ':fecha_inicio' => $fecha_inicio,
        ':id_renglon' => $id_renglon,
        ':id_area' => $id_area,
        ':id_puesto_nominal' => $id_puesto_nominal,
        ':id_puesto_funcional' => $id_puesto_funcional
    ]);

    // 7. Redirigir con éxito
    // Usamos un parámetro GET 'status=success' para mostrar una alerta en la siguiente página
    header('Location: index.php?status=success');
    exit;

} catch (PDOException $e) {
    // Manejo específico de errores de Base de Datos
    if ($e->getCode() == 23000) { // Código 23000 es violación de integridad (ej: DPI duplicado)
        $error = "Error: El DPI $dpi ya está registrado en el sistema.";
    } else {
        $error = "Error de base de datos: " . $e->getMessage();
    }
} catch (Exception $e) {
    // Errores genéricos
    $error = $e->getMessage();
}

// Si hubo error, mostrarlo (en un caso real, podrías redirigir atrás con el error)
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
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">🚫 No se pudo guardar el empleado</h4>
            <p><?= $error ?></p>
            <hr>
            <a href="crear_empleado.php" class="btn btn-danger">Volver al Formulario</a>
        </div>
    </div>
</body>
</html>