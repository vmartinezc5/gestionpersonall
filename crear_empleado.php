<?php
// crear_empleado.php
require_once 'seguridad.php';
require_once 'config/db.php';

// Obtener datos para los "Selects" (Dropdowns)
try {
    $renglones = $pdo->query("SELECT * FROM catalogo_renglones ORDER BY codigo ASC")->fetchAll();
    $areas = $pdo->query("SELECT * FROM catalogo_areas ORDER BY nombre ASC")->fetchAll();
    $cargos_nominales = $pdo->query("SELECT * FROM catalogo_puestos_nominales ORDER BY nombre ASC")->fetchAll();
    $cargos_funcionales = $pdo->query("SELECT * FROM catalogo_cargos ORDER BY nombre ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar catálogos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Empleado | Sistema RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: none; }
        .form-floating > label { color: #6c757d; }
        .section-header { color: #0d6efd; border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; margin-bottom: 1.5rem; margin-top: 2rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary fw-bold">👤 Nuevo Empleado</h2>
                <a href="index.php" class="btn btn-outline-secondary">← Volver al Listado</a>
            </div>

            <div class="card p-4">
                <form action="guardar_empleado.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    
                    <h5 class="section-header">1. Información Personal</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="dpi" name="dpi" placeholder="DPI" required minlength="13" maxlength="13" pattern="\d{13}">
                                <label for="dpi">DPI (CUI) *</label>
                                <div class="invalid-feedback">El DPI debe tener 13 dígitos numéricos.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nombres" name="nombres" required oninput="this.value = this.value.toUpperCase()" placeholder="NOMBRES">
                                <label for="nombres">Nombres *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required oninput="this.value = this.value.toUpperCase()" placeholder="APELLIDOS">
                                <label for="apellidos">Apellidos *</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                                <label for="fecha_nacimiento">Fecha Nacimiento *</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <select class="form-select" id="genero" name="genero" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                                <label for="genero">Género *</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <select class="form-select" id="tipo_sangre" name="tipo_sangre">
                                    <option value="" selected disabled>Seleccione...</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                                <label for="tipo_sangre">Tipo de Sangre</label>
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nit" name="nit" placeholder="NIT">
                                <label for="nit">NIT</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="section-header">2. Contacto y Ubicación</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" required>
                                <label for="telefono">Teléfono *</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" placeholder="nombre@ejemplo.com">
                                <label for="correo_electronico">Correo Electrónico</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="direccion" name="direccion" placeholder="Dirección Completa" style="height: 80px" required></textarea>
                                <label for="direccion">Dirección de Residencia *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="departamento" name="departamento" required oninput="this.value = this.value.toUpperCase()" placeholder="DEPARTAMENTO">
                                <label for="departamento">Departamento</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="municipio" name="municipio" required oninput="this.value = this.value.toUpperCase()" placeholder="MUNICIPIO">
                                  <label for="municipio">Municipio</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="section-header">3. Contactos de Emergencia</h5>
                    
                    <h6 class="text-muted small text-uppercase fw-bold mb-2">Contacto Principal</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="contacto_emergencia_nombre" name="contacto_emergencia_nombre" required oninput="this.value = this.value.toUpperCase()" placeholder="NOMBRE CONTACTO">
                                <label for="contacto_emergencia_nombre">Nombre del Contacto 1</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="contacto_emergencia_telefono" name="contacto_emergencia_telefono" placeholder="Teléfono Contacto">
                                <label for="contacto_emergencia_telefono">Teléfono del Contacto 1</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-muted small text-uppercase fw-bold mb-2 mt-2">Contacto Secundario (Opcional)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="contacto_emergencia_nombre2" name="contacto_emergencia_nombre2" required oninput="this.value = this.value.toUpperCase()" placeholder="NOMBRE CONTACTO 2">
                                <label for="contacto_emergencia_nombre2">Nombre del Contacto 2</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="contacto_emergencia_telefono2" name="contacto_emergencia_telefono2" placeholder="Teléfono Contacto 2">
                                <label for="contacto_emergencia_telefono2">Teléfono del Contacto 2</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="section-header">4. Información Institucional</h5>
                    <div class="row g-3">
                         <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="id_renglon" name="id_renglon" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($renglones as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= $r['codigo'] ?> - <?= $r['descripcion'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="id_renglon">Renglón Presupuestario *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="fecha_inicio_labores" name="fecha_inicio_labores" required>
                                <label for="fecha_inicio_labores">Fecha Inicio Labores *</label>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="id_area" name="id_area" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($areas as $a): ?>
                                        <option value="<?= $a['id'] ?>"><?= $a['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="id_area">Área / Departamento *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="id_puesto_nominal" name="id_puesto_nominal" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($cargos_nominales as $cn): ?>
                                        <option value="<?= $cn['id'] ?>">
                                            <?= !empty($cn['codigo']) ? $cn['codigo'] . ' - ' : '' ?><?= $cn['nombre'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="id_puesto_nominal">Puesto Nominal (Según Contrato) *</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="id_puesto_funcional" name="id_puesto_funcional" required>
                                    <option value="" selected disabled>Seleccione...</option>
                                    <?php foreach($cargos_funcionales as $cf): ?>
                                        <option value="<?= $cf['id'] ?>"><?= $cf['nombre'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="id_puesto_funcional">Puesto Funcional (Real) *</label>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="section-header">5. Multimedia</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label for="foto_perfil" class="form-label text-secondary">Foto de Perfil</label>
                            <input class="form-control" type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                        <button type="reset" class="btn btn-light me-md-2">Limpiar</button>
                        <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Empleado</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()

// Validación extra para DPI solo números
document.getElementById('dpi').addEventListener('input', function (e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

</body>
</html>