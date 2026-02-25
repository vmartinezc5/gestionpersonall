<?php
// estadisticas.php
require_once 'seguridad.php';
require_once 'config/db.php';

try {
    // 1. Datos para Gráfica: Empleados Activos por Área
    $sqlAreas = "SELECT a.nombre AS area, COUNT(e.id) AS total 
                 FROM empleados e 
                 JOIN catalogo_areas a ON e.id_area = a.id 
                 WHERE e.estado = 'Activo'
                 GROUP BY a.id";
    $stmtAreas = $pdo->query($sqlAreas);
    
    $areas_labels = [];
    $areas_data = [];
    foreach ($stmtAreas as $row) {
        $areas_labels[] = $row['area'];
        $areas_data[] = $row['total'];
    }

    // 2. Datos para Gráfica: Estado General de Empleados
    $sqlEstados = "SELECT estado, COUNT(id) AS total FROM empleados GROUP BY estado";
    $stmtEstados = $pdo->query($sqlEstados);
    
    $estados_labels = [];
    $estados_data = [];
    foreach ($stmtEstados as $row) {
        $estados_labels[] = $row['estado'];
        $estados_data[] = $row['total'];
    }

    // 3. Datos para Gráfica: Distribución por Género (Solo Activos)
    $sqlGenero = "SELECT genero, COUNT(id) AS total FROM empleados WHERE estado = 'Activo' GROUP BY genero";
    $stmtGenero = $pdo->query($sqlGenero);
    
    $genero_labels = [];
    $genero_data = [];
    foreach ($stmtGenero as $row) {
        // Convertir 'M' a Masculino y 'F' a Femenino para la gráfica
        $genero_labels[] = ($row['genero'] == 'M') ? 'Masculino' : 'Femenino';
        $genero_data[] = $row['total'];
    }

} catch (PDOException $e) {
    die("Error cargando estadísticas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas | Sistema RRHH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-people-fill me-2"></i>RRHH Manager</a>
    <div class="d-flex">
        <a href="index.php" class="btn btn-sm btn-outline-light me-2">
            <i class="bi bi-house-door"></i> Inicio
        </a>
        <a href="logout.php" class="btn btn-sm btn-danger">
            <i class="bi bi-box-arrow-right"></i> Salir
        </a>
    </div>
  </div>
</nav>

<div class="container pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Panel de Estadísticas</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimir Reporte</button>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-stat p-4 h-100">
                <h5 class="text-muted fw-bold mb-4">Personal Activo por Área</h5>
                <canvas id="graficaAreas" style="max-height: 350px;"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-stat p-4 h-100">
                <h5 class="text-muted fw-bold mb-4">Estado General del Personal</h5>
                <canvas id="graficaEstados" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-stat p-4 h-100">
                <h5 class="text-muted fw-bold mb-4">Distribución por Género (Activos)</h5>
                <canvas id="graficaGenero" style="max-height: 300px;"></canvas>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card-stat p-4 h-100 d-flex flex-column justify-content-center">
                <h5 class="text-muted fw-bold mb-4">Resumen del Sistema</h5>
                <div class="row text-center">
                    <div class="col-4 border-end">
                        <h2 class="fw-bold text-primary"><?= array_sum($areas_data) ?></h2>
                        <span class="text-muted small text-uppercase">Total Activos</span>
                    </div>
                    <div class="col-4 border-end">
                        <h2 class="fw-bold text-success"><?= count($areas_labels) ?></h2>
                        <span class="text-muted small text-uppercase">Áreas Registradas</span>
                    </div>
                    <div class="col-4">
                        <h2 class="fw-bold text-info"><?= date('d/m/Y') ?></h2>
                        <span class="text-muted small text-uppercase">Fecha de Corte</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Configuración general para que las gráficas se vean modernas
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';

    // 1. Gráfica de Áreas (Barras)
    new Chart(document.getElementById('graficaAreas'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($areas_labels) ?>,
            datasets: [{
                label: 'Cantidad de Empleados',
                data: <?= json_encode($areas_data) ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.8)', // Azul primary
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // 2. Gráfica de Estados (Pastel)
    new Chart(document.getElementById('graficaEstados'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($estados_labels) ?>,
            datasets: [{
                data: <?= json_encode($estados_data) ?>,
                backgroundColor: [
                    '#198754', // Activo - Verde
                    '#dc3545', // Baja - Rojo
                    '#ffc107', // Vacaciones - Amarillo
                    '#fd7e14'  // Suspendido - Naranja
                ]
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 3. Gráfica de Género (Dona)
    new Chart(document.getElementById('graficaGenero'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($genero_labels) ?>,
            datasets: [{
                data: <?= json_encode($genero_data) ?>,
                backgroundColor: [
                    '#0dcaf0', // Masculino - Cian
                    '#d63384'  // Femenino - Rosa
                ]
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>

</body>
</html>