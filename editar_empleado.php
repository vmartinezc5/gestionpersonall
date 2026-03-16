<?php
// editar_empleado.php
require_once 'seguridad.php';
require_once 'config/db.php';

// 1. Validar ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_empleado = $_GET['id'];

try {
    // 2. Obtener datos del empleado
    $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id = ?");
    $stmt->execute([$id_empleado]);
    $emp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$emp) {
        die("Empleado no encontrado.");
    }

    // 3. Cargar Catálogos
    $renglones = $pdo->query("SELECT * FROM catalogo_renglones ORDER BY codigo ASC")->fetchAll();
    $areas = $pdo->query("SELECT * FROM catalogo_areas ORDER BY nombre ASC")->fetchAll();
    $cargos_nominales = $pdo->query("SELECT * FROM catalogo_puestos_nominales ORDER BY nombre ASC")->fetchAll();
    $cargos_funcionales = $pdo->query("SELECT * FROM catalogo_cargos ORDER BY nombre ASC")->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado | Sistema RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f3f4f6; font-family: 'Segoe UI', system-ui, sans-serif; }
        .card { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-radius: 12px; }
        .img-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .section-title { font-size: 0.9rem; font-weight: 700; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; margin-bottom: 20px; margin-top: 10px; }
        /* Forzar visualización en mayúsculas */
        .uppercase-input { text-transform: uppercase; }
    </style>
