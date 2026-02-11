<?php
// seed_catalogos.php

// 1. Incluimos la conexión
require_once 'seguridad.php';
require_once 'config/db.php';

try {
    echo "<h1>Iniciando Carga de Datos... ⏳</h1>";

    // --- A. SEMILLA DE RENGLONES PRESUPUESTARIOS ---
    // Datos comunes en planillas (puedes editar estos códigos)
    $renglones = [
        ['011', 'Personal Permanente'],
        ['022', 'Personal por Contrato'],
        ['029', 'Servicios Técnicos / Profesionales'],
        ['031', 'Jornales'],
        ['189', 'Otros Estudios y/o Servicios']
    ];

    $stmtR = $pdo->prepare("INSERT IGNORE INTO catalogo_renglones (codigo, descripcion) VALUES (?, ?)");
    
    foreach ($renglones as $renglon) {
        $stmtR->execute($renglon);
    }
    echo "<p>✅ Renglones insertados (si no existían previamente).</p>";


    // --- B. SEMILLA DE ÁREAS / DEPARTAMENTOS ---
    $areas = [
        'Dirección General',
        'Recursos Humanos',
        'Informática',
        'Medicina General',
        'Enfermería',
        'Jefatura',
        'Secretaría',
        'Consulta Externa',
        'Estadística',
        'Archivo',
        'Cuantitativa',
        'Adminisión Materno Neonatal',
        'Admisión General'
    ];

    $stmtA = $pdo->prepare("INSERT IGNORE INTO catalogo_areas (nombre) VALUES (?)"); // Asumiendo que definiste UNIQUE el nombre, si no, insertará duplicados. Si no es unique, mejor verificar antes.
    // Para simplificar y evitar errores si ejecutas esto 2 veces, usaremos una lógica simple de "INSERT IGNORE" no estándar o verificación previa es mejor, 
    // pero para este ejemplo, vamos a limpiar la tabla antes o confiar en que está vacía. 
    // *Mejor práctica simple:* Verificar si existe.
    
    foreach ($areas as $area) {
        // Verificamos si ya existe para no duplicar
        $check = $pdo->prepare("SELECT id FROM catalogo_areas WHERE nombre = ?");
        $check->execute([$area]);
        
        if ($check->rowCount() == 0) {
            $stmtA->execute([$area]);
        }
    }
    echo "<p>✅ Áreas insertadas correctamente.</p>";


    // --- C. SEMILLA DE CARGOS / PUESTOS ---
    $cargos = [
        'Jefe de departamento',
        'Subjefe de departamento',
        'Encargado de sección',
        'Registros Médicos',
        'SIGSA 3H COEX',
        'Producción',
        'SIGSA 3H Emergencia',
        'Auxiliar Registros Médicos Legal',
        'Secretaria',
        'Fafilitador de procesos',
        'Otro'
    ];

    $stmtC = $pdo->prepare("INSERT INTO catalogo_cargos (nombre) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM catalogo_cargos WHERE nombre = ?)");
    
    foreach ($cargos as $cargo) {
        // Truco SQL: Insertar solo si no existe usando los parámetros dos veces
        $stmtC->execute([$cargo, $cargo]);
    }
    echo "<p>✅ Cargos insertados correctamente.</p>";

    echo "<h2>¡Proceso Terminado con Éxito! 🎉</h2>";
    echo "<p>Ahora tu base de datos tiene información base para trabajar.</p>";
    echo "<a href='index.php'>Ir al Inicio (cuando lo creemos)</a>";

} catch (PDOException $e) {
    echo "<h1>Error en la carga ❌</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>