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
    
    // Obtener usuario activo para auditoría
    $usuario_actual = $_SESSION['usuario_nombre'] ?? 'Sistema';

    // ---------------------------------------------------------
    // LÓGICA DE GUARDADO Y ELIMINADO (BACKEND PROPIO)
    // ---------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        
        // A) Agregar Sanción/Reporte (Se agregó $usuario_actual)
        if ($_POST['action'] == 'add_sancion') {
            $stmt = $pdo->prepare("INSERT INTO empleado_sanciones (empleado_id, tipo, descripcion, fecha, creado_por) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $_POST['tipo'], $_POST['descripcion'], $_POST['fecha'], $usuario_actual]);
            header("Location: perfil_empleado.php?id=$id&tab=reportes");
            exit;
        }

        // B) Eliminar Sanción
        if ($_POST['action'] == 'delete_sancion') {
            $stmt = $pdo->prepare("DELETE FROM empleado_sanciones WHERE id = ?");
            $stmt->execute([$_POST['sancion_id']]);
            header("Location: perfil_empleado.php?id=$id&tab=reportes");
            exit;
        }

        // C) Agregar Asistencia/Vacaciones (Se agregó $usuario_actual)
        if ($_POST['action'] == 'add_asistencia') {
            $stmt = $pdo->prepare("INSERT INTO empleado_asistencia (empleado_id, tipo, fecha_inicio, fecha_fin, comentario, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $_POST['tipo'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['comentario'], $usuario_actual]);
            header("Location: perfil_empleado.php?id=$id&tab=asistencia");
            exit;
        }

        // D) Eliminar Asistencia
        if ($_POST['action'] == 'delete_asistencia') {
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
        // 1. Datos del Empleado
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

        // 2. Datos de Sanciones
        $sanciones = $pdo->prepare("SELECT * FROM empleado_sanciones WHERE empleado_id = ? ORDER BY fecha DESC");
        $sanciones->execute([$id]);
        $lista_sanciones = $sanciones->fetchAll();

        // 3. Datos de Asistencia
        $asistencia = $pdo->prepare("SELECT * FROM empleado_asistencia WHERE empleado_id = ? ORDER BY fecha_inicio DESC");
        $asistencia->execute([$id]);
        $lista_asistencia = $asistencia->fetchAll();

        // 4. Datos de Historial Laboral (Promociones)
        $stmtHist = $pdo->prepare("SELECT hl.*, a.nombre AS area_ant, p.nombre AS puesto_ant 
                                FROM historial_laboral hl
                                LEFT JOIN catalogo_areas a ON hl.area_anterior_id = a.id
                                LEFT JOIN catalogo_cargos p ON hl.puesto_anterior_id = p.id
                                WHERE hl.empleado_id = ? 
                                ORDER BY hl.fecha_fin_puesto DESC");
        $stmtHist->execute([$id]);
        $historial = $stmtHist->fetchAll();

        // 5. Catálogos para el Modal de Promoción
        $renglones = $pdo->query("SELECT * FROM catalogo_renglones ORDER BY codigo ASC")->fetchAll();
        $areas = $pdo->query("SELECT * FROM catalogo_areas ORDER BY nombre ASC")->fetchAll();
        $cargos = $pdo->query("SELECT * FROM catalogo_cargos ORDER BY nombre ASC")->fetchAll();

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
            
            /* --- INICIO CAMBIOS DE FONDO --- */
            .profile-header { 
                background-image: url('imagenes/panel2.jpg');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                border-radius: 0 0 15px 15px; 
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                position: relative;
                color: white; 
            }
            .profile-header::before {
                content: "";
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.6); 
                border-radius: 0 0 15px 15px;
                z-index: 0;
            }
            .profile-header .container {
                position: relative;
                z-index: 1;
            }
            /* --- FIN CAMBIOS DE FONDO --- */

            .avatar-lg { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background-color: white;}
            .avatar-initials { width: 120px; height: 120px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; border: 4px solid white; }
            .nav-tabs .nav-link.active { border-bottom: 3px solid #0d6efd; color: #0d6efd; font-weight: 600; }
            .nav-tabs .nav-link { color: #6c757d; border: none; }
            .card-custom { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
            .info-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #adb5bd; font-weight: 600; }
            .info-value { font-size: 1rem; color: #212529; font-weight: 500; }
            
            .btn-outline-light-custom { color: white; border-color: rgba(255,255,255,0.5); }
            .btn-outline-light-custom:hover { background-color: rgba(255,255,255,0.1); color: white; border-color: white; }
        </style>
    </head>
    <body>

    <div class="profile-header pt-5 pb-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between mb-3">
                <a href="index.php" class="btn btn-outline-light-custom btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
                <a href="editar_empleado.php?id=<?= $emp['id'] ?>" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i> Editar Datos</a>
            </div>
            
            <div class="row align-items-center">
                <div class="col-md-auto text-center text-md-start mb-3 mb-md-0 position-relative">
                    
                    <?php if (!empty($emp['foto_perfil']) && file_exists("imagenes/perfiles/" . $emp['foto_perfil'])): ?>
                        <img src="imagenes/perfiles/<?= $emp['foto_perfil'] ?>" class="avatar-lg">
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
                    <h2 class="fw-bold mb-0 text-white"><?= $emp['nombres'] ?> <?= $emp['apellidos'] ?></h2>
                    <div class="text-light mb-2 opacity-75"><?= $emp['puesto_fun'] ?></div>
                    
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
                        <span class="badge <?= $statusClass ?> border border-white"><?= $emp['estado'] ?></span>
                    </div>
                </div>
                
                <div class="col-md-3 mt-3 mt-md-0">
                    <div class="card bg-white border-0 p-3 shadow-sm text-dark h-100 d-flex flex-column justify-content-center">
                        <small class="text-danger fw-bold text-uppercase mb-2"><i class="bi bi-heart-pulse"></i> Contactos de Emergencia</small>
                        
                        <div>
                            <div class="fw-bold small text-truncate" title="<?= htmlspecialchars($emp['contacto_emergencia_nombre']) ?>">
                                <i class="bi bi-person-fill text-muted"></i> <?= $emp['contacto_emergencia_nombre'] ?: 'No registrado' ?>
                            </div>
                            <div class="text-muted small ps-3">
                                <i class="bi bi-telephone-fill"></i> <?= $emp['contacto_emergencia_telefono'] ?: '--' ?>
                            </div>
                        </div>

                        <?php if (!empty($emp['contacto_emergencia_nombre2']) || !empty($emp['contacto_emergencia_telefono2'])): ?>
                        <div class="border-top pt-2 mt-2">
                            <div class="fw-bold small text-truncate" title="<?= htmlspecialchars($emp['contacto_emergencia_nombre2']) ?>">
                                <i class="bi bi-person-fill text-muted"></i> <?= $emp['contacto_emergencia_nombre2'] ?: 'No registrado' ?>
                            </div>
                            <div class="text-muted small ps-3">
                                <i class="bi bi-telephone-fill"></i> <?= $emp['contacto_emergencia_telefono2'] ?: '--' ?>
                            </div>
                        </div>
                        <?php endif; ?>

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
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab == 'trayectoria' ? 'active' : '' ?>" id="trayectoria-tab" data-bs-toggle="tab" data-bs-target="#trayectoria" type="button">📈 Trayectoria Laboral</button>
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
                                    <div class="info-label">Ingreso al Hospital</div>
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
                                    <th>Registrado por</th>
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
                                    <td><small class="text-muted"><?= $s['creado_por'] ?: 'Sistema' ?></small></td>
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
                                    <tr><td colspan="5" class="text-center text-muted py-4">No hay reportes registrados.</td></tr>
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
                                    <th>Registrado por</th>
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
                                    <td><small class="text-muted"><?= $a['creado_por'] ?: 'Sistema' ?></small></td>
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
                                    <tr><td colspan="6" class="text-center text-muted py-4">No hay registros de asistencia.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?= $activeTab == 'trayectoria' ? 'show active' : '' ?>" id="trayectoria">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-primary mb-0">Historial de Promociones y Cambios</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPromocion">
                            <i class="bi bi-graph-up-arrow"></i> Promover / Cambiar Puesto
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Periodo</th>
                                    <th>Puesto Anterior</th>
                                    <th>Área Anterior</th>
                                    <th>Motivo del Cambio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($historial) > 0): ?>
                                    <?php foreach($historial as $h): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted d-block">Desde: <?= date('d/m/Y', strtotime($h['fecha_inicio_puesto'])) ?></small>
                                            <strong>Hasta: <?= date('d/m/Y', strtotime($h['fecha_fin_puesto'])) ?></strong>
                                        </td>
                                        <td><?= $h['puesto_ant'] ?></td>
                                        <td><?= $h['area_ant'] ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= $h['motivo_cambio'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay registros de cambios de puesto.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div> <div class="modal fade" id="modalSancion" tabindex="-1">
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
                            <select class="form-select" name="tipo" id="tipoMovimiento" required>
                                <option value="Vacaciones">Vacaciones</option>
                                <option value="Día Libre">Día Libre</option>
                                <option value="Descanso">Día de Descanso (Turno)</option>
                                <option value="Cambio de Turno">Cambio de Turno</option>
                                <option value="Incapacidad">Incapacidad Médica (IGSS)</option>
                                <option value="Permiso con Goce">Permiso con Goce de Sueldo</option>
                                <option value="Permiso sin Goce">Permiso sin Goce de Sueldo</option>
                                <option value="Falta Injustificada">Falta Injustificada</option>
                                <option value="Reposicion de tiempo">Reposición de tiempo</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3" id="colDesde">
                                <label class="form-label" id="labelDesde">Desde</label>
                                <input type="date" class="form-control" name="fecha_inicio" id="fechaInicio" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6 mb-3" id="colHasta">
                                <label class="form-label">Hasta</label>
                                <input type="date" class="form-control" name="fecha_fin" id="fechaFin" value="<?= date('Y-m-d') ?>" required>
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

    <div class="modal fade" id="modalPromocion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-graph-up-arrow"></i> Promover / Cambiar Puesto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_promocion.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="empleado_id" value="<?= $emp['id'] ?>">
                        <input type="hidden" name="area_actual" value="<?= $emp['id_area'] ?>">
                        <input type="hidden" name="renglon_actual" value="<?= $emp['id_renglon'] ?>">
                        <input type="hidden" name="puesto_funcional_actual" value="<?= $emp['id_puesto_funcional'] ?>">
                        <input type="hidden" name="fecha_inicio_actual" value="<?= $emp['fecha_ultimo_ascenso'] ?? $emp['fecha_inicio_labores'] ?>">

                        <div class="alert alert-info">
                            <strong>Puesto actual:</strong> <?= $emp['puesto_fun'] ?> en <?= $emp['area'] ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nueva Fecha de Ascenso/Cambio</label>
                                <input type="date" class="form-control" name="fecha_nuevo_ascenso" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Motivo del Cambio</label>
                                <input type="text" class="form-control" name="motivo_cambio" placeholder="Ej: Ascenso, Reubicación" required oninput="this.value = this.value.toUpperCase()">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nuevo Renglón</label>
                                <select class="form-select" name="nuevo_renglon" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($renglones as $r) echo "<option value='{$r['id']}'>{$r['codigo']} - {$r['descripcion']}</option>"; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nueva Área</label>
                                <select class="form-select" name="nueva_area" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($areas as $a) echo "<option value='{$a['id']}'>{$a['nombre']}</option>"; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Nuevo Puesto Funcional</label>
                                <select class="form-select" name="nuevo_puesto_funcional" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($cargos as $c) echo "<option value='{$c['id']}'>{$c['nombre']}</option>"; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Aplicar Promoción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectTipo = document.getElementById('tipoMovimiento');
        const inputInicio = document.getElementById('fechaInicio');
        const inputFin = document.getElementById('fechaFin');
        const colDesde = document.getElementById('colDesde');
        const colHasta = document.getElementById('colHasta');
        const labelDesde = document.getElementById('labelDesde');
        // Función que evalúa qué mostrar según el tipo de movimiento
        function evaluarFechas() {
            const tipo = selectTipo.value;
            // Movimientos que son de 1 solo día
            const deUnDia = ['Día Libre', 'Descanso', 'Reposicion de tiempo'];
            if (deUnDia.includes(tipo)) {
                // Ocultar "Hasta"
                colHasta.style.display = 'none';
                // Hacer que "Desde" ocupe todo el ancho
                colDesde.classList.remove('col-6');
                colDesde.classList.add('col-12');
                labelDesde.textContent = 'Fecha del evento';
                // Sincronizar automáticamente la fecha final con la inicial
                inputFin.value = inputInicio.value;
            } else {
                // Mostrar "Hasta"
                colHasta.style.display = 'block';
                // Regresar a mitad de ancho
                colDesde.classList.remove('col-12');
                colDesde.classList.add('col-6');
                labelDesde.textContent = 'Desde';
            }
        }
        // Ejecutar cuando se cambie el selector
        selectTipo.addEventListener('change', evaluarFechas);
        // Ejecutar cuando se cambie la fecha de inicio (para mantener sincronizado "Hasta" si está oculto)
        inputInicio.addEventListener('change', function() {
            const tipo = selectTipo.value;
            const deUnDia = ['Día Libre', 'Descanso', 'Reposicion de tiempo'];
            if (deUnDia.includes(tipo)) {
                inputFin.value = inputInicio.value;
            }
        });
        // Ejecutar al abrir la página por primera vez
        evaluarFechas();
    });
</script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>