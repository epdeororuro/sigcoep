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
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $responsable_id = filter_input(INPUT_POST, 'responsable_id', FILTER_VALIDATE_INT);
    $miembros = isset($_POST['miembros']) && is_array($_POST['miembros']) ? array_map('intval', $_POST['miembros']) : [];

    if (empty($id) || empty($nombre) || empty($responsable_id) || empty($miembros)) {
        $_SESSION['mensaje'] = 'Todos los campos son obligatorios.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Actualizar la comisión
        $stmt = $pdo->prepare("UPDATE comision SET nombre = ?, descripcion = ?, responsable_id = ?, actualizado_en = CURRENT_TIMESTAMP() WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $responsable_id, $id]);

        // Eliminar los miembros actuales de la comisión
        $stmtEliminarMiembros = $pdo->prepare("DELETE FROM comision_miembro WHERE comision_id = ?");
        $stmtEliminarMiembros->execute([$id]);

        // Insertar los nuevos miembros de la comisión
        $stmtInsertarMiembros = $pdo->prepare("INSERT INTO comision_miembro (comision_id, funcionario_id) VALUES (?, ?)");
        foreach ($miembros as $miembro_id) {
            $stmtInsertarMiembros->execute([$id, $miembro_id]);
        }

        // Commit la transacción
        $pdo->commit();

        $_SESSION['mensaje'] = 'Comisión actualizada exitosamente.';
        $_SESSION['mensaje_tipo'] = 'success';
    } catch (PDOException $e) {
        // Si hay un error, rollback la transacción
        $pdo->rollBack();
        $_SESSION['mensaje'] = 'Error al actualizar la comisión: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
} else {
    $_SESSION['mensaje'] = 'Solicitud no válida.';
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
