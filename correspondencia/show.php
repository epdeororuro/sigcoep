<?php
session_start();
require '../db.php';

try {
    // determinar usuario actual y su rol
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_cargo = $_SESSION['usuario_cargo'] ?? null;

    // POST param
    $filtro_admin = $_POST['filtro_admin'] ?? 'derivados'; // opciones posibles: 'derivados', 'iniciados'

    if (in_array($usuario_cargo, ['Administrador', 'Secretaria'])) {
        // Administrador y Secretaria ven todas las correspondencias
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno
                FROM correspondencia c
                WHERE c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else if ($usuario_cargo === 'Gerente') {
        // Gerente ve todas desde estado Iniciado en adelante
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno
                FROM correspondencia c
                WHERE c.estado != 'Registrado'
                  AND c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else if ($usuario_cargo === 'Administrativo') {
        if ($filtro_admin === 'iniciados') {
            // Correspondencias iniciadas por el Administrativo (donde él fue remitente original)
            // No incluir las que ya están en estado Aceptado
            $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno
                    FROM correspondencia c
                    WHERE c.remitente_id = :uid
                      AND c.estado <> 'Aceptado'
                      AND c.eliminado_en IS NULL
                    ORDER BY c.fecha DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid' => $usuario_id]);
        } elseif ($filtro_admin === 'aceptados') {
            // Correspondencias actualmente aceptadas y en poder del usuario
            $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno
                    FROM correspondencia c
                    WHERE c.idfuncionario_enturno = :uid
                      AND c.estado = 'Aceptado'
                      AND c.eliminado_en IS NULL
                    ORDER BY c.fecha DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid' => $usuario_id]);
        } else {
            // Por defecto: Correspondencias derivadas a él en algún momento (solo estado Derivado)
            $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno
                    FROM correspondencia c
                    WHERE EXISTS (
                        SELECT 1 FROM derivacion d2
                        WHERE d2.id_correspondencia = c.id
                          AND d2.id_funcionario = :uid
                    )
                      AND c.estado = 'Derivado'
                      AND c.eliminado_en IS NULL
                    ORDER BY c.fecha DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid' => $usuario_id]);
        }
    } else {
        // Otros roles (fallback general): derivadas
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno
                FROM correspondencia c
                WHERE EXISTS (
                    SELECT 1 FROM derivacion d2
                    WHERE d2.id_correspondencia = c.id
                      AND d2.id_funcionario = :uid
                )
                  AND c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $usuario_id]);
    }

    $correspondencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($correspondencias as $correspondencia) {
        $acciones = '';
        $estado_display = $correspondencia['estado'];

        // Foto (thumbnail clickeable si existe)
        $fotoHtml = '';
        if (!empty($correspondencia['foto'])) {
            $urlFoto = '../assets/fotos_correspondencia/' . $correspondencia['foto'];
            $fotoHtml = '<a href="#" onclick="verFoto(\'' . $urlFoto . '\'); return false;"><img src="' . $urlFoto . '" alt="Foto" style="height:40px;" class="rounded border"></a>';
        }

        // Añadir indicador (punto rojo) si el documento está en poder de este funcionario
        if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
            $estado_display .= ' <span class="badge bg-danger blink ms-2" title="En su poder">&bull;</span>';
        }

        // --- SISTEMA DE BOTONES POR ROL ---
        $btn_aceptar = '<button type="button" class="btn btn-dark btn-sm" style="margin-left:4px;" title="Aceptar/Rechazar" onclick="abrirAceptarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-check-circle"></i></button>';

        $btn_editar = '<form action="" method="post" style="display: inline;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button></form>';
        $btn_eliminar = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Eliminar" onclick="confirmarEliminacion('.$correspondencia['id'].')"><i class="bi bi-trash"></i></button>';
        $btn_iniciar = '<form action="create.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-primary btn-sm" title="Iniciar"><i class="bi bi-play-circle"></i></button></form>';
        $btn_derivar = '<form action="" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-success btn-sm" title="Derivar" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button></form>';
        $btn_historial = '<form action="../derivacion/index.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-info btn-sm" title="Ver historial de derivaciones"><i class="bi bi-list-ul"></i></button></form>';
        $btn_imprimir = '<button type="button" class="btn btn-secondary btn-sm ms-1" style="margin-left:4px;" title="Imprimir" onclick="solicitarPagina('.$correspondencia['id'].')"><i class="bi bi-printer"></i></button>';

        $estado = $correspondencia['estado'];

        if (in_array($usuario_cargo, ['Administrador', 'Secretaria'])) {
            // Administrador y Secretaria pueden editar y eliminar en cualquier etapa
            $acciones .= $btn_editar . $btn_eliminar;

            if ($estado === 'Registrado') {
                $acciones .= $btn_iniciar;
            } elseif ($estado === 'Derivado') {
                // Solo ver historial + aceptar/rechazar
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_aceptar;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Aceptado') {
                // Aceptado: puede derivar y ver historial (e imprimir)
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
                $acciones .= $btn_imprimir;
            } else {
                // Otros estados: conservar lógica anterior
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
                $acciones .= $btn_imprimir;
            }
        } else if ($usuario_cargo === 'Gerente') {
            if ($estado !== 'Registrado') {
                if ($estado === 'Derivado') {
                    if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                        $acciones .= $btn_aceptar;
                    }
                    $acciones .= $btn_historial;
                } elseif ($estado === 'Aceptado') {
                    if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                        $acciones .= $btn_derivar;
                    }
                    $acciones .= $btn_historial;
                } else {
                    if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                        $acciones .= $btn_derivar;
                    }
                    $acciones .= $btn_historial;
                }
            }
        } else if ($usuario_cargo === 'Administrativo') {
            if ($estado === 'Derivado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_aceptar;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Aceptado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
            } else {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
            }
        }

        $data[] = array(
            'hojaruta' => $correspondencia['hojaruta'],
            'remitente' => $correspondencia['remitente'],
            'referencia' => $correspondencia['referencia'],
            'fojas' => $correspondencia['fojas'],
            'foto' => $fotoHtml,
            'fecha' => date('d-m-Y H:i:s', strtotime($correspondencia['fecha'])),
            'estado' => $estado_display,
            'acciones' => $acciones
        );
    }
    echo json_encode(array("data" => $data));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>