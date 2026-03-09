<?php
session_start();
require '../db.php';

try {
    // Obtener datos del formulario
    $tipo_remitente = $_POST['tipo_remitente'] ?? 'externo';
    $referencia = $_POST['referencia'] ?? '';
    $fojas = intval($_POST['fojas'] ?? 1);

    // Validaciones básicas
    if (empty($referencia)) {
        throw new Exception('Referencia es requerida');
    }
    if ($fojas <= 0) {
        throw new Exception('Fojas debe ser mayor a 0');
    }

    // Variables para remitente
    $remitente_id = null;
    $remitente_externo = null;
    $remitente = '';

    if ($tipo_remitente === 'interno') {
        // Obtener funcionario interno
        $remitente_id = intval($_POST['remitente_id'] ?? 0);
        if ($remitente_id <= 0) {
            throw new Exception('Debe seleccionar un funcionario');
        }

        // Obtener datos del funcionario
        $stmt = $pdo->prepare("SELECT nombre, paterno, materno FROM funcionario WHERE id = :id AND estado = 'Activo'");
        $stmt->execute([':id' => $remitente_id]);
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$funcionario) {
            throw new Exception('Funcionario no encontrado o inactivo');
        }

        // Construir nombre completo
        $remitente = trim($funcionario['nombre'] . ' ' . ($funcionario['paterno'] ?? '') . ' ' . ($funcionario['materno'] ?? ''));

    } else if ($tipo_remitente === 'externo') {
        // Obtener remitente externo
        $remitente_externo = $_POST['remitente_externo'] ?? '';
        if (empty($remitente_externo)) {
            throw new Exception('Nombre del remitente externo es requerido');
        }
        $remitente = $remitente_externo;
    } else {
        throw new Exception('Tipo de remitente inválido');
    }

    // Generar automáticamente la hoja de ruta: total de registros + 1 / año actual
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM correspondencia");
    $totalCorrespondencia = (int) $stmtCount->fetchColumn();
    $numeroHojaRuta = $totalCorrespondencia + 1;
    $hojaruta = $numeroHojaRuta . '/' . date('Y');

    // Manejo de carga de foto (opcional)
    $fotoNombre = '';
    $uploadDir = __DIR__ . '/../assets/fotos_correspondencia/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto']['tmp_name'];
        $origName = basename($_FILES['foto']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            throw new Exception('Formato de foto no permitido. Use JPG, PNG, GIF o WEBP');
        }

        $fotoNombre = uniqid('corr_', true) . '.' . $ext;
        $destino = $uploadDir . $fotoNombre;

        if (!move_uploaded_file($tmpName, $destino)) {
            throw new Exception('No se pudo guardar la foto en el servidor');
        }
    }

    // Insertar la correspondencia
    $sql = "INSERT INTO correspondencia (hojaruta, remitente_id, remitente_externo, tipo_remitente, remitente, referencia, fojas, fecha, foto, estado, actualizado_en, eliminado_en) 
            VALUES (:hojaruta, :remitente_id, :remitente_externo, :tipo_remitente, :remitente, :referencia, :fojas, NOW(), :foto, 'Registrado', NULL, NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hojaruta' => $hojaruta,
        ':remitente_id' => $remitente_id,
        ':remitente_externo' => $remitente_externo,
        ':tipo_remitente' => $tipo_remitente,
        ':remitente' => $remitente,
        ':referencia' => $referencia,
        ':fojas' => $fojas,
        ':foto' => $fotoNombre
    ]);

    $_SESSION['mensaje'] = 'Correspondencia registrada con éxito';
    $_SESSION['mensaje_tipo'] = 'success';
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $_SESSION['mensaje'] = 'Error al registrar correspondencia: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}
?>