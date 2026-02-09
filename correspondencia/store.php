<?php
session_start();
require '../db.php'; // Incluye tu archivo de conexión a la base de datos

try {
    $sql = "INSERT INTO correspondencia (cite, remitente, referencia, fojas, fecha, estado, actualizado_en, eliminado_en) VALUES (:cite, :remitente, :referencia, :fojas, NOW(), 'En curso', NULL, NULL)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'cite' => $_POST['cite'],
        'remitente' => $_POST['remitente'],
        'referencia' => $_POST['referencia'],
        'fojas' => $_POST['fojas']
    ]);

    // Mostrar mensaje de alerta y redirigir
    $_SESSION['mensaje'] = 'Correspondencia registrada con éxito';
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['mensaje'] = 'Error al registrar correspondencia: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>