<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['total' => 0, 'correspondencia' => 0, 'grupos' => 0]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$data = ['total' => 0, 'correspondencia' => 0, 'grupos' => 0];

try {
    // 1. Nuevas correspondencias (Derivadas al usuario que aún no acepta)
    $stmtCorr = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE idfuncionario_enturno = ? AND estado = 'Derivado' AND eliminado_en IS NULL");
    $stmtCorr->execute([$usuario_id]);
    $data['correspondencia'] = (int) $stmtCorr->fetchColumn();

    // 2. Nuevas tareas de grupo (Pendientes de aceptar)
    $stmtGrupo = $pdo->prepare("
        SELECT COUNT(*) 
        FROM derivacion_grupo_detalle dgd 
        JOIN derivacion_grupo dg ON dgd.derivacion_grupo_id = dg.id 
        WHERE dgd.funcionario_id = ? AND dgd.estado = 'pendiente' AND dg.estado IN ('activo', 'en_proceso')
    ");
    $stmtGrupo->execute([$usuario_id]);
    $data['grupos'] = (int) $stmtGrupo->fetchColumn();

    $data['total'] = $data['correspondencia'] + $data['grupos'];

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(['total' => 0, 'error' => $e->getMessage()]);
}
?>