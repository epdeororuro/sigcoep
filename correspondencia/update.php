<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $cite = $_POST['cite'];
    $remitente = $_POST['remitente'];
    $referencia = $_POST['referencia'];
    $fojas = $_POST['fojas'];
    $actualizado_en = date('Y-m-d H:i:s');

    try {
        $sql = "UPDATE correspondencia SET cite = :cite, remitente = :remitente, referencia = :referencia, fojas = :fojas, actualizado_en = :actualizado_en WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cite', $cite);
        $stmt->bindParam(':remitente', $remitente);
        $stmt->bindParam(':referencia', $referencia);
        $stmt->bindParam(':fojas', $fojas);
        $stmt->bindParam(':actualizado_en', $actualizado_en);
        $stmt->execute();

        // Mostrar mensaje de alerta y redirigir
        $_SESSION['mensaje'] = 'Correspondencia actualizada con éxito';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al actualizar correspondencia: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['mensaje'] = 'No se proporcionó el ID de la correspondencia';
    header('Location: index.php');
    exit;
}
?>