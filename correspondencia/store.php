<?php
session_start();
require '../db.php'; // Incluye tu archivo de conexión a la base de datos

try {
    $sql = "INSERT INTO correspondencia (hojaruta, remitente, referencia, fojas, fecha, estado, actualizado_en, eliminado_en) VALUES (:hojaruta, :remitente, :referencia, :fojas, NOW(), 'Registrado', NULL, NULL)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'hojaruta' => $_POST['hojaruta'],
        'remitente' => $_POST['remitente'],
        'referencia' => $_POST['referencia'],
        'fojas' => $_POST['fojas']
    ]);

    // Mostrar mensaje de alerta y redirigir al index
    $_SESSION['mensaje'] = 'Correspondencia registrada con éxito';
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['mensaje'] = 'Error al registrar correspondencia: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>