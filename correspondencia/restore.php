<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $sql = "UPDATE correspondencia SET estado = 'En curso', eliminado_en = NULL WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Mostrar mensaje de alerta y redirigir
        $_SESSION['mensaje'] = 'Correspondencia restaurada con éxito';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al restaurar correspondencia: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['mensaje'] = 'No se proporcionó el ID de la correspondencia';
    header('Location: index.php');
    exit;
}
?>