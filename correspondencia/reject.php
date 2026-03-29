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
    $action_type = $_POST['action_type'] ?? 'final';

    if ($id <= 0) {
        $_SESSION['mensaje'] = 'Identificador de correspondencia inválido.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    if ($action_type === 'devolver') {
        // --- NUEVO FLUJO: Devolver al remitente anterior ---
        $motivo = trim($_POST['motivo_rechazo'] ?? '');
        if (empty($motivo)) {
            $_SESSION['mensaje'] = 'Debe especificar un motivo para la devolución.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: index.php');
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 1. Encontrar al remitente anterior en el historial
            $stmtPrev = $pdo->prepare("SELECT id_funcionario FROM derivacion WHERE id_correspondencia = :id ORDER BY fecha_derivacion DESC LIMIT 1, 1");
            $stmtPrev->execute([':id' => $id]);
            $id_funcionario_anterior = $stmtPrev->fetchColumn();

            if (!$id_funcionario_anterior) {
                throw new PDOException("No se pudo encontrar un remitente anterior para devolver la correspondencia.");
            }

            // 2. Actualizar la correspondencia: estado 'Aceptado' y en poder del remitente anterior
            $stmtUpdateCorr = $pdo->prepare("UPDATE correspondencia SET estado = 'Aceptado', idfuncionario_enturno = :id_anterior, actualizado_en = NOW() WHERE id = :id_corr");
            $stmtUpdateCorr->execute([':id_anterior' => $id_funcionario_anterior, ':id_corr' => $id]);

            // 3. Marcar el paso del usuario actual como "procesado" (porque ya lo devolvió)
            $stmtUpdateDeriv = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id_corr AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
            $stmtUpdateDeriv->execute([':id_corr' => $id, ':uid' => $usuario_id]);

            // 4. Insertar el registro de "Devolución" en el historial
            $motivo_completo = "[DEVUELTO POR " . ($_SESSION['usuario_nombre'] ?? 'Usuario') . "] " . $motivo;
            $stmtInsertDevolucion = $pdo->prepare("INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter, fecha_entrega_derivacion) VALUES (:id_corr, :id_anterior, NOW(), :motivo, 0, 'Devolución', NOW())");
            $stmtInsertDevolucion->execute([':id_corr' => $id, ':id_anterior' => $id_funcionario_anterior, ':motivo' => $motivo_completo]);

            $pdo->commit();
            $_SESSION['mensaje'] = 'Correspondencia devuelta al remitente anterior.';
            $_SESSION['mensaje_tipo'] = 'success';

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['mensaje'] = 'Error al devolver la correspondencia: ' . $e->getMessage();
            $_SESSION['mensaje_tipo'] = 'danger';
        }

    } else {
        // --- FLUJO ANTERIOR: Marcar con estado final "No cursada" ---
        try {
            $pdo->beginTransaction();
            $estado_destino = isset($_POST['estado_destino']) ? trim($_POST['estado_destino']) : 'Rechazado';
            if (!in_array($estado_destino, ['Rechazado', 'No cursada'])) { $estado_destino = 'Rechazado'; }

            $stmt = $pdo->prepare("UPDATE correspondencia SET estado = :estado, actualizado_en = NOW() WHERE id = :id");
            $stmt->execute([':estado' => $estado_destino, ':id' => $id]);

            $stmtDeriv = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
            $stmtDeriv->execute([':id' => $id, ':uid' => $usuario_id]);

            $pdo->commit();
            $_SESSION['mensaje'] = 'Correspondencia marcada como ' . $estado_destino . ' correctamente.';
            $_SESSION['mensaje_tipo'] = 'warning';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['mensaje'] = 'Error al procesar la correspondencia: ' . $e->getMessage();
            $_SESSION['mensaje_tipo'] = 'danger';
        }
    }
    header('Location: index.php');
    exit;
}
?>