</head>
<body>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">✏️ Editar Empleado</h2>
            <p class="text-muted">Actualizando información de: <strong><?= htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']) ?></strong></p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <form action="actualizar_empleado.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?= $emp['id'] ?>">
        <input type="hidden" name="foto_actual" value="<?= $emp['foto_perfil'] ?>">

        <div class="row">
            
            <div class="col-lg-3 mb-4">
                <div class="card p-4 text-center sticky-top" style="top: 20px; z-index: 1;">
                    <div class="mb-3 position-relative" id="contenedor-foto">
                        <?php if (!empty($emp['foto_perfil']) && file_exists("imagenes/perfiles/" . $emp['foto_perfil'])): ?>
                            <img src="imagenes/perfiles/<?= $emp['foto_perfil'] ?>" class="img-preview mb-2" id="previewImg">
                        <?php else: ?>
                            <div id="initialsAvatar" class="img-preview d-flex align-items-center justify-content-center bg-secondary text-white mx-auto mb-2" style="font-size: 2rem;">
                                <?= strtoupper(substr($emp['nombres'], 0, 1) . substr($emp['apellidos'], 0, 1)) ?>
                            </div>
                            <img src="" class="img-preview mb-2 d-none" id="previewImg">
                        <?php endif; ?>
                    </div>
                    
                    <label class="btn btn-sm btn-outline-primary mb-3">
                        <i class="bi bi-camera"></i> Cambiar Foto
                        <input type="file" name="foto_perfil" id="foto_perfil" hidden accept="image/*" onchange="previewImage(this)">
                    </label>

                    <hr>

                    <div class="form-floating mb-2">
                        <select class="form-select fw-bold" id="estado" name="estado" required>
                            <option value="Activo" <?= $emp['estado'] == 'Activo' ? 'selected' : '' ?>>🟢 Activo</option>
                            <option value="Vacaciones" <?= $emp['estado'] == 'Vacaciones' ? 'selected' : '' ?>>🏖️ Vacaciones</option>
                            <option value="Suspendido" <?= $emp['estado'] == 'Suspendido' ? 'selected' : '' ?>>🟠 Suspendido</option>
                            <option value="Baja" <?= $emp['estado'] == 'Baja' ? 'selected' : '' ?>>🔴 Baja</option>
                        </select>
                        <label for="estado">Estado Actual</label>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card p-4">
                    
                    <div class="section-title text-primary">1. Información Personal</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">DPI (CUI)</label>
                            <input type="text" class="form-control" name="dpi" value="<?= $emp['dpi'] ?>" required pattern="\d{13}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">NIT</label>
                            <input type="text" class="form-control uppercase-input" name="nit" value="<?= $emp['nit'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nombres</label>
                            <input type="text" class="form-control uppercase-input" name="nombres" value="<?= $emp['nombres'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Apellidos</label>
                            <input type="text" class="form-control uppercase-input" name="apellidos" value="<?= $emp['apellidos'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Fecha Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" value="<?= $emp['fecha_nacimiento'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Género</label>
                            <select class="form-select" name="genero" required>
                                <option value="M" <?= $emp['genero'] == 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= $emp['genero'] == 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Tipo Sangre</label>
                            <select class="form-select" name="tipo_sangre">
                                <?php 
                                $tipos = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
                                foreach($tipos as $t): ?>
                                    <option value="<?= $t ?>" <?= $emp['tipo_sangre'] == $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-title text-primary mt-4">2. Contacto y Ubicación</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" value="<?= $emp['telefono'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo_electronico" value="<?= $emp['correo_electronico'] ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Dirección Completa</label>
                            <input type="text" class="form-control uppercase-input" name="direccion" value="<?= $emp['direccion'] ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Municipio</label>
                            <input type="text" class="form-control uppercase-input" name="municipio" value="<?= $emp['municipio'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Departamento</label>
                            <input type="text" class="form-control uppercase-input" name="departamento" value="<?= $emp['departamento'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="section-title text-danger mt-4"><i class="bi bi-heart-pulse me-1"></i> Contactos de Emergencia</div>
                    <div class="row g-3 bg-light p-3 rounded border">
                        <div class="col-12 mb-0">
                            <h6 class="text-muted small text-uppercase fw-bold m-0">Contacto Principal</h6>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label text-muted small">Nombre del Contacto 1</label>
                            <input type="text" class="form-control uppercase-input" name="contacto_emergencia_nombre" value="<?= $emp['contacto_emergencia_nombre'] ?>">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label text-muted small">Teléfono del Contacto 1</label>
                            <input type="tel" class="form-control" name="contacto_emergencia_telefono" value="<?= $emp['contacto_emergencia_telefono'] ?>">
                        </div>

                        <div class="col-12 mt-4 mb-0">
                            <h6 class="text-muted small text-uppercase fw-bold m-0">Contacto Secundario (Opcional)</h6>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label text-muted small">Nombre del Contacto 2</label>
                            <input type="text" class="form-control uppercase-input" name="contacto_emergencia_nombre2" value="<?= $emp['contacto_emergencia_nombre2'] ?>">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label text-muted small">Teléfono del Contacto 2</label>
                            <input type="tel" class="form-control" name="contacto_emergencia_telefono2" value="<?= $emp['contacto_emergencia_telefono2'] ?>">
                        </div>
                    </div>

                    <div class="section-title text-primary mt-4">3. Institucional</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Renglón</label>
                            <select class="form-select" name="id_renglon" required>
                                <?php foreach($renglones as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $emp['id_renglon'] == $r['id'] ? 'selected' : '' ?>>
                                        <?= $r['codigo'] ?> - <?= $r['descripcion'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Área</label>
                            <select class="form-select" name="id_area" required>
                                <?php foreach($areas as $a): ?>
                                    <option value="<?= $a['id'] ?>" <?= $emp['id_area'] == $a['id'] ? 'selected' : '' ?>>
                                        <?= $a['nombre'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Inicio Labores</label>
                            <input type="date" class="form-control" name="fecha_inicio_labores" value="<?= $emp['fecha_inicio_labores'] ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Puesto Nominal (Contrato)</label>
                            <select class="form-select" name="id_puesto_nominal" required>
                                <?php foreach($cargos_nominales as $cn): ?>
                                    <option value="<?= $cn['id'] ?>" <?= $emp['id_puesto_nominal'] == $cn['id'] ? 'selected' : '' ?>>
                                        <?= $cn['nombre'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Puesto Funcional (Real)</label>
                            <select class="form-select" name="id_puesto_funcional" required>
                                <?php foreach($cargos_funcionales as $cf): ?>
                                    <option value="<?= $cf['id'] ?>" <?= $emp['id_puesto_funcional'] == $cf['id'] ? 'selected' : '' ?>>
                                        <?= $cf['nombre'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    
                    </div>

                    <div class="mt-5 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" onclick="window.history.back()">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Actualizar Cambios</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Previsualizar imagen
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('previewImg');
            var initials = document.getElementById('initialsAvatar');
            if(initials) {
                initials.classList.add('d-none');
                img.classList.remove('d-none');
            }
            img.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Validación Bootstrap y Conversión a Mayúsculas
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        
        // Convertir campos marcados a MAYÚSCULAS antes de enviar
        const inputsToUpper = form.querySelectorAll('.uppercase-input');
        inputsToUpper.forEach(input => {
            input.value = input.value.toUpperCase();
        });

        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

</body>
</html>