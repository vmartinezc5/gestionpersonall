<?php
// index.php
require_once 'seguridad.php';
require_once 'config/db.php';

$empleados = [];

// --- 1. CARGAR CATÁLOGOS PARA LOS FILTROS ---
try {
    $areas = $pdo->query("SELECT * FROM catalogo_areas ORDER BY nombre ASC")->fetchAll();
    $renglones = $pdo->query("SELECT * FROM catalogo_renglones ORDER BY codigo ASC")->fetchAll();
    $cargos = $pdo->query("SELECT * FROM catalogo_cargos ORDER BY nombre ASC")->fetchAll(); // Opcional
} catch (PDOException $e) {
    die("Error cargando catálogos: " . $e->getMessage());
}

// --- 2. CONSTRUCCIÓN DE LA CONSULTA DINÁMICA ---

// Variables de filtro recibidas por URL (GET)
$filtro_area    = $_GET['f_area'] ?? '';
$filtro_renglon = $_GET['f_renglon'] ?? '';
$filtro_orden   = $_GET['f_orden'] ?? 'reciente'; // Valor por defecto

// Base de la consulta
$sql = "SELECT 
            e.id, e.dpi, e.nombres, e.apellidos, e.telefono, e.estado, e.foto_perfil, e.correo_electronico,
            a.nombre AS area,
            pn.nombre AS puesto_nominal,    
            cf.nombre AS puesto_funcional,  
            r.codigo AS renglon
        FROM empleados e
        INNER JOIN catalogo_areas a ON e.id_area = a.id
        INNER JOIN catalogo_puestos_nominales pn ON e.id_puesto_nominal = pn.id
        INNER JOIN catalogo_cargos cf ON e.id_puesto_funcional = cf.id
        INNER JOIN catalogo_renglones r ON e.id_renglon = r.id";

$params = [];
$condiciones = [];

// A. Aplicar Filtros WHERE si existen
if (!empty($filtro_area)) {
    $condiciones[] = "e.id_area = ?";
    $params[] = $filtro_area;
}

if (!empty($filtro_renglon)) {
    $condiciones[] = "e.id_renglon = ?";
    $params[] = $filtro_renglon;
}

// Unir condiciones
if (count($condiciones) > 0) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

// B. Aplicar Ordenamiento ORDER BY
switch ($filtro_orden) {
    case 'nombre_asc':
        $sql .= " ORDER BY e.nombres ASC, e.apellidos ASC";
        break;
    case 'nombre_desc':
        $sql .= " ORDER BY e.nombres DESC, e.apellidos DESC";
        break;
    case 'antiguos':
        $sql .= " ORDER BY e.fecha_inicio_labores ASC";
        break;
    default: // 'reciente'
        $sql .= " ORDER BY e.created_at DESC";
        break;
}

// --- 3. EJECUTAR CONSULTA ---
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contadores (Calculados sobre los resultados filtrados o globales según prefieras)
    // Nota: Para contadores globales reales, deberías hacer queries aparte sin filtros.
    // Aquí los haremos sobre lo visible para que coincida con la tabla.
    $total_emp = count($empleados);
    $activos = count(array_filter($empleados, fn($e) => $e['estado'] === 'Activo'));
    $bajas = count(array_filter($empleados, fn($e) => $e['estado'] === 'Baja'));

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal | Sistema RRHH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .avatar-circle {
            width: 45px; height: 45px; border-radius: 50%; object-fit: cover;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; color: white; font-size: 1.1rem;
        }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .text-nominal { font-size: 0.85rem; color: #6c757d; }
    </style>
</head>
<body>



    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-people-fill me-2"></i>RRHH Manager</a>
    
    <div class="d-flex">
        <span class="navbar-text text-white me-3 d-none d-md-block">
            Hola, <strong>Jefatura</strong>
        </span>
        <a href="logout.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-right"></i> Salir
        </a>
    </div>
  </div>
</nav>


<div class="container pb-5">

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Operación realizada con éxito.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card card-stat p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Total Empleados</h6>
                        <h3 class="fw-bold mb-0 text-primary"><?= $total_emp ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Activos</h6>
                        <h3 class="fw-bold mb-0 text-success"><?= $activos ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                        <i class="bi bi-person-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase mb-1">Bajas / Inactivos</h6>
                        <h3 class="fw-bold mb-0 text-danger"><?= $bajas ?></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                        <i class="bi bi-person-x fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div class="col-12 col-md-12 mb-2 mb-md-0">

        <div class="card p-3 mb-4 shadow-sm border-0">
            <form method="GET" action="index.php" id="filterForm">
             <div class="row g-2 align-items-center">
                
                  <div class="col-md-3">
                      <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Buscar en pantalla...">
                     </div>
                  </div>

                <div class="col-md-2">
                    <select class="form-select" name="f_area" onchange="this.form.submit()">
                        <option value="">Todas las Áreas</option>
                        <?php foreach($areas as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $filtro_area == $a['id'] ? 'selected' : '' ?>>
                                <?= $a['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="f_renglon" onchange="this.form.submit()">
                        <option value="">Todos Renglones</option>
                        <?php foreach($renglones as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $filtro_renglon == $r['id'] ? 'selected' : '' ?>>
                                <?= $r['codigo'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select" name="f_orden" onchange="this.form.submit()">
                        <option value="reciente" <?= $filtro_orden == 'reciente' ? 'selected' : '' ?>>📅 Más Recientes</option>
                        <option value="nombre_asc" <?= $filtro_orden == 'nombre_asc' ? 'selected' : '' ?>>🔤 Nombre (A-Z)</option>
                        <option value="nombre_desc" <?= $filtro_orden == 'nombre_desc' ? 'selected' : '' ?>>🔤 Nombre (Z-A)</option>
                        <option value="antiguos" <?= $filtro_orden == 'antiguos' ? 'selected' : '' ?>>👴 Antigüedad</option>
                    </select>
                </div>

                <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                    <?php if(!empty($filtro_area) || !empty($filtro_renglon) || $filtro_orden != 'reciente'): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Limpiar Filtros"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                    
                    <a href="crear_empleado.php" class="btn btn-primary text-nowrap">
                        <i class="bi bi-plus-lg"></i> Nuevo Empleado
                    </a>
                </div>

            </div>
          </form>
         </div>
        </div>
    </div>

    <div class="card card-stat overflow-hidden">
        <div class="table-responsive">
            <table class="table mb-0 align-middle" id="employeeTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Empleado</th>
                        <th>Cargos (Funcional / Nominal)</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($empleados) > 0): ?>
                        <?php foreach ($empleados as $emp): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <?php 
                                        // Lógica para mostrar foto o iniciales
                                        $initials = strtoupper(substr($emp['nombres'], 0, 1) . substr($emp['apellidos'], 0, 1));
                                        $colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger', 'bg-secondary'];
                                        $randomColor = $colors[array_rand($colors)];
                                        
                                        if (!empty($emp['foto_perfil']) && file_exists("uploads/" . $emp['foto_perfil'])) {
                                            echo "<img src='uploads/{$emp['foto_perfil']}' class='avatar-circle me-3'>";
                                        } else {
                                            echo "<div class='avatar-circle {$randomColor} me-3'>{$initials}</div>";
                                        }
                                    ?>
                                    <div>
                                        <div class="fw-bold text-dark"><?= $emp['nombres'] ?> <?= $emp['apellidos'] ?></div>
                                        <div class="small text-muted"><i class="bi bi-card-heading me-1"></i><?= $emp['dpi'] ?></div>
                                        <?php if($emp['correo_electronico']): ?>
                                            <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= $emp['correo_electronico'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark"><?= $emp['puesto_funcional'] ?></div> <div class="text-nominal">
                                    <i class="bi bi-briefcase me-1"></i>Ctto: <?= $emp['puesto_nominal'] ?>
                                </div>
                            </td>

                            <td>
                                <div class="text-dark"><?= $emp['area'] ?></div>
                                <span class="badge bg-light text-secondary border mt-1">Renglón <?= $emp['renglon'] ?></span>
                            </td>

                            <td>
                                <?php 
                                    $statusClass = match($emp['estado']) {
                                        'Activo' => 'text-success bg-success',
                                        'Suspendido' => 'text-warning bg-warning',
                                        'Baja' => 'text-danger bg-danger',
                                        default => 'text-secondary bg-secondary'
                                    };
                                ?>
                                <span class="badge bg-opacity-10 <?= $statusClass ?> px-3 py-2 rounded-pill">
                                    <span class="status-dot <?= $statusClass ?>"></span><?= $emp['estado'] ?>
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="editar_empleado.php?id=<?= $emp['id'] ?>"><i class="bi bi-pencil me-2 text-warning"></i>Editar</a></li>
                                        <li><a class="dropdown-item" href="perfil_empleado.php?id=<?= $emp['id'] ?>"><i class="bi bi-person-badge me-2 text-info"></i>Ver Perfil</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?= $emp['id'] ?>)"><i class="bi bi-trash me-2"></i>Eliminar Registro</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    No hay empleados registrados.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Buscador simple en JavaScript
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#employeeTable tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function confirmDelete(id) {
        // Mensaje de advertencia fuerte para evitar accidentes
        if(confirm('⚠️ ¿Estás seguro de ELIMINAR PERMANENTEMENTE este registro?\n\nEsta acción no se puede deshacer y borrará historial, reportes y fotos asociados.')) {
            window.location.href = 'eliminar_empleado.php?id=' + id;
        }
    }
</script>
</script>

</body>
</html>