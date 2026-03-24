<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $hojaruta = $_POST['hojaruta'] ?? '';
    $referencia = $_POST['referencia'] ?? '';
    $fojas = intval($_POST['fojas'] ?? 1);
    $anexo = $_POST['edit_anexo'] ?? '';
    $actualizado_en = date('Y-m-d H:i:s');

    // Foto actual y posible nueva foto
    $foto_actual = $_POST['foto_actual'] ?? '';
    $fotoNombre = $foto_actual;
    $uploadDir = __DIR__ . '/../assets/fotos_correspondencia/';

    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['foto_nueva']) && $_FILES['foto_nueva']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto_nueva']['tmp_name'];
        $origName = basename($_FILES['foto_nueva']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            throw new Exception('Formato de foto no permitido. Use JPG, PNG, GIF o WEBP');
        }

        $nuevoNombre = uniqid('corr_', true) . '.' . $ext;
        $destino = $uploadDir . $nuevoNombre;

        if (!move_uploaded_file($tmpName, $destino)) {
            throw new Exception('No se pudo guardar la foto en el servidor');
        }

        // Eliminar foto anterior si existe
        if (!empty($foto_actual)) {
            $rutaAnterior = $uploadDir . $foto_actual;
            if (is_file($rutaAnterior)) {
                @unlink($rutaAnterior);
            }
        }

        $fotoNombre = $nuevoNombre;
    }

    // Tipo de remitente y datos asociados desde el formulario de edición
    $tipo_remitente = $_POST['edit_tipo_remitente'] ?? 'externo';
    $remitente_id = null;
    $remitente_externo = null;
    $remitente = '';

    try {
        if ($tipo_remitente === 'interno') {
            $remitente_id = intval($_POST['edit_remitente_id'] ?? 0);
            if ($remitente_id <= 0) {
                throw new Exception('Debe seleccionar un funcionario como remitente');
            }

            // Obtener datos del funcionario para construir el nombre completo
            $stmtFunc = $pdo->prepare("SELECT nombre, paterno, materno FROM funcionario WHERE id = :id AND estado = 'Activo'");
            $stmtFunc->execute([':id' => $remitente_id]);
            $funcionario = $stmtFunc->fetch(PDO::FETCH_ASSOC);

            if (!$funcionario) {
                throw new Exception('Funcionario remitente no encontrado o inactivo');
            }

            $remitente = trim($funcionario['nombre'] . ' ' . ($funcionario['paterno'] ?? '') . ' ' . ($funcionario['materno'] ?? ''));
        } elseif ($tipo_remitente === 'externo') {
            $remitente_externo = $_POST['edit_remitente_externo'] ?? '';
            if (empty($remitente_externo)) {
                throw new Exception('Nombre del remitente externo es requerido');
            }
            $remitente = $remitente_externo;
        } else {
            throw new Exception('Tipo de remitente inválido');
        }

        if (empty($hojaruta)) {
            throw new Exception('Hoja de ruta es requerida');
        }
        if (empty($referencia)) {
            throw new Exception('Referencia es requerida');
        }
        if ($fojas <= 0) {
            throw new Exception('Fojas debe ser mayor a 0');
        }

        $sql = "UPDATE correspondencia 
                SET hojaruta = :hojaruta,
                    remitente_id = :remitente_id,
                    remitente_externo = :remitente_externo,
                    tipo_remitente = :tipo_remitente,
                    remitente = :remitente,
                    referencia = :referencia,
                    fojas = :fojas,
                    anexo = :anexo,
                    foto = :foto,
                    actualizado_en = :actualizado_en 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':hojaruta', $hojaruta);
        $stmt->bindParam(':remitente_id', $remitente_id);
        $stmt->bindParam(':remitente_externo', $remitente_externo);
        $stmt->bindParam(':tipo_remitente', $tipo_remitente);
        $stmt->bindParam(':remitente', $remitente);
        $stmt->bindParam(':referencia', $referencia);
        $stmt->bindParam(':fojas', $fojas, PDO::PARAM_INT);
        $stmt->bindParam(':anexo', $anexo);
        $stmt->bindParam(':foto', $fotoNombre);
        $stmt->bindParam(':actualizado_en', $actualizado_en);
        $stmt->execute();

        $_SESSION['mensaje'] = 'Correspondencia actualizada con éxito';
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['mensaje'] = 'Error al actualizar correspondencia: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['mensaje'] = 'No se proporcionó el ID de la correspondencia';
    header('Location: index.php');
    exit;
}
?>