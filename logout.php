<?php
// logout.php
session_start();

// 1. Vaciar todas las variables de sesión actuales en memoria
$_SESSION = array();

// 2. Destruir la cookie de sesión en el navegador del usuario (Seguridad extra)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al login con un parámetro para mostrar un mensaje de despedida
header("Location: identificador.php?logout=success");
exit;
?>