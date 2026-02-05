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
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_cargo'] = $user['cargo'];
        $_SESSION['usuario_area'] = $user['area'];

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
