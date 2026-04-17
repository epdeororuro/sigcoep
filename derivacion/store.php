<?php
session_start();
require '../db.php';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = 'Método no permitido';
    header('Location: ../correspondencia/index.php');
    exit;
}

// Recoger datos
$id_correspondencia = $_POST['id_correspondencia'] ?? null;
$instruccion = trim($_POST['instruccion_adicional'] ?? '');
$fojas = (isset($_POST['fojas']) && trim($_POST['fojas']) !== '') ? intval($_POST['fojas']) : 0;
$caracter = trim($_POST['caracter'] ?? '');

$destino_raw = $_POST['id_funcionario'] ?? null;
$id_funcionario = null;

// Procesar si es una comisión o un funcionario directo
if ($destino_raw && strpos($destino_raw, 'c_') === 0) {
    $id_comision = (int) str_replace('c_', '', $destino_raw);
    $stmtC = $pdo->prepare("SELECT nombre, responsable_id FROM comision WHERE id = :id");
    $stmtC->execute([':id' => $id_comision]);
    $com = $stmtC->fetch(PDO::FETCH_ASSOC);
    if ($com) {
        $id_funcionario = $com['responsable_id'];
        $instruccion = "[Comisión: " . $com['nombre'] . "]\n" . $instruccion;
    }
} elseif (strpos($destino_raw, 'f_') === 0) {
    $id_funcionario = (int) str_replace('f_', '', $destino_raw);
} else {
    $id_funcionario = intval($destino_raw); // Fallback de compatibilidad
}

if (empty($id_correspondencia) || empty($id_funcionario)) {
    $_SESSION['mensaje'] = 'Faltan datos obligatorios para derivar.';
    header('Location: ../correspondencia/index.php');
    exit;
}

// Validar que los campos obligatorios del formulario estén presentes
if (empty($instruccion) || empty($caracter)) {
    $_SESSION['mensaje'] = 'Complete todos los campos obligatorios antes de derivar.';
    header('Location: ../correspondencia/index.php');
    exit;
}

try {
    // Insertar derivación en la tabla `derivacion`
    $sql = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter) VALUES (:id_corr, :id_func, NOW(), :instr, :fojas, :caracter)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_corr' => $id_correspondencia,
        ':id_func' => $id_funcionario,
        ':instr' => $instruccion,
        ':fojas' => $fojas,
        ':caracter' => $caracter
    ]);

    $id_derivacion_insertada = $pdo->lastInsertId();

    // REGISTRO DE HISTORIAL DE IMPRESIONES (Para el sistema de cuadrícula)
    // Obtenemos el último número de historial de este usuario y le sumamos 1
    $stmtHist = $pdo->prepare("SELECT COALESCE(MAX(numero_historial), 0) + 1 FROM historial_impresiones WHERE id_funcionario = :uid");
    $stmtHist->execute([':uid' => $_SESSION['usuario_id']]);
    $nuevo_numero_historial = (int) $stmtHist->fetchColumn();

    $stmtInsHist = $pdo->prepare("INSERT INTO historial_impresiones (id_funcionario, id_derivacion, numero_historial, fecha_creacion) VALUES (:uid, :id_deriv, :num_hist, NOW())");
    $stmtInsHist->execute([
        ':uid' => $_SESSION['usuario_id'],
        ':id_deriv' => $id_derivacion_insertada,
        ':num_hist' => $nuevo_numero_historial
    ]);

    // Actualizar la fecha de entrega (recepción) de la derivación anterior si estaba pendiente
    if (isset($_SESSION['usuario_id'])) {
        $stmtUpdateDeriv = $pdo->prepare("UPDATE derivacion SET fecha_entrega_derivacion = NOW() WHERE id_correspondencia = :id_corr AND id_funcionario = :uid AND fecha_entrega_derivacion IS NULL ORDER BY fecha_derivacion DESC LIMIT 1");
        $stmtUpdateDeriv->execute([
            ':id_corr' => $id_correspondencia,
            ':uid' => $_SESSION['usuario_id']
        ]);
    }

    // Actualizar estado de la correspondencia
    $sqlUpd = "UPDATE correspondencia SET estado = 'Derivado', idfuncionario_enturno = :id_func, actualizado_en = NOW() WHERE id = :id";
    $stmtUpd = $pdo->prepare($sqlUpd);
    $stmtUpd->execute([
        ':id' => $id_correspondencia,
        ':id_func' => $id_funcionario
    ]);

    $_SESSION['mensaje'] = 'Correspondencia derivada con éxito';
    header('Location: ../correspondencia/index.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['mensaje'] = 'Error al registrar derivación: ' . $e->getMessage();
    header('Location: ../correspondencia/index.php');
    exit;
}
