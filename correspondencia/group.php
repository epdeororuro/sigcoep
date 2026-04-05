<?php
session_start();
require '../db.php';

// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = 'Método no permitido.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

$id_hija = isset($_POST['id_hija']) ? intval($_POST['id_hija']) : 0;
$id_madre = isset($_POST['id_madre']) ? intval($_POST['id_madre']) : 0;
$usuario_id = $_SESSION['usuario_id'];

if ($id_hija <= 0 || $id_madre <= 0) {
    $_SESSION['mensaje'] = 'Datos de agrupación inválidos.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($id_hija === $id_madre) {
    $_SESSION['mensaje'] = 'Una correspondencia no puede agruparse a sí misma.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verificar que ambas correspondencias (hija y madre) pertenecen al usuario y están en estado 'Aceptado'
    $stmtCheck = $pdo->prepare(
        "SELECT hojaruta, estado FROM correspondencia WHERE id = :id AND idfuncionario_enturno = :uid"
    );
    
    // Verificar hija
    $stmtCheck->execute([':id' => $id_hija, ':uid' => $usuario_id]);
    $corr_hija = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if (!$corr_hija || $corr_hija['estado'] !== 'Aceptado') {
        throw new Exception("La correspondencia a agrupar no está en su poder o no tiene el estado 'Aceptado'.");
    }

    // Verificar madre
    $stmtCheck->execute([':id' => $id_madre, ':uid' => $usuario_id]);
    $corr_madre = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if (!$corr_madre || $corr_madre['estado'] !== 'Aceptado') {
        throw new Exception("La correspondencia madre seleccionada ya no está en su poder o su estado ha cambiado.");
    }

    // 2. Actualizar la correspondencia hija
    $stmtUpdateHija = $pdo->prepare(
        "UPDATE correspondencia SET estado = 'Agrupado', agrupado_en = :id_madre, actualizado_en = NOW() WHERE id = :id_hija"
    );
    $stmtUpdateHija->execute([':id_madre' => $id_madre, ':id_hija' => $id_hija]);

    // 3. Marcar el paso actual de la hija como procesado (para que no quede pendiente)
    $stmtUpdateDerivHija = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id_corr AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
    $stmtUpdateDerivHija->execute([':id_corr' => $id_hija, ':uid' => $usuario_id]);

    // 4. Insertar registro de derivación para la correspondencia HIJA (cierre)
    $instruccion_hija = "[AGRUPADO] Esta hoja de ruta se ha agrupado a la H.R. " . $corr_madre['hojaruta'] . " y se da por concluida.";
    $sqlDerivHija = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, fecha_entrega_derivacion, instruccion_adicional, fojas, caracter) VALUES (:id_corr, :id_func, NOW(), NOW(), :instruccion, 0, 'Archivo')";
    $stmtDerivHija = $pdo->prepare($sqlDerivHija);
    $stmtDerivHija->execute([':id_corr' => $id_hija, ':id_func' => $usuario_id, ':instruccion' => $instruccion_hija]);

    // 5. Insertar registro de derivación para la correspondencia MADRE (informativo)
    $instruccion_madre = "[AGRUPACIÓN] Se agrupó la H.R. " . $corr_hija['hojaruta'] . " a este trámite.";
    $sqlDerivMadre = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, fecha_entrega_derivacion, instruccion_adicional, fojas, caracter) VALUES (:id_corr, :id_func, NOW(), NOW(), :instruccion, 0, 'Informativo')";
    $stmtDerivMadre = $pdo->prepare($sqlDerivMadre);
    $stmtDerivMadre->execute([':id_corr' => $id_madre, ':id_func' => $usuario_id, ':instruccion' => $instruccion_madre]);

    $pdo->commit();
    $_SESSION['mensaje'] = 'Correspondencia H.R. ' . $corr_hija['hojaruta'] . ' agrupada con éxito a la H.R. ' . $corr_madre['hojaruta'] . '.';
    $_SESSION['mensaje_tipo'] = 'success';
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['mensaje'] = 'Error al agrupar la correspondencia: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
?>