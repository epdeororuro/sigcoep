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
    $tipo_archivo = $_POST['tipo_archivo'] ?? 'personal';
    $nota = trim($_POST['nota_archivo'] ?? '');
    
    $usuario_id = $_SESSION['usuario_id'];
    $usuario_cargo = strtolower($_SESSION['usuario_cargo'] ?? '');

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

        // 2. Decidir el flujo según el tipo de archivo seleccionado
        if ($tipo_archivo === 'central' && $usuario_cargo !== 'archivista central') {
            
            // Buscar al funcionario con rol Archivista Central
            $stmtArchivista = $pdo->prepare("SELECT id FROM funcionario WHERE LOWER(rol) = 'archivista central' AND estado = 'Activo' LIMIT 1");
            $stmtArchivista->execute();
            $archivista = $stmtArchivista->fetch(PDO::FETCH_ASSOC);

            if (!$archivista) {
                throw new Exception("No se encontró ningún funcionario activo con el rol 'Archivista Central' en el sistema.");
            }

            $id_archivista = $archivista['id'];
            $instruccion = "Para resguardo final en Archivo Central.";
            if (!empty($nota)) {
                $instruccion .= "\nNota/Ubicación sugerida: " . $nota;
            }

            // Generar una derivación formal hacia el archivista
            $sqlDerivacion = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter) 
                              VALUES (:id_corr, :id_func, NOW(), :instruccion, 0, 'Archivo')";
            $stmtDerivacion = $pdo->prepare($sqlDerivacion);
            $stmtDerivacion->execute([':id_corr' => $id, ':id_func' => $id_archivista, ':instruccion' => $instruccion]);

            // Actualizar correspondencia a 'Derivado'
            $sqlCorrespondencia = "UPDATE correspondencia SET estado = 'Derivado', idfuncionario_enturno = :id_func, actualizado_en = NOW() WHERE id = :id";
            $stmtCorrespondencia = $pdo->prepare($sqlCorrespondencia);
            $stmtCorrespondencia->execute([':id_func' => $id_archivista, ':id' => $id]);

            $_SESSION['mensaje'] = 'Correspondencia enviada al Archivo Central exitosamente.';

        } else {
            
            // Flujo: Archivo Personal (o el Archivista Central ejecutando su archivo final)
            $instruccion = "[ARCHIVADO] ";
            $instruccion .= !empty($nota) ? "Ubicación/Nota: " . $nota : "Guardado en archivo físico personal/central.";

            // Insertar paso de cierre en el historial
            $sqlDerivacion = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, fecha_entrega_derivacion, instruccion_adicional, fojas, caracter) 
                              VALUES (:id_corr, :id_func, NOW(), NOW(), :instruccion, 0, 'Archivo')";
            $stmtDerivacion = $pdo->prepare($sqlDerivacion);
            $stmtDerivacion->execute([':id_corr' => $id, ':id_func' => $usuario_id, ':instruccion' => $instruccion]);

            // Actualizar estado a 'Archivado' (Se queda en poder del usuario actual)
            $sqlCorrespondencia = "UPDATE correspondencia SET estado = 'Archivado', actualizado_en = NOW() WHERE id = :id";
            $stmtCorrespondencia = $pdo->prepare($sqlCorrespondencia);
            $stmtCorrespondencia->execute([':id' => $id]);

            $_SESSION['mensaje'] = 'Correspondencia archivada y cerrada exitosamente.';
        }

        $pdo->commit();
        $_SESSION['mensaje_tipo'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensaje'] = 'Error al procesar: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    header('Location: index.php');
    exit;
}