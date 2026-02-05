<?php
session_start();
require '../db.php'; // Incluye tu archivo de conexión a la base de datos

try {
    // Generar usuario y password
    $usuario = strtolower(substr($_POST['nombre'], 0, 1) . $_POST['paterno']);
    $password = substr($_POST['nombre'], 0, 1) . $_POST['ci'];
    $password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO funcionario (ci, nombre, paterno, materno, usuario, password, cargo, area, estado, creado_en) 
            VALUES (:ci, :nombre, :paterno, :materno, :usuario, :password, :cargo, :area, 'Activo', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'ci' => $_POST['ci'],
        'nombre' => $_POST['nombre'],
        'paterno' => $_POST['paterno'],
        'materno' => $_POST['materno'],
        'usuario' => $usuario,
        'password' => $password,
        'cargo' => $_POST['cargo'],
        'area' => $_POST['area']
    ]);

    header('Location: index.php');
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>