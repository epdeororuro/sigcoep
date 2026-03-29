<?php
session_start();
require '../db.php';

// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $usuario_id = $_SESSION['usuario_id'];
    $usuario_cargo = strtolower($_SESSION['usuario_cargo'] ?? '');
    $autorizacion = trim($_POST['autorizacion'] ?? '');

    if ($id <= 0) {
        $_SESSION['mensaje'] = 'ID de correspondencia inválido.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar estado de la correspondencia y su poseedor actual
        $stmtCheck = $pdo->prepare("SELECT idfuncionario_enturno, estado, hojaruta FROM correspondencia WHERE id = :id");
        $stmtCheck->execute([':id' => $id]);
        $correspondencia = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$correspondencia || $correspondencia['estado'] !== 'Archivado') {
            throw new Exception("No se puede desarchivar. La correspondencia no tiene el estado 'Archivado'.");
        }

        $id_poseedor = $correspondencia['idfuncionario_enturno'];
        $hojaruta = $correspondencia['hojaruta'];
        
        // Determinar si es Archivo Central (viendo el rol del poseedor actual)
        $stmtOwner = $pdo->prepare("SELECT rol FROM funcionario WHERE id = :uid");
        $stmtOwner->execute([':uid' => $id_poseedor]);
        $owner_rol = strtolower($stmtOwner->fetchColumn() ?? '');
        $es_archivo_central = ($owner_rol === 'archivista central');
        
        // Validar permisos
        if ($es_archivo_central) {
            if ($usuario_cargo !== 'administrador' && $usuario_cargo !== 'archivista central') {
                throw new Exception("Solo el Administrador o el Archivista Central pueden desarchivar documentos del Archivo Central.");
            }
            if (empty($autorizacion)) {
                throw new Exception("Debe ingresar la Autorización/Justificación para extraer un documento del Archivo Central.");
            }
        } else {
            // Archivo personal: solo el dueño o el admin pueden
            if ($usuario_cargo !== 'administrador' && $id_poseedor != $usuario_id) {
                throw new Exception("No tiene permisos para desarchivar este documento personal.");
            }
        }

        // Manejo de la foto de respaldo
        $fotoNombre = '';
        if (isset($_FILES['foto_desarchivo']) && $_FILES['foto_desarchivo']['error'] === UPLOAD_ERR_OK) {
            $anio = date('Y');
            $uploadBaseDir = __DIR__ . '/../assets/solicitud_desarchivo/';
            $uploadDir = $uploadBaseDir . $anio . '/';
            if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
            $tmpName = $_FILES['foto_desarchivo']['tmp_name'];
            $origName = basename($_FILES['foto_desarchivo']['name']);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                // Formato: Desarchivar-1-2023.jpg
                $safe_hojaruta = str_replace(['/', '\\'], '-', $hojaruta);
                $fotoNombreSolo = 'Desarchivar-' . $safe_hojaruta . '.' . $ext;
                $destino = $uploadDir . $fotoNombreSolo;
                $fotoNombre = $anio . '/' . $fotoNombreSolo; // Respaldo para el historial

                if ($ext === 'pdf') {
                    if (!move_uploaded_file($tmpName, $destino)) throw new Exception("Error al guardar PDF.");
                } else {
                    // Proceso de compresión de imágenes
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
                            else { imagejpeg($img, $destino, 75); }
                            imagedestroy($img);
                        } else {
                            if (!move_uploaded_file($tmpName, $destino)) throw new Exception("Error al guardar la imagen.");
                        }
                    } else {
                        // Fallback si la extensión GD no está activada en PHP
                        if (!move_uploaded_file($tmpName, $destino)) throw new Exception("Error al guardar la imagen.");
                    }
                }
            } else {
                throw new Exception("Formato de archivo no válido. Solo se permiten JPG, PNG, WEBP o PDF.");
            }
        } else {
            throw new Exception("Debe adjuntar obligatoriamente la foto del formulario firmado y sellado por gerencia.");
        }

        // 1. Cambiar estado de vuelta a 'Aceptado'
        $sqlCorrespondencia = "UPDATE correspondencia SET estado = 'Aceptado', actualizado_en = NOW() WHERE id = :id";
        $stmtCorrespondencia = $pdo->prepare($sqlCorrespondencia);
        $stmtCorrespondencia->execute([':id' => $id]);

        // 2. Insertar rastro explícito en el historial de derivaciones
        $instruccion = "[DESARCHIVADO] Retorna a bandeja de pendientes.";
        if (!empty($autorizacion)) { $instruccion .= "\nAutorización: " . $autorizacion; }
        if (!empty($fotoNombre)) { $instruccion .= "\n(Archivo respaldo: " . $fotoNombre . ")"; }
        
        $sqlDerivacion = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, fecha_entrega_derivacion, instruccion_adicional, fojas, caracter) 
                          VALUES (:id_corr, :id_func, NOW(), NOW(), :instruccion, 0, 'Desarchivado')";
        $stmtDerivacion = $pdo->prepare($sqlDerivacion);
        $stmtDerivacion->execute([':id_corr' => $id, ':id_func' => $usuario_id, ':instruccion' => $instruccion]);

        $pdo->commit();
        $_SESSION['mensaje'] = 'Correspondencia desarchivada con éxito. Verifique su bandeja de Pendientes (Aceptados).';
        $_SESSION['mensaje_tipo'] = 'success';

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensaje'] = 'Error al procesar la solicitud: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }

    header('Location: index.php');
    exit;
}
?>