<?php
// eliminar_empleado_dev.php
require_once 'seguridad.php';
require_once 'config/db.php';

// ------------------------------------------------------------------
// 1. SEGURIDAD NIVEL DIOS: Solo permitimos el paso a "Administradores"
// ------------------------------------------------------------------
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>🛑 ACCESO DENEGADO: Nivel de privilegios insuficiente.</h2>");
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_empleado = $_GET['id'];

// Obtener datos del empleado para mostrar la advertencia y saber qué foto borrar
$stmt = $pdo->prepare("SELECT nombres, apellidos, foto_perfil, dpi FROM empleados WHERE id = ?");
$stmt->execute([$id_empleado]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$emp) {
    die("<h2 style='text-align:center; margin-top:50px;'>El empleado no existe o ya fue eliminado.</h2>");
}

// ------------------------------------------------------------------
// 2. LÓGICA DE ELIMINACIÓN (Se ejecuta solo si se confirma por POST)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_eliminacion'])) {
    
    try {
        $pdo->beginTransaction();

        // A) Borrar foto de perfil del servidor
        if (!empty($emp['foto_perfil']) && file_exists("imagenes/perfiles/" . $emp['foto_perfil'])) {
            unlink("imagenes/perfiles/" . $emp['foto_perfil']);
        }

        // B) Buscar y borrar los archivos PDF/Docs de los reportes de este empleado
        $stmtReportes = $pdo->prepare("SELECT archivo_adjunto FROM historial_reportes WHERE id_empleado = ? AND archivo_adjunto IS NOT NULL");
        $stmtReportes->execute([$id_empleado]);
        $reportes = $stmtReportes->fetchAll();
        
        foreach ($reportes as $rep) {
            if (file_exists("documentos/reportes/" . $rep['archivo_adjunto'])) {
                unlink("documentos/reportes/" . $rep['archivo_adjunto']);
            }
        }

        // C) Borrar registros huérfanos que NO tienen "ON DELETE CASCADE" en tu base de datos
        $pdo->prepare("DELETE FROM historial_reportes WHERE id_empleado = ?")->execute([$id_empleado]);
        $pdo->prepare("DELETE FROM historial_ausencias WHERE id_empleado = ?")->execute([$id_empleado]);
        $pdo->prepare("DELETE FROM historial_cambios_turno WHERE id_empleado = ?")->execute([$id_empleado]);

        // D) Borrar al empleado (Las tablas con ON DELETE CASCADE como asistencia y sanciones se borrarán solas)
        $pdo->prepare("DELETE FROM empleados WHERE id = ?")->execute([$id_empleado]);

        $pdo->commit();

        // Redirigir al inicio con un mensaje de éxito especial
        header("Location: index.php?status=success&msg=empleado_eliminado");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error crítico al eliminar: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ZONA DE PELIGRO | Borrado Físico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #1a1a1a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: monospace;}
        .danger-box { background-color: #2b0000; border: 2px dashed #ff4d4d; padding: 40px; border-radius: 15px; max-width: 600px; text-align: center;}
    </style>
</head>
<body>

    <div class="danger-box shadow-lg">
        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
        <h2 class="text-danger mt-3 fw-bold">¡ZONA DE PELIGRO!</h2>
        <h4 class="mt-3">Estás a punto de borrar FÍSICAMENTE a:</h4>
        <h3 class="text-warning fw-bold"><?= $emp['nombres'] ?> <?= $emp['apellidos'] ?> (DPI: <?= $emp['dpi'] ?>)</h3>
        
        <div class="alert alert-danger mt-4 text-start small">
            <strong>¿Qué significa esto?</strong><br>
            Esta acción NO se puede deshacer. Se borrará toda su información, su foto de perfil, sus archivos PDF, su historial laboral, asistencias y sanciones de la base de datos.<br><br>
            <i>*Si este empleado renunció o fue despedido, deberías hacer clic en Cancelar, ir a "Editar" y cambiar su estado a "Baja". Utiliza esta herramienta SOLO para borrar registros de prueba o errores graves.</i>
        </div>

        <form method="POST" class="mt-4 d-flex justify-content-center gap-3">
            <a href="perfil_empleado.php?id=<?= $id_empleado ?>" class="btn btn-secondary px-4">⬅️ Cancelar y Volver</a>
            <input type="hidden" name="confirmar_eliminacion" value="1">
            <button type="submit" class="btn btn-danger px-4 fw-bold"><i class="bi bi-trash-fill"></i> SÍ, DESTRUIR REGISTRO</button>
        </form>
    </div>

</body>
</html>