<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$detalle_id = $_POST['detalle_id'] ?? 0;
$accion = $_POST['accion'] ?? ''; // 'aprobar' o 'rechazar'
$observaciones = trim($_POST['observaciones'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if (!$detalle_id || !in_array($accion, ['aprobar', 'rechazar'])) {
    $_SESSION['mensaje'] = 'Datos de evaluación inválidos.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($accion === 'rechazar' && empty($observaciones)) {
    $_SESSION['mensaje'] = 'Debe especificar una observación al momento de rechazar.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar permisos: el usuario actual debe ser el responsable del grupo
    $stmtCheck = $pdo->prepare("
        SELECT dg.responsable_id 
        FROM derivacion_grupo_detalle dgd
        JOIN derivacion_grupo dg ON dgd.derivacion_grupo_id = dg.id
        WHERE dgd.id = ?
    ");
    $stmtCheck->execute([$detalle_id]);
    $responsable_id = $stmtCheck->fetchColumn();

    if ($responsable_id != $usuario_id) {
        throw new Exception("No tiene permisos para evaluar este informe.");
    }

    $nuevo_estado = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';

    $stmtUpdateDetalle = $pdo->prepare("UPDATE derivacion_grupo_detalle SET estado = ? WHERE id = ?");
    $stmtUpdateDetalle->execute([$nuevo_estado, $detalle_id]);

    $stmtUpdateInforme = $pdo->prepare("UPDATE informes SET estado = ?, observaciones = ? WHERE derivacion_grupo_detalle_id = ?");
    $stmtUpdateInforme->execute([$nuevo_estado, $observaciones, $detalle_id]);

    $pdo->commit();
    $_SESSION['mensaje'] = ($accion === 'aprobar') ? 'Informe aprobado correctamente. La barra de progreso ha sido actualizada.' : 'El informe fue observado y devuelto al funcionario para su corrección.';
    $_SESSION['mensaje_tipo'] = 'success';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = 'Error al evaluar: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
?>