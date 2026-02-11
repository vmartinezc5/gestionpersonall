<?php
// perfil_empleado.php
require_once 'seguridad.php';
require_once 'config/db.php';

// 1. Validar ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = $_GET['id'];

// ---------------------------------------------------------
// LÓGICA DE GUARDADO Y ELIMINADO (BACKEND)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A) Agregar Sanción/Reporte
    if (isset($_POST['action']) && $_POST['action'] == 'add_sancion') {
        $stmt = $pdo->prepare("INSERT INTO empleado_sanciones (empleado_id, tipo, descripcion, fecha) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $_POST['tipo'], $_POST['descripcion'], $_POST['fecha']]);
        header("Location: perfil_empleado.php?id=$id&tab=reportes");
        exit;
    }

    // B) Eliminar Sanción
    if (isset($_POST['action']) && $_POST['action'] == 'delete_sancion') {
        $stmt = $pdo->prepare("DELETE FROM empleado_sanciones WHERE id = ?");
        $stmt->execute([$_POST['sancion_id']]);
        header("Location: perfil_empleado.php?id=$id&tab=reportes");
        exit;
    }

    // C) Agregar Asistencia/Vacaciones
    if (isset($_POST['action']) && $_POST['action'] == 'add_asistencia') {
        $stmt = $pdo->prepare("INSERT INTO empleado_asistencia (empleado_id, tipo, fecha_inicio, fecha_fin, comentario) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $_POST['tipo'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['comentario']]);
        header("Location: perfil_empleado.php?id=$id&tab=asistencia");
        exit;
    }

    // D) Eliminar Asistencia
    if (isset($_POST['action']) && $_POST['action'] == 'delete_asistencia') {
        $stmt = $pdo->prepare("DELETE FROM empleado_asistencia WHERE id = ?");
        $stmt->execute([$_POST['asistencia_id']]);
        header("Location: perfil_empleado.php?id=$id&tab=asistencia");
        exit;
    }
}

// ---------------------------------------------------------
// CONSULTAS DE DATOS
// ---------------------------------------------------------
try {
    // Datos del Empleado
    $sql = "SELECT e.*, a.nombre as area, pn.nombre as puesto_nom, cf.nombre as puesto_fun, r.codigo as renglon 
            FROM empleados e 
            JOIN catalogo_areas a ON e.id_area = a.id
            JOIN catalogo_puestos_nominales pn ON e.id_puesto_nominal = pn.id
            JOIN catalogo_cargos cf ON e.id_puesto_funcional = cf.id
            JOIN catalogo_renglones r ON e.id_renglon = r.id
            WHERE e.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $emp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emp) die("Empleado no encontrado");

    // Datos de Sanciones
    $sanciones = $pdo->prepare("SELECT * FROM empleado_sanciones WHERE empleado_id = ? ORDER BY fecha DESC");
    $sanciones->execute([$id]);
    $lista_sanciones = $sanciones->fetchAll();

    // Datos de Asistencia
    $asistencia = $pdo->prepare("SELECT * FROM empleado_asistencia WHERE empleado_id = ? ORDER BY fecha_inicio DESC");
    $asistencia->execute([$id]);
    $lista_asistencia = $asistencia->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Determinar tab activa
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'ficha';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= $emp['nombres'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .profile-header { background: white; border-radius: 0 0 15px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .avatar-lg { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .avatar-initials { width: 120px; height: 120px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; border: 4px solid white; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #0d6efd; color: #0d6efd; font-weight: 600; }
        .nav-tabs .nav-link { color: #6c757d; border: none; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        .info-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #adb5bd; font-weight: 600; }
        .info-value { font-size: 1rem; color: #212529; font-weight: 500; }
        .blood-type { position: absolute; top: 10px; right: 10px; background: #ffebee; color: #c62828; padding: 5px 10px; border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>

<div class="profile-header pt-5 pb-3 mb-4">
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
             <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
             <a href="editar_empleado.php?id=<?= $emp['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Editar Datos</a>
        </div>
        
        <div class="row align-items-center">
            <div class="col-md-auto text-center text-md-start mb-3 mb-md-0 position-relative">
                <?php if (!empty($emp['foto_perfil']) && file_exists("uploads/" . $emp['foto_perfil'])): ?>
                    <img src="uploads/<?= $emp['foto_perfil'] ?>" class="avatar-lg">
                <?php else: ?>
                    <div class="avatar-initials mx-auto">
                        <?= strtoupper(substr($emp['nombres'], 0, 1) . substr($emp['apellidos'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <?php if($emp['tipo_sangre']): ?>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle border border-light">
                        <i class="bi bi-droplet-fill"></i> <?= $emp['tipo_sangre'] ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="col-md">
                <h2 class="fw-bold mb-0"><?= $emp['nombres'] ?> <?= $emp['apellidos'] ?></h2>
                <div class="text-muted mb-2"><?= $emp['puesto_fun'] ?></div>
                
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark border"><i class="bi bi-building"></i> <?= $emp['area'] ?></span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-upc-scan"></i> DPI: <?= $emp['dpi'] ?></span>
                    <?php 
                        $statusClass = match($emp['estado']) {
                            'Activo' => 'bg-success',
                            'Suspendido' => 'bg-warning text-dark',
                            'Baja' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= $emp['estado'] ?></span>
                </div>
            </div>
            
            <div class="col-md-3 mt-3 mt-md-0">
                <div class="card bg-light border-0 p-2">
                    <small class="text-danger fw-bold text-uppercase mb-1"><i class="bi bi-heart-pulse"></i> Emergencia</small>
                    <div class="fw-bold text-dark small"><?= $emp['contacto_emergencia_nombre'] ?: 'No registrado' ?></div>
                    <div class="text-muted small"><i class="bi bi-telephone"></i> <?= $emp['contacto_emergencia_telefono'] ?: '--' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">

    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab == 'ficha' ? 'active' : '' ?>" id="ficha-tab" data-bs-toggle="tab" data-bs-target="#ficha" type="button">📝 Ficha Técnica</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab == 'reportes' ? 'active' : '' ?>" id="reportes-tab" data-bs-toggle="tab" data-bs-target="#reportes" type="button">⚠️ Reportes y Sanciones</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab == 'asistencia' ? 'active' : '' ?>" id="asistencia-tab" data-bs-toggle="tab" data-bs-target="#asistencia" type="button">📅 Vacaciones y Asistencia</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        
        <div class="tab-pane fade <?= $activeTab == 'ficha' ? 'show active' : '' ?>" id="ficha">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card card-custom h-100 p-4">
                        <h5 class="mb-4 text-primary"><i class="bi bi-person-lines-fill"></i> Información Personal</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-label">Fecha Nacimiento</div>
                                <div class="info-value"><?= date('d/m/Y', strtotime($emp['fecha_nacimiento'])) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Género</div>
                                <div class="info-value"><?= $emp['genero'] == 'M' ? 'Masculino' : 'Femenino' ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Teléfono</div>
                                <div class="info-value"><?= $emp['telefono'] ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">NIT</div>
                                <div class="info-value"><?= $emp['nit'] ?: 'N/A' ?></div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Dirección</div>
                                <div class="info-value"><?= $emp['direccion'] ?></div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Correo Electrónico</div>
                                <div class="info-value"><?= $emp['correo_electronico'] ?: 'No registrado' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom h-100 p-4">
                        <h5 class="mb-4 text-primary"><i class="bi bi-building-check"></i> Información Laboral</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="info-label">Renglón</div>
                                <div class="info-value"><?= $emp['renglon'] ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Inicio Labores</div>
                                <div class="info-value"><?= date('d/m/Y', strtotime($emp['fecha_inicio_labores'])) ?></div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Puesto Nominal (Contrato)</div>
                                <div class="info-value"><?= $emp['puesto_nom'] ?></div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Puesto Funcional (Real)</div>
                                <div class="info-value"><?= $emp['puesto_fun'] ?></div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Antigüedad</div>
                                <?php 
                                    $fecha_inicio = new DateTime($emp['fecha_inicio_labores']);
                                    $hoy = new DateTime();
                                    $antiguedad = $fecha_inicio->diff($hoy);
                                ?>
                                <div class="info-value"><?= $antiguedad->y ?> años, <?= $antiguedad->m ?> meses</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?= $activeTab == 'reportes' ? 'show active' : '' ?>" id="reportes">
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-danger mb-0">Historial Disciplinario</h5>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalSancion">
                        <i class="bi bi-plus-circle"></i> Agregar Reporte
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripción / Motivo</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista_sanciones as $s): ?>
                            <tr>
                                <td style="width: 150px;"><?= date('d/m/Y', strtotime($s['fecha'])) ?></td>
                                <td style="width: 180px;">
                                    <span class="badge bg-light text-dark border"><?= $s['tipo'] ?></span>
                                </td>
                                <td><?= nl2br($s['descripcion']) ?></td>
                                <td class="text-end">
                                    <form method="POST" onsubmit="return confirm('¿Eliminar este reporte permanentemente?');">
                                        <input type="hidden" name="action" value="delete_sancion">
                                        <input type="hidden" name="sancion_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($lista_sanciones) == 0): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No hay reportes registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?= $activeTab == 'asistencia' ? 'show active' : '' ?>" id="asistencia">
             <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-success mb-0">Control de Asistencia y Vacaciones</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsistencia">
                        <i class="bi bi-calendar-plus"></i> Registrar Movimiento
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fechas</th>
                                <th>Días</th>
                                <th>Tipo de Movimiento</th>
                                <th>Comentarios</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista_asistencia as $a): ?>
                            <tr>
                                <td style="width: 200px;">
                                    <i class="bi bi-calendar-event text-muted me-1"></i>
                                    <?= date('d/m/Y', strtotime($a['fecha_inicio'])) ?> 
                                    <?php if($a['fecha_inicio'] != $a['fecha_fin']): ?>
                                        <br><span class="text-muted ms-3">al <?= date('d/m/Y', strtotime($a['fecha_fin'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $d1 = new DateTime($a['fecha_inicio']);
                                        $d2 = new DateTime($a['fecha_fin']);
                                        echo $d1->diff($d2)->days + 1;
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $tipoClass = match($a['tipo']) {
                                            'Vacaciones' => 'bg-info text-dark',
                                            'Falta Injustificada' => 'bg-danger',
                                            'Cambio de Turno' => 'bg-warning text-dark',
                                            'Incapacidad' => 'bg-secondary',
                                            default => 'bg-primary'
                                        };
                                    ?>
                                    <span class="badge <?= $tipoClass ?>"><?= $a['tipo'] ?></span>
                                </td>
                                <td><?= $a['comentario'] ?></td>
                                <td class="text-end">
                                    <form method="POST" onsubmit="return confirm('¿Eliminar este registro?');">
                                        <input type="hidden" name="action" value="delete_asistencia">
                                        <input type="hidden" name="asistencia_id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($lista_asistencia) == 0): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay registros de asistencia.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalSancion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Reporte / Sanción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_sancion">
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" required>
                            <option value="Llamada de Atención">Llamada de Atención</option>
                            <option value="Reporte">Reporte Disciplinario</option>
                            <option value="Acta Administrativa">Acta Administrativa</option>
                            <option value="Sanción">Sanción (Suspensión)</option>
                            <option value="Felicitación">Felicitación / Reconocimiento</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3" required placeholder="Detalle la situación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsistencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_asistencia">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" name="tipo" required>
                            <option value="Vacaciones">Vacaciones</option>
                            <option value="Día Libre">Día Libre</option>
                            <option value="Descanso">Día de Descanso (Turno)</option>
                            <option value="Cambio de Turno">Cambio de Turno</option>
                            <option value="Incapacidad">Incapacidad Médica (IGSS)</option>
                            <option value="Permiso con Goce">Permiso con Goce de Sueldo</option>
                            <option value="Permiso sin Goce">Permiso sin Goce de Sueldo</option>
                            <option value="Falta Injustificada">Falta Injustificada</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Desde</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Hasta</label>
                            <input type="date" class="form-control" name="fecha_fin" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comentarios / Observaciones</label>
                        <textarea class="form-control" name="comentario" rows="2" placeholder="Ej: Cambio de turno con compañero X..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>