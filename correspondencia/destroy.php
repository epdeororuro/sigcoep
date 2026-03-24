<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $usuario_cargo = $_SESSION['usuario_cargo'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? null;

    try {
        // Verificar si la correspondencia ya tiene derivaciones registradas
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM derivacion WHERE id_correspondencia = :id");
        $stmtCheck->execute([':id' => $id]);
        $tiene_derivaciones = $stmtCheck->fetchColumn() > 0;

        // Si tiene derivaciones y NO es administrador (ej. Secretaria), se bloquea la eliminación
        if ($tiene_derivaciones && strtolower($usuario_cargo) !== 'administrador') {
            $_SESSION['mensaje'] = 'No se puede eliminar: Esta correspondencia ya cuenta con derivaciones en curso.';
            $_SESSION['mensaje_tipo'] = 'danger';
        } else {
            // Iniciar transacción segura
            $pdo->beginTransaction();

            // 1. Eliminación lógica de la correspondencia (aplica para ambos roles si pasan la validación)
            $sql = "UPDATE correspondencia SET eliminado_en = NOW(), estado = 'Anulado' WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            // 2. Si el Administrador la está eliminando y ya tenía historial, dejamos constancia
            if ($tiene_derivaciones && strtolower($usuario_cargo) === 'administrador') {
                $sqlHistorial = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter) 
                                 VALUES (:id_corr, :id_func, NOW(), :instruccion, 0, 'Archivo')";
                $stmtHistorial = $pdo->prepare($sqlHistorial);
                $stmtHistorial->execute([
                    ':id_corr' => $id,
                    ':id_func' => $usuario_id,
                    ':instruccion' => '[SISTEMA] - CORRESPONDENCIA ANULADA Y ELIMINADA POR EL ADMINISTRADOR.'
                ]);
            }

            $pdo->commit();
            $_SESSION['mensaje'] = 'Correspondencia eliminada/anulada con éxito.';
            $_SESSION['mensaje_tipo'] = 'success';
        }
    } catch (PDOException $e) {
        // Revertir si hay un error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensaje'] = 'Error al intentar eliminar la correspondencia: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    
    header('Location: index.php');
    exit;
}
?>
