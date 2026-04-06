<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id_correspondencia = $_POST['id_correspondencia'] ?? 0;
$contenido = trim($_POST['contenido'] ?? '');
$usuario_id = $_SESSION['usuario_id'];

if ($id_correspondencia <= 0 || empty($contenido)) {
    $_SESSION['mensaje'] = 'El contenido/conclusión es obligatorio.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Obtener el ID del detalle del grupo para este usuario y trámite
    $stmtDetalle = $pdo->prepare("
        SELECT dgd.id, dgd.estado, dgd.es_principal 
        FROM derivacion_grupo_detalle dgd
        JOIN derivacion_grupo dg ON dgd.derivacion_grupo_id = dg.id
        WHERE dg.correspondencia_id = :id_corr 
          AND dgd.funcionario_id = :uid 
          AND dg.estado IN ('activo', 'en_proceso')
    ");
    $stmtDetalle->execute([':id_corr' => $id_correspondencia, ':uid' => $usuario_id]);
    $detalle = $stmtDetalle->fetch(PDO::FETCH_ASSOC);

    if (!$detalle) {
        throw new Exception("No forma parte de un grupo activo o el trámite ya fue consolidado.");
    }

    // Si es un integrante normal no puede editar si ya lo aprobaron. El Responsable sí puede auto-editarse.
    if ($detalle['estado'] === 'aprobado' && !$detalle['es_principal']) {
        throw new Exception("No puede editar el informe porque ya fue aprobado por el responsable.");
    }

    $detalle_id = $detalle['id'];

    // 2. Verificar si ya existe un informe para actualizarlo
    $stmtInf = $pdo->prepare("SELECT id, archivo_adjunto FROM informes WHERE derivacion_grupo_detalle_id = :detalle_id LIMIT 1");
    $stmtInf->execute([':detalle_id' => $detalle_id]);
    $informeExistente = $stmtInf->fetch(PDO::FETCH_ASSOC);

    $archivoNombre = $informeExistente ? $informeExistente['archivo_adjunto'] : '';

    // 3. Manejo del archivo adjunto si se subió uno nuevo
    if (isset($_FILES['archivo_informe']) && $_FILES['archivo_informe']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/informes_grupo/';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        
        $tmpName = $_FILES['archivo_informe']['tmp_name'];
        $origName = basename($_FILES['archivo_informe']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
            throw new Exception("Formato de archivo no válido. Solo JPG, PNG, WEBP o PDF.");
        }
        
        $nuevoArchivoNombre = uniqid('informe_') . '.' . $ext;
        $destino = $uploadDir . $nuevoArchivoNombre;
        
        if (!move_uploaded_file($tmpName, $destino)) {
            throw new Exception("Error al guardar el archivo adjunto en el servidor.");
        }

        // Eliminar el archivo anterior si existía para no ocupar espacio basura
        if ($informeExistente && !empty($informeExistente['archivo_adjunto'])) {
            $rutaAnterior = $uploadDir . $informeExistente['archivo_adjunto'];
            if (file_exists($rutaAnterior)) {
                @unlink($rutaAnterior);
            }
        }
        $archivoNombre = $nuevoArchivoNombre;
    } elseif (!$informeExistente) {
        throw new Exception("Debe adjuntar un archivo (PDF/Imagen) para su primer informe.");
    }

    // 4. Insertar o Actualizar el informe. Si es el responsable, se auto-aprueba.
    $nuevo_estado_informe = $detalle['es_principal'] ? 'aprobado' : 'enviado';

    if ($informeExistente) {
        $stmtUpdateInf = $pdo->prepare("UPDATE informes SET contenido = ?, archivo_adjunto = ?, estado = ?, fecha = NOW() WHERE id = ?");
        $stmtUpdateInf->execute([$contenido, $archivoNombre, $nuevo_estado_informe, $informeExistente['id']]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO informes (derivacion_grupo_detalle_id, contenido, archivo_adjunto, estado) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$detalle_id, $contenido, $archivoNombre, $nuevo_estado_informe]);
    }

    $stmtUpdateDetalle = $pdo->prepare("UPDATE derivacion_grupo_detalle SET estado = ?, fecha_respuesta = NOW() WHERE id = ?");
    $stmtUpdateDetalle->execute([$nuevo_estado_informe, $detalle_id]);

    $pdo->commit();
    $_SESSION['mensaje'] = 'Su informe ha sido enviado con éxito.';
    $_SESSION['mensaje_tipo'] = 'success';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje'] = 'Error al subir informe: ' . $e->getMessage();
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;