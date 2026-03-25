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
            $stmt = $pdo->prepare("UPDATE comision SET estado = 'Activo' WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['mensaje'] = 'Comisión restaurada exitosamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = 'Error al restaurar la comisión: ' . $e->getMessage();
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