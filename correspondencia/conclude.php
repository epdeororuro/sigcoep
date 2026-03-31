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
    $nota = trim($_POST['nota_conclusion'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];

    if ($id <= 0) {
        $_SESSION['mensaje'] = 'ID de correspondencia inválido.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Marcar el paso actual como procesado para que no quede pendiente en el historial
        $stmtUpdateDeriv = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id_corr AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
        $stmtUpdateDeriv->execute([':id_corr' => $id, ':uid' => $usuario_id]);

        // 2. Insertar el paso de "Concluido" en el historial
        $instruccion = "[CONCLUIDO] ";
        $instruccion .= !empty($nota) ? "Nota: " . $nota : "Trámite finalizado por el usuario.";

        $sqlDerivacion = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, fecha_entrega_derivacion, instruccion_adicional, fojas, caracter) 
                          VALUES (:id_corr, :id_func, NOW(), NOW(), :instruccion, 0, 'Concluido')";
        $stmtDerivacion = $pdo->prepare($sqlDerivacion);
        $stmtDerivacion->execute([':id_corr' => $id, ':id_func' => $usuario_id, ':instruccion' => $instruccion]);

        // 3. Actualizar estado a 'Concluido' (Se queda en poder del usuario actual)
        $sqlCorrespondencia = "UPDATE correspondencia SET estado = 'Concluido', actualizado_en = NOW() WHERE id = :id";
        $stmtCorrespondencia = $pdo->prepare($sqlCorrespondencia);
        $stmtCorrespondencia->execute([':id' => $id]);

        $pdo->commit();
        $_SESSION['mensaje'] = 'El trámite ha sido concluido exitosamente.';
        $_SESSION['mensaje_tipo'] = 'success';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensaje'] = 'Error al concluir el trámite: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    header('Location: index.php');
    exit;
}
?>