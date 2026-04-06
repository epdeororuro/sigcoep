<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$grupo_id = $_POST['grupo_id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

if (!$grupo_id) {
    $_SESSION['mensaje'] = 'ID de grupo no válido.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Obtener datos del grupo y verificar permisos
    $stmtGrupo = $pdo->prepare("SELECT correspondencia_id, responsable_id FROM derivacion_grupo WHERE id = ? AND estado IN ('activo', 'en_proceso')");
    $stmtGrupo->execute([$grupo_id]);
    $grupo = $stmtGrupo->fetch(PDO::FETCH_ASSOC);

    if (!$grupo) {
        throw new Exception("El grupo no existe o ya fue consolidado anteriormente.");
    }

    if ($grupo['responsable_id'] != $usuario_id) {
        throw new Exception("Solo el responsable del grupo puede consolidarlo.");
    }

    // 2. Validar regla de negocio: Todos los demás integrantes deben estar "aprobados"
    $stmtValidacion = $pdo->prepare("SELECT COUNT(*) FROM derivacion_grupo_detalle WHERE derivacion_grupo_id = ? AND es_principal = 0 AND estado != 'aprobado'");
    $stmtValidacion->execute([$grupo_id]);
    if ($stmtValidacion->fetchColumn() > 0) {
        throw new Exception("No puede consolidar el trámite. Todos los integrantes deben haber subido su informe y usted debe haberlos evaluado como 'Aprobados'.");
    }

    // 3. Marcar el grupo como consolidado
    $stmtUpdateGrupo = $pdo->prepare("UPDATE derivacion_grupo SET estado = 'consolidado' WHERE id = ?");
    $stmtUpdateGrupo->execute([$grupo_id]);

    // 4. Magia: Retornar la correspondencia al flujo normal (Bandeja de Aceptados del Responsable)
    $stmtCorr = $pdo->prepare("UPDATE correspondencia SET estado = 'Aceptado', actualizado_en = NOW() WHERE id = ?");
    $stmtCorr->execute([$grupo['correspondencia_id']]);

    $pdo->commit();
    $_SESSION['mensaje'] = '¡Trámite Consolidado! La correspondencia ha retornado a su bandeja principal de "Correspondencia Activa".';
    $_SESSION['mensaje_tipo'] = 'success';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = 'Error al consolidar: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
?>