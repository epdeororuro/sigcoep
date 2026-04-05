<?php
session_start();
require 'db.php';
require 'config.php';

// Verifica que venga del formulario
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    // Buscar usuario en DB
    $stmt = $pdo->prepare("SELECT * FROM funcionario WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])){
        // Login exitoso
        
        session_regenerate_id(true); // Previene el secuestro de sesión (Session Fixation)
        
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_cargo'] = $user['rol'];
        // guardar puesto para controles especiales (4 = Secretaria Ejecutiva)
        $_SESSION['usuario_id_puesto'] = $user['id_puesto'];

        header('Location: dashboard.php'); // Redirige al dashboard
        exit;
    } else {
        // Login fallido
        header('Location: index.php?error=1');
        exit;
    }

} else {
    // Si accede directamente sin POST
    header('Location: index.php');
    exit;
}
