<?php
session_start();
require '../db.php';

// Validar acceso (Solo Administrador o Secretaria)
if (!isset($_SESSION['usuario_cargo']) || (strtolower(trim($_SESSION['usuario_cargo'])) != 'administrador' && strtolower(trim($_SESSION['usuario_cargo'])) != 'secretaria')) {
    $_SESSION['mensaje'] = 'Acceso denegado.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validaciones básicas
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $responsable_id = filter_input(INPUT_POST, 'responsable_id', FILTER_VALIDATE_INT);
    $miembros = isset($_POST['miembros']) && is_array($_POST['miembros']) ? array_map('intval', $_POST['miembros']) : [];

    if (empty($nombre) || empty($responsable_id) || empty($miembros)) {
        $_SESSION['mensaje'] = 'Todos los campos son obligatorios.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Insertar la comisión
        $stmt = $pdo->prepare("INSERT INTO comision (nombre, descripcion, responsable_id, estado) VALUES (?, ?, ?, 'Activo')");
        $stmt->execute([$nombre, $descripcion, $responsable_id]);
        $comision_id = $pdo->lastInsertId();

        // Insertar los miembros de la comisión
        $stmtMiembros = $pdo->prepare("INSERT INTO comision_miembro (comision_id, funcionario_id) VALUES (?, ?)");
        foreach ($miembros as $miembro_id) {
            $stmtMiembros->execute([$comision_id, $miembro_id]);
        }

        // Commit la transacción
        $pdo->commit();

        $_SESSION['mensaje'] = 'Comisión creada exitosamente.';
        $_SESSION['mensaje_tipo'] = 'success';
    } catch (PDOException $e) {
        // Si hay un error, rollback la transacción
        $pdo->rollBack();
        $_SESSION['mensaje'] = 'Error al crear la comisión: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
} else {
    $_SESSION['mensaje'] = 'Solicitud no válida.';
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
