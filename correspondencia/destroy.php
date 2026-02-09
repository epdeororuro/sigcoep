<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $fechaEliminacion = date('Y-m-d H:i:s');

    try {
        $sql = "UPDATE correspondencia SET estado = 'Anulado', eliminado_en = :fechaEliminacion WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fechaEliminacion', $fechaEliminacion);
        $stmt->execute();

        // Mostrar mensaje de alerta y redirigir
        $_SESSION['mensaje'] = 'Correspondencia eliminada con éxito';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al eliminar correspondencia: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['mensaje'] = 'No se proporcionó el ID de la correspondencia';
    header('Location: index.php');
    exit;
}
?>