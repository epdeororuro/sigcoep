<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$detalle_id = $_POST['detalle_id'] ?? 0;
$accion = $_POST['accion'] ?? ''; // 'aceptar' o 'rechazar'
$usuario_id = $_SESSION['usuario_id'];

if (!$detalle_id || !in_array($accion, ['aceptar', 'rechazar'])) {
    $_SESSION['mensaje'] = 'Datos o acción no válida.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

try {
    // Verificar que el funcionario sea dueño de la tarea y esté pendiente
    $stmt = $pdo->prepare("SELECT id FROM derivacion_grupo_detalle WHERE id = ? AND funcionario_id = ? AND estado = 'pendiente'");
    $stmt->execute([$detalle_id, $usuario_id]);
    if (!$stmt->fetch()) {
        throw new Exception('No se encontró la tarea o ya fue procesada anteriormente.');
    }

    $nuevo_estado = ($accion === 'aceptar') ? 'aceptado' : 'rechazado';
    
    $update = $pdo->prepare("UPDATE derivacion_grupo_detalle SET estado = ? WHERE id = ?");
    $update->execute([$nuevo_estado, $detalle_id]);

    $_SESSION['mensaje'] = ($accion === 'aceptar') ? 'Tarea grupal aceptada correctamente. Puede subir su informe desde la pestaña Aceptados.' : 'Tarea rechazada con éxito.';
    $_SESSION['mensaje_tipo'] = 'success';
} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;