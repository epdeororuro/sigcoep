<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nota = trim($_POST['nota_solicitud'] ?? '');
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

        // Obtener el ID del Área a la que pertenece el funcionario
        $stmtUser = $pdo->prepare("SELECT id_area FROM funcionario WHERE id = :uid");
        $stmtUser->execute([':uid' => $usuario_id]);
        $user_data = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // Buscar al Archivista Central
        $stmtArchivista = $pdo->prepare("SELECT id FROM funcionario WHERE LOWER(rol) = 'archivista central' AND estado = 'Activo' LIMIT 1");
        $stmtArchivista->execute();
        $archivista = $stmtArchivista->fetch(PDO::FETCH_ASSOC);

        // Marcar el paso actual como completado
        $stmtUpdateDeriv = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id_corr AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
        $stmtUpdateDeriv->execute([':id_corr' => $id, ':uid' => $usuario_id]);

        $id_destino = null;
        $nuevo_estado = '';
        $instruccion = '';

        // Lógica de derivación según jerarquía
        if ($usuario_cargo === 'gerente' || (isset($user_data['id_area']) && false)) { // Para simplificar, trataremos al Gerente como un ente libre
            if (!$archivista) throw new Exception("No hay un Archivista Central registrado o activo en el sistema.");
            $id_destino = $archivista['id'];
            $nuevo_estado = 'Pendiente Archivo';
            $instruccion = "[SOLICITUD ARCHIVO DIRECTA] " . (!empty($nota) ? $nota : "Enviar a resguardo definitivo.");
        } else {
            if (empty($user_data['id_area'])) throw new Exception("No tiene un Departamento asignado. Contacte al Administrador.");
            
            // Obtener el PUESTO del Jefe del Área
            $stmtArea = $pdo->prepare("SELECT jefe_puesto_id FROM area WHERE id = :id_area");
            $stmtArea->execute([':id_area' => $user_data['id_area']]);
            $area = $stmtArea->fetch(PDO::FETCH_ASSOC);
            
            if (empty($area['jefe_puesto_id'])) throw new Exception("Su Departamento no tiene un Puesto de Jefe asignado. Contacte al Administrador.");

            // Encontrar al funcionario que ocupa ese puesto
            $stmtJefe = $pdo->prepare("SELECT id FROM funcionario WHERE id_puesto = :puesto_id AND estado = 'Activo' LIMIT 1");
            $stmtJefe->execute([':puesto_id' => $area['jefe_puesto_id']]);
            $jefe = $stmtJefe->fetch(PDO::FETCH_ASSOC);

            if (!$jefe) throw new Exception("No se encontró un funcionario activo para el puesto de Jefe de su Departamento.");

            $id_destino = $jefe['id'];
            $nuevo_estado = 'Revisión Archivo';
            $instruccion = "[SOLICITUD APROBACIÓN ARCHIVO] " . (!empty($nota) ? $nota : "Solicito visto bueno para Archivo Central.");
        }

        // Insertar la derivación hacia el Jefe o Archivista
        $sqlDerivacion = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter) VALUES (:id_corr, :id_func, NOW(), :instruccion, 0, 'Archivo')";
        $stmtDerivacion = $pdo->prepare($sqlDerivacion);
        $stmtDerivacion->execute([':id_corr' => $id, ':id_func' => $id_destino, ':instruccion' => $instruccion]);

        // Actualizar el estado de la hoja de ruta
        $sqlCorrespondencia = "UPDATE correspondencia SET estado = :estado, idfuncionario_enturno = :id_func, actualizado_en = NOW() WHERE id = :id";
        $stmtCorrespondencia = $pdo->prepare($sqlCorrespondencia);
        $stmtCorrespondencia->execute([':estado' => $nuevo_estado, ':id_func' => $id_destino, ':id' => $id]);

        $pdo->commit();
        $_SESSION['mensaje'] = 'La solicitud de Archivo se ha enviado exitosamente.';
        $_SESSION['mensaje_tipo'] = 'success';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['mensaje'] = 'Error al enviar solicitud: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    header('Location: index.php');
    exit;
}
?>