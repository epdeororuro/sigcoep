<?php
session_start();
require '../db.php';

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = 'Método no permitido';
    header('Location: ../correspondencia/index.php');
    exit;
}

// Validar permisos estrictos para conformar la comisión
$rol_sesion = strtolower($_SESSION['usuario_cargo'] ?? '');
// Abierto para que los funcionarios operativos puedan solicitar trabajo grupal
$puede_conformar_comision = !in_array($rol_sesion, ['secretaria', 'archivista central']);

if (!$puede_conformar_comision) {
    $_SESSION['mensaje'] = 'Error: No cuenta con autorización para conformar grupos de trabajo.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: ../correspondencia/index.php');
    exit;
}

$id_correspondencia = $_POST['id_correspondencia'] ?? null;
$responsable_id = $_POST['responsable_id'] ?? null;
$participantes_ids = $_POST['participantes_ids'] ?? []; // Array de IDs de funcionarios
$fecha_limite = $_POST['fecha_limite'] ?? null;
$creado_por = $_SESSION['usuario_id'];

if (empty($id_correspondencia) || empty($responsable_id) || empty($participantes_ids)) {
    $_SESSION['mensaje'] = 'Faltan datos obligatorios para crear el grupo de trabajo.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: ../correspondencia/index.php');
    exit;
}

// Asegurarse de que el responsable esté incluido en la lista de participantes
if (!in_array($responsable_id, $participantes_ids)) {
    $participantes_ids[] = $responsable_id;
}

try {
    $pdo->beginTransaction();

    // 1. Crear el registro general del grupo
    $stmtGrupo = $pdo->prepare("INSERT INTO derivacion_grupo (correspondencia_id, creado_por, responsable_id, fecha_limite, estado) VALUES (:id_corr, :creado_por, :responsable_id, :fecha_limite, 'activo')");
    $stmtGrupo->execute([
        ':id_corr' => $id_correspondencia,
        ':creado_por' => $creado_por,
        ':responsable_id' => $responsable_id,
        ':fecha_limite' => empty($fecha_limite) ? null : $fecha_limite
    ]);
    $grupo_id = $pdo->lastInsertId();

    // 2. Insertar a todos los participantes en el detalle del grupo
    $stmtDetalle = $pdo->prepare("INSERT INTO derivacion_grupo_detalle (derivacion_grupo_id, funcionario_id, es_principal) VALUES (?, ?, ?)");
    foreach ($participantes_ids as $func_id) {
        $es_principal = ($func_id == $responsable_id) ? 1 : 0;
        $stmtDetalle->execute([$grupo_id, $func_id, $es_principal]);
    }

    // 3. Aislar la correspondencia (Pasa a estado 'En Grupo' en poder del responsable)
    $stmtCorr = $pdo->prepare("UPDATE correspondencia SET estado = 'En Grupo', idfuncionario_enturno = ?, actualizado_en = NOW() WHERE id = ?");
    $stmtCorr->execute([$responsable_id, $id_correspondencia]);

    // 4. Cerrar la derivación anterior (Marcar la fecha de entrega del remitente actual)
    $stmtUpdateDeriv = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id_corr AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
    $stmtUpdateDeriv->execute([
        ':id_corr' => $id_correspondencia,
        ':uid' => $creado_por
    ]);

    $pdo->commit();
    $_SESSION['mensaje'] = 'Grupo de trabajo creado con éxito. Se notificó a todos los participantes.';
    $_SESSION['mensaje_tipo'] = 'success';
    header('Location: ../correspondencia/index.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = 'Error al crear grupo: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: ../correspondencia/index.php');
    exit;
}