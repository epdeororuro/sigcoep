<?php
session_start();

// Destruye todas las variables de sesión
$_SESSION = [];

// Destruye la sesión completamente
session_destroy();

// Opcional: borrar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirige al login
header('Location: index.php');
exit;
