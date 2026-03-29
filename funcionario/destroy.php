<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        // 1. Verificar si el funcionario tiene correspondencias en su poder
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE idfuncionario_enturno = :id AND eliminado_en IS NULL");
        $stmtCheck->execute([':id' => $id]);
        $correspondencias_pendientes = (int) $stmtCheck->fetchColumn();

        if ($correspondencias_pendientes > 0) {
            // Bloqueo total: no se puede dar de baja si tiene documentos a su cargo
            $_SESSION['mensaje'] = "No se puede dar de baja. El funcionario aún tiene <strong>$correspondencias_pendientes</strong> correspondencia(s) en su poder. Debe derivarlas y asegurar su recepción antes de proceder.";
            $_SESSION['mensaje_tipo'] = 'danger';
        } else {
            // 2. Si la validación pasa, procedemos a dar de baja (cambiar estado a Inactivo)
            $sql = "UPDATE funcionario SET estado = 'Inactivo' WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $_SESSION['mensaje'] = 'Funcionario dado de baja exitosamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al intentar dar de baja al funcionario: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    
    header('Location: index.php');
    exit;
}
?>