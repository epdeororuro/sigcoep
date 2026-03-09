<?php
session_start();
require '../db.php';

// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $usuario_id = $_SESSION['usuario_id'];

    if ($id <= 0) {
        $_SESSION['mensaje'] = 'Identificador de correspondencia inválido.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        // Marcar como Aceptado solo si actualmente está Derivado
        $sql = "UPDATE correspondencia 
                SET estado = 'Aceptado',
                    actualizado_en = NOW()
                WHERE id = :id AND estado = 'Derivado'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = 'Correspondencia aceptada correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'No se pudo aceptar la correspondencia (verifique su estado actual).';
            $_SESSION['mensaje_tipo'] = 'danger';
        }

        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al aceptar la correspondencia: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}

