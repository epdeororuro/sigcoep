<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');

// Solo el rol Administrador puede ver contraseñas en texto plano
if (!isset($_SESSION['usuario_cargo']) || strtolower($_SESSION['usuario_cargo']) !== 'administrador') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (isset($_POST['id'])) {
    $stmt = $pdo->prepare("SELECT usuario, contrasenia FROM funcionario WHERE id = :id");
    $stmt->execute([':id' => $_POST['id']]);
    $func = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($func) {
        echo json_encode([
            'usuario' => $func['usuario'],
            'contrasenia' => $func['contrasenia']
        ]);
    } else {
        echo json_encode(['error' => 'Funcionario no encontrado']);
    }
} else {
    echo json_encode(['error' => 'ID no proporcionado']);
}
