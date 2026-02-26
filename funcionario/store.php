<?php
session_start();
require '../db.php'; // Incluye tu archivo de conexión a la base de datos

try {
    // Generar usuario y password
    $usuario = strtolower(substr($_POST['nombre'], 0, 1) . $_POST['paterno']);
    $password = $_POST['ci'];
    $password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO funcionario (ci, nombre, paterno, materno, usuario, password, rol, id_puesto, estado, creado_en) 
            VALUES (:ci, :nombre, :paterno, :materno, :usuario, :password, :rol, :id_puesto, 'Activo', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'ci' => $_POST['ci'],
        'nombre' => $_POST['nombre'],
        'paterno' => $_POST['paterno'],
        'materno' => $_POST['materno'],
        'usuario' => $usuario,
        'password' => $password,
        'rol' => $_POST['rol'],
        'id_puesto' => $_POST['id_puesto']
    ]);

    $_SESSION['mensaje'] = 'Funcionario registrado con éxito';
    $_SESSION['mensaje_tipo'] = 'success';
    header('Location: index.php');
} catch (PDOException $e) {
    // Verificar si es un error de clave duplicada (CI ya existe)
    if ($e->getCode() == 23000) {
        $_SESSION['mensaje'] = 'El carnet de identidad ya está registrado en el sistema';
    } else {
        $_SESSION['mensaje'] = 'Error al registrar funcionario: Por favor intente nuevamente';
    }
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}
?>