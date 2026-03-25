<?php
session_start();
require '../db.php';

// Validar acceso (Solo Administrador)
if (!isset($_SESSION['usuario_cargo']) || strtolower(trim($_SESSION['usuario_cargo'])) !== 'administrador') {
    $_SESSION['mensaje'] = 'Acceso denegado.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // Eliminar los miembros de la comisión
            $stmtEliminarMiembros = $pdo->prepare("DELETE FROM comision_miembros WHERE comision_id = ?");
            $stmtEliminarMiembros->execute([$id]);

            // Eliminar la comisión
            $stmt = $pdo->prepare("DELETE FROM comision WHERE id = ?");
            $stmt->execute([$id]);

            // Commit la transacción
            $pdo->commit();

            $_SESSION['mensaje'] = 'Comisión y sus miembros eliminados exitosamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } catch (PDOException $e) {
            // Si hay un error, rollback la transacción
            $pdo->rollBack();
            $_SESSION['mensaje'] = 'Error al eliminar la comisión: ' . $e->getMessage();
            $_SESSION['mensaje_tipo'] = 'danger';
        }
    } else {
        $_SESSION['mensaje'] = 'ID de comisión no válido.';
        $_SESSION['mensaje_tipo'] = 'danger';
    }
} else {
    $_SESSION['mensaje'] = 'Solicitud no válida.';
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
?>