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
    // Esta funcionalidad ha sido deshabilitada por motivos de seguridad.
    // Las contraseñas nunca deben ser visibles en texto plano.
    echo json_encode(['error' => 'Funcionalidad deshabilitada por seguridad. Implemente un flujo de "restablecer contraseña".']);
} else {
    echo json_encode(['error' => 'ID no proporcionado']);
}
