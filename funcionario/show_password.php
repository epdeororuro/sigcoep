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
    $id = intval($_POST['id']);

    try {
        $stmt = $pdo->prepare("SELECT contrasenia FROM funcionario WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(['contrasenia' => $row['contrasenia']]);
        } else {
            echo json_encode(['contrasenia' => '']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID no proporcionado']);
}

