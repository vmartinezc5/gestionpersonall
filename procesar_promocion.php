<?php
// procesar_promocion.php
session_start();
require_once 'seguridad.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $empleado_id = $_POST['empleado_id'];
    $usuario_actual = $_SESSION['usuario_id'] ?? 1; // ID del usuario logueado

    // Sanitización y estandarización a mayúsculas
    $motivo_cambio = mb_strtoupper(trim($_POST['motivo_cambio']), 'UTF-8');

    try {
        // INICIAMOS LA TRANSACCIÓN
        // Si falla un paso, no se guarda ninguno (evita datos corruptos)
        $pdo->beginTransaction();

        // 1. INSERTAR EL HISTORIAL (Los datos viejos)
        $sqlHistorial = "INSERT INTO historial_laboral (
                            empleado_id, puesto_anterior_id, renglon_anterior_id, area_anterior_id, 
                            fecha_inicio_puesto, fecha_fin_puesto, motivo_cambio, usuario_que_registro
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtHistorial = $pdo->prepare($sqlHistorial);
        $stmtHistorial->execute([
            $empleado_id, 
            $_POST['puesto_funcional_actual'], 
            $_POST['renglon_actual'], 
            $_POST['area_actual'],
            $_POST['fecha_inicio_actual'],
            $_POST['fecha_nuevo_ascenso'], // El día que termina el viejo, es el día que empieza el nuevo
            $motivo_cambio,
            $usuario_actual
        ]);

        // 2. ACTUALIZAR AL EMPLEADO (Los datos nuevos)
        $sqlActualizar = "UPDATE empleados SET 
                            id_renglon = ?, 
                            id_area = ?, 
                            id_puesto_funcional = ?, 
                            fecha_ultimo_ascenso = ? 
                          WHERE id = ?";
        
        $stmtActualizar = $pdo->prepare($sqlActualizar);
        $stmtActualizar->execute([
            $_POST['nuevo_renglon'],
            $_POST['nueva_area'],
            $_POST['nuevo_puesto_funcional'],
            $_POST['fecha_nuevo_ascenso'],
            $empleado_id
        ]);

        // SI TODO SALIÓ BIEN, APLICAMOS LOS CAMBIOS
        $pdo->commit();

        // Regresar al perfil a la pestaña de trayectoria
        header("Location: perfil_empleado.php?id=$empleado_id&tab=trayectoria");
        exit;

    } catch (Exception $e) {
        // Si algo falla, deshacemos todo
        $pdo->rollBack();
        die("Error al procesar la promoción: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>