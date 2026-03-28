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
        $pdo->beginTransaction();

        // Determinar si es un Rechazo normal o una declaración de "No cursada"
        $estado_destino = isset($_POST['estado_destino']) ? trim($_POST['estado_destino']) : 'Rechazado';
        if (!in_array($estado_destino, ['Rechazado', 'No cursada'])) {
            $estado_destino = 'Rechazado';
        }

        // 1. Actualizar estado de la correspondencia
        $sql = "UPDATE correspondencia SET estado = :estado, actualizado_en = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':estado' => $estado_destino, ':id' => $id]);

        // 2. Registrar la fecha de entrega como "procesado" (para cerrar el ciclo en derivacion)
        $sqlDeriv = "UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1";
        $stmtDeriv = $pdo->prepare($sqlDeriv);
        $stmtDeriv->execute([':id' => $id, ':uid' => $usuario_id]);

        $pdo->commit();
        $_SESSION['mensaje'] = 'Correspondencia marcada como ' . $estado_destino . ' correctamente.';
        $_SESSION['mensaje_tipo'] = 'warning';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['mensaje'] = 'Error al rechazar la correspondencia: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    header('Location: index.php');
    exit;
}
?>