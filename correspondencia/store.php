<?php
session_start();
require '../db.php';

try {
    // Obtener datos del formulario
    $tipo_remitente = $_POST['tipo_remitente'] ?? 'externo';
    $referencia = $_POST['referencia'] ?? '';
    $fojas = intval($_POST['fojas'] ?? 1);
    $anexo = $_POST['anexo'] ?? '';
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
    $anio = date('Y');
    $uploadBaseDir = __DIR__ . '/../assets/fotos_correspondencia/';
    $uploadDir = $uploadBaseDir . $anio . '/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto']['tmp_name'];
        $origName = basename($_FILES['foto']['name']);
        
        // Validar MIME type real por seguridad (evitar scripts camuflados)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type_real = $finfo->file($tmpName);
        $mimes_permitidos = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        
        if (!in_array($mime_type_real, $mimes_permitidos)) {
            throw new Exception('Error de seguridad: El contenido del archivo no es válido o no está permitido.');
        }
        
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        if (!in_array($ext, $allowed)) {
            throw new Exception('Formato de archivo no permitido. Use JPG, PNG, WEBP o PDF');
        }

        // Reemplazar la barra de la hoja de ruta (ej. 1/2023) por un guion (ej. 1-2023)
        $safe_hojaruta = str_replace(['/', '\\'], '-', $hojaruta);
        $fotoNombreSolo = $safe_hojaruta . '.' . $ext;
        $destino = $uploadDir . $fotoNombreSolo;
        $fotoNombre = $anio . '/' . $fotoNombreSolo; // Guardado en DB (ej: 2024/1-2024.jpg)

        if ($ext === 'pdf') {
            if (!move_uploaded_file($tmpName, $destino)) {
                throw new Exception('No se pudo guardar el documento PDF en el servidor');
            }
        } else {
            // Proceso de compresión de imágenes usando GD Library
            if (extension_loaded('gd')) {
                $info = @getimagesize($tmpName);
                $img = null;
                if ($info) {
                    if ($info['mime'] == 'image/jpeg') $img = @imagecreatefromjpeg($tmpName);
                    elseif ($info['mime'] == 'image/png') $img = @imagecreatefrompng($tmpName);
                    elseif ($info['mime'] == 'image/webp') $img = @imagecreatefromwebp($tmpName);
                }
    
                if ($img) {
                    if ($ext === 'png') { imagealphablending($img, true); imagesavealpha($img, true); imagepng($img, $destino, 7); }
                    elseif ($ext === 'webp') { imagewebp($img, $destino, 75); }
                    else { imagejpeg($img, $destino, 75); } // Calidad 75 para reducir tamaño sin pérdida notable
                    imagedestroy($img);
                } else {
                    if (!move_uploaded_file($tmpName, $destino)) {
                        throw new Exception('No se pudo guardar la foto en el servidor');
                    }
                }
            } else {
                // Fallback si la extensión GD no está activada en PHP
                if (!move_uploaded_file($tmpName, $destino)) {
                    throw new Exception('No se pudo guardar la foto en el servidor');
                }
            }
        }
    }

    // Insertar la correspondencia
    $sql = "INSERT INTO correspondencia (hojaruta, remitente_id, remitente_externo, tipo_remitente, remitente, referencia, fojas, anexo, fecha_registro, foto, estado, actualizado_en, eliminado_en) 
            VALUES (:hojaruta, :remitente_id, :remitente_externo, :tipo_remitente, :remitente, :referencia, :fojas, :anexo, NOW(), :foto, 'Registrado', NULL, NULL)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':hojaruta' => $hojaruta,
        ':remitente_id' => $remitente_id,
        ':remitente_externo' => $remitente_externo,
        ':tipo_remitente' => $tipo_remitente,
        ':remitente' => $remitente,
        ':referencia' => $referencia,
        ':fojas' => $fojas,
        ':anexo' => $anexo,
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