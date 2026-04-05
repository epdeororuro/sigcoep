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

        // 2. Verificar si el funcionario derivó correspondencias que aún no fueron aceptadas por el destinatario
        $stmtCheckDerivados = $pdo->prepare("
            SELECT COUNT(c.id) 
            FROM correspondencia c
            WHERE c.estado = 'Derivado' 
              AND c.eliminado_en IS NULL
              AND (
                  (SELECT d.id_funcionario 
                   FROM derivacion d 
                   WHERE d.id_correspondencia = c.id 
                   ORDER BY d.fecha_derivacion DESC 
                   LIMIT 1 OFFSET 1) = :id
                  OR 
                  (c.remitente_id = :id AND (SELECT COUNT(*) FROM derivacion d2 WHERE d2.id_correspondencia = c.id) = 1)
              )
        ");
        $stmtCheckDerivados->execute([':id' => $id]);
        $derivaciones_no_aceptadas = (int) $stmtCheckDerivados->fetchColumn();

        if ($correspondencias_pendientes > 0 || $derivaciones_no_aceptadas > 0) {
            // Bloqueo total: no se puede dar de baja si tiene documentos a su cargo o pendientes de ser aceptados
            $mensaje_alerta = "No se puede dar de baja.<ul>";
            if ($correspondencias_pendientes > 0) {
                $mensaje_alerta .= "<li>El funcionario tiene <strong>$correspondencias_pendientes</strong> correspondencia(s) en su poder.</li>";
            }
            if ($derivaciones_no_aceptadas > 0) {
                $mensaje_alerta .= "<li>Derivó <strong>$derivaciones_no_aceptadas</strong> correspondencia(s) que el destinatario aún <strong>no ha aceptado</strong>.</li>";
            }
            $mensaje_alerta .= "</ul>Debe regularizar estos trámites antes de proceder.";
            
            $_SESSION['mensaje'] = $mensaje_alerta;
            $_SESSION['mensaje_tipo'] = 'danger';
        } else {
            // 3. Si la validación pasa, procedemos a dar de baja (cambiar estado a Inactivo)
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