<?php
// identificador.php
session_start();
require_once 'config/db.php'; // Importante: Conexión a la BD

// 1. Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';
$mensaje_exito = ''; // Nueva variable para mensajes positivos

// Detectar si viene de un cierre por inactividad
if (isset($_GET['error']) && $_GET['error'] == 'expirado') {
    $error = "Por seguridad, tu sesión se ha cerrado tras 40 minutos de inactividad.";
}

// --- NUEVO: Detectar si el usuario cerró sesión voluntariamente ---
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $mensaje_exito = "Has cerrado sesión correctamente. ¡Hasta pronto!";
}
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiamos espacios en blanco
    $usuario_form = trim($_POST['usuario'] ?? '');
    $password_form = trim($_POST['password'] ?? '');

    if (empty($usuario_form) || empty($password_form)) {
        $error = "Por favor ingrese usuario y contraseña.";
    } else {
        try {
            // 2. Buscar usuario en la Base de Datos
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ? LIMIT 1");
            $stmt->execute([$usuario_form]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. Verificar si existe el usuario y si la contraseña coincide (hash)
            if ($user && password_verify($password_form, $user['password'])) {
                
                // Verificar que el usuario no esté inactivo
                if ($user['estado'] === 'Activo') {
                    
                    // --- LOGIN EXITOSO ---
                    $_SESSION['usuario_logueado'] = true;
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nombre'] = $user['nombre_completo']; // Ej: Jefatura Principal
                    $_SESSION['usuario_rol'] = $user['rol'];       // Ej: Administrador

                    // 4. Registrar el acceso en la tabla 'historial_accesos'
                    $ip_acceso = $_SERVER['REMOTE_ADDR']; // Obtiene la IP del usuario
                    $logStmt = $pdo->prepare("INSERT INTO historial_accesos (usuario_id, ip_acceso) VALUES (?, ?)");
                    $logStmt->execute([$user['id'], $ip_acceso]);

                    // Redireccionar
                    header("Location: index.php");
                    exit;
                
                } else {
                    $error = "Su usuario está inactivo. Contacte a soporte.";
                }

            } else {
                $error = "Usuario o contraseña incorrectos.";
            }

        } catch (PDOException $e) {
            $error = "Error de sistema: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .card-login {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .login-body {
            padding: 40px 30px;
        }
        .btn-login {
            background: #0d6efd;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-login mx-auto">
                <div class="login-header">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="#0d6efd" class="bi bi-hospital" viewBox="0 0 16 16">
                            <path d="M8.5 5.034v1.1l.953-.55.5.867L9 7l.953.55-.5.866-.953-.55v1.1h-1v-1.1l-.953.55-.5-.866L7 7l-.953-.55.5-.866.953.55v-1.1h1ZM13.25 9a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5a.25.25 0 0 0 .25-.25v-.5a.25.25 0 0 0-.25-.25h-.5ZM13 11.25a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25v-.5Zm.25 1.75a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5a.25.25 0 0 0 .25-.25v-.5a.25.25 0 0 0-.25-.25h-.5Zm-11-4a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5A.25.25 0 0 0 3 7.75v-.5A.25.25 0 0 0 2.75 7h-.5Zm0 2a.25.25 0 0 0-.25.25v.5c0 .138.112.25.25.25h.5a.25.25 0 0 0 .25-.25v-.5a.25.25 0 0 0-.25-.25h-.5ZM2.75 12a.25.25 0 0 1 .25-.25h.5a.25.25 0 0 1 .25.25v.5a.25.25 0 0 1-.25.25h-.5a.25.25 0 0 1-.25-.25v-.5Z"/>
                            <path d="M5 1a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1a1 1 0 0 1 1 1v4h3a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h3V3a1 1 0 0 1 1-1V1Zm2 14h2v-3H7v3Zm3 0h1V3H5v12h1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3Zm0-14H6v1h4V1Zm2 7v7h3V8h-3Zm-8 7V8H1v7h3Z"/>
                        </svg>
                    </div>
                    <h4 class="fw-bold text-dark">Acceso Administrativo</h4>
                    <p class="text-muted mb-0 small">Ingrese sus credenciales institucionales</p>
                </div>

                <div class="login-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center py-2 mb-4" role="alert">
                            <small><?= $error ?></small>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($mensaje_exito)): ?>
                        <div class="alert alert-success text-center py-2 mb-4" role="alert">
                            <small><?= $mensaje_exito ?></small>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required autofocus>
                            <label for="usuario">Usuario</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                            <label for="password">Contraseña</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login btn-lg text-white">Ingresar al Sistema</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3 text-white-50 small">
                &copy; 2026 Sistema de Gestión Hospitalario diseñado por Victor Martínez.
            </div>
        </div>
    </div>
</div>

</body>
</html>