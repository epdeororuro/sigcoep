<?php
session_start();
require '../db.php';

try {
    // determinar usuario actual y su rol
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_cargo = $_SESSION['usuario_cargo'] ?? null;
 
    // Filtro desde la URL (o POST), para soportar menú y pestañas
    $filtro = $_REQUEST['filtro'] ?? null;
 
    $base_sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno, c.anexo, 
                 COALESCE(
                     (SELECT MAX(fecha_entrega_derivacion) FROM derivacion WHERE id_correspondencia = c.id AND id_funcionario = c.idfuncionario_enturno), 
                     c.actualizado_en, 
                     c.fecha
                 ) as fecha_referencia,
                 f.nombre, f.paterno, f.materno 
                 FROM correspondencia c
                 LEFT JOIN funcionario f ON c.idfuncionario_enturno = f.id";
    $where_clauses = ["c.eliminado_en IS NULL"];
    $params = [];
 
    if ($usuario_cargo === 'Administrador') {
        // Para el Administrador, se pueden implementar pestañas para filtrar por estado
        switch ($filtro) {
            case 'registrado':
                $where_clauses[] = "c.estado = 'Registrado'";
                break;
            case 'iniciado':
                $where_clauses[] = "c.estado = 'Iniciado'";
                break;
            case 'derivado':
                $where_clauses[] = "c.estado = 'Derivado'";
                break;
            case 'aceptado':
                $where_clauses[] = "c.estado = 'Aceptado'";
                break;
            // 'todos' o null no añade filtro de estado, muestra todo
        }
    } else if ($usuario_cargo === 'Secretaria') {
        if ($filtro === 'rechazados') {
            $where_clauses[] = "c.estado = 'Rechazado'";
        } else {
            $where_clauses[] = "c.estado != 'Rechazado'";
        }
    } else if (in_array($usuario_cargo, ['Gerente', 'Administrativo'])) {
        // Los roles Gerente y Administrativo comparten casi las mismas bandejas
        $filtro = $filtro ?? 'entrantes'; // Filtro por defecto: Bandeja de Entrada
 
        if ($filtro === 'iniciados' && $usuario_cargo === 'Administrativo') {
            // Bandeja de Iniciados: Correspondencias creadas por el usuario
            $where_clauses[] = "c.remitente_id = :uid";
            $params[':uid'] = $usuario_id;
        } elseif ($filtro === 'para_iniciar' && $usuario_cargo === 'Gerente') {
            // Bandeja para Iniciar: Registros nuevos derivados de ventanilla única
            $where_clauses[] = "c.estado = 'Iniciado'";
        } elseif ($filtro === 'pendientes') {
            // Bandeja de Pendientes: Aceptados y en poder del usuario
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Aceptado'";
            $params[':uid'] = $usuario_id;
        } elseif ($filtro === 'despachados') {
            // Bandeja de Despachados: Correspondencias que pasaron por el usuario y derivó
            $where_clauses[] = "EXISTS (
                SELECT 1 FROM derivacion d2
                WHERE d2.id_correspondencia = c.id
                  AND d2.id_funcionario = :uid1
            )";
            $where_clauses[] = "c.idfuncionario_enturno != :uid2";
            $params[':uid1'] = $usuario_id;
            $params[':uid2'] = $usuario_id;
        } else {
            // Por defecto 'entrantes': Bandeja de Entrada, derivados al usuario
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Derivado'";
            $params[':uid'] = $usuario_id;
        }
    } else {
        // Otros roles (fallback general): ven las que les fueron derivadas en algún momento
        $where_clauses[] = "EXISTS (
            SELECT 1 FROM derivacion d2
            WHERE d2.id_correspondencia = c.id
              AND d2.id_funcionario = :uid
        )";
        $params[':uid'] = $usuario_id;
    }
 
    $sql = $base_sql . " WHERE " . implode(" AND ", $where_clauses) . " ORDER BY c.fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
 
    $correspondencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    // Calcular contadores para pestañas
    $counts = [];
    if (in_array($usuario_cargo, ['Gerente', 'Administrativo'])) {
        $stmt_c1 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Derivado' AND idfuncionario_enturno = :uid AND eliminado_en IS NULL");
        $stmt_c1->execute([':uid' => $usuario_id]);
        $counts['entrantes'] = $stmt_c1->fetchColumn();

        $stmt_c2 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Aceptado' AND idfuncionario_enturno = :uid AND eliminado_en IS NULL");
        $stmt_c2->execute([':uid' => $usuario_id]);
        $counts['pendientes'] = $stmt_c2->fetchColumn();

        $stmt_c3 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia c WHERE EXISTS (SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :uid1) AND c.idfuncionario_enturno != :uid2 AND c.eliminado_en IS NULL");
        $stmt_c3->execute([':uid1' => $usuario_id, ':uid2' => $usuario_id]);
        $counts['despachados'] = $stmt_c3->fetchColumn();

        if ($usuario_cargo === 'Administrativo') {
            $stmt_c4 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE remitente_id = :uid AND eliminado_en IS NULL");
            $stmt_c4->execute([':uid' => $usuario_id]);
            $counts['iniciados'] = $stmt_c4->fetchColumn();
        }
        if ($usuario_cargo === 'Gerente') {
            $stmt_g1 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Iniciado' AND eliminado_en IS NULL");
            $stmt_g1->execute();
            $counts['para_iniciar'] = $stmt_g1->fetchColumn();
        }
    } elseif ($usuario_cargo === 'Administrador') {
        $stmt_c = $pdo->query("SELECT estado, COUNT(*) as total FROM correspondencia WHERE eliminado_en IS NULL GROUP BY estado");
        $estado_counts = $stmt_c->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $counts['todos'] = array_sum($estado_counts);
        $counts['registrado'] = $estado_counts['Registrado'] ?? 0;
        $counts['iniciado'] = $estado_counts['Iniciado'] ?? 0;
        $counts['derivado'] = $estado_counts['Derivado'] ?? 0;
        $counts['aceptado'] = $estado_counts['Aceptado'] ?? 0;
    } elseif ($usuario_cargo === 'Secretaria') {
        $stmt_c = $pdo->query("SELECT estado, COUNT(*) as total FROM correspondencia WHERE eliminado_en IS NULL GROUP BY estado");
        $estado_counts = $stmt_c->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $counts['rechazados'] = $estado_counts['Rechazado'] ?? 0;
        $counts['todos'] = array_sum($estado_counts) - $counts['rechazados'];
    }
 
    $data = array();
    foreach ($correspondencias as $correspondencia) {
        $acciones = '';

        // Foto (thumbnail clickeable si existe)
        $fotoHtml = '';
        if (!empty($correspondencia['foto'])) {
            $urlFoto = '../assets/fotos_correspondencia/' . $correspondencia['foto'];
            $fotoHtml = '<a href="#" onclick="verFoto(\'' . $urlFoto . '\'); return false;"><img src="' . $urlFoto . '" alt="Foto" style="height:40px;" class="rounded border"></a>';
        }

        // Verificar si está retrasado (estado Aceptado por más de 2 días)
        $es_retrasado = false;
        if ($correspondencia['estado'] === 'Aceptado' && !empty($correspondencia['fecha_referencia'])) {
            $dias_pasados = floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($correspondencia['fecha_referencia'])))) / 86400);
            if ($dias_pasados >= 2) {
                $es_retrasado = true;
            }
        }

        // Formatear el estado con el nombre del funcionario
        $estado_texto = $correspondencia['estado'];
        $nombre_enturno = trim(($correspondencia['nombre'] ?? '') . ' ' . ($correspondencia['paterno'] ?? '') . ' ' . ($correspondencia['materno'] ?? ''));

        if (!empty($nombre_enturno)) {
            if ($correspondencia['estado'] === 'Aceptado') {
                $estado_texto = 'Aceptado por';
            } elseif ($correspondencia['estado'] === 'Derivado') {
                $estado_texto = 'Derivado a';
            } elseif ($correspondencia['estado'] === 'Iniciado') {
                $estado_texto = 'Iniciado para';
        } elseif ($correspondencia['estado'] === 'Rechazado') {
            $estado_texto = 'Rechazado por';
            }
        }

        $estado_display = '<span class="fw-bold">' . $estado_texto . '</span>';

        // Añadir indicador (punto verde o rojo) si el documento está en poder de este funcionario
        if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
            if ($es_retrasado) {
                $estado_display .= ' <span class="badge bg-danger blink ms-1" title="Retrasado (más de 2 días)">&bull;</span>';
            } else {
                $estado_display .= ' <span class="badge bg-success blink ms-1" title="En su poder">&bull;</span>';
            }
        }

        if (!empty($nombre_enturno) && in_array($correspondencia['estado'], ['Aceptado', 'Derivado', 'Iniciado', 'Rechazado'])) {
            $color_clase = $correspondencia['estado'] === 'Rechazado' ? 'text-danger' : ($correspondencia['estado'] === 'Aceptado' ? 'text-primary' : ($correspondencia['estado'] === 'Derivado' ? 'text-success' : 'text-info'));
            $estado_display .= '<br><small class="' . $color_clase . ' fw-semibold">' . htmlspecialchars($nombre_enturno) . '</small>';
        }

        // --- SISTEMA DE BOTONES POR ROL ---
        $btn_aceptar = '<button type="button" class="btn btn-dark btn-sm" style="margin-left:4px;" title="Aceptar/Rechazar" onclick="abrirAceptarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-check-circle"></i></button>';

        $btn_rechazar = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Rechazar" onclick="abrirRechazarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-x-circle"></i></button>';
        $btn_editar = '<form action="" method="post" style="display: inline;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button></form>';
        $btn_eliminar = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Eliminar" onclick="confirmarEliminacion('.$correspondencia['id'].')"><i class="bi bi-trash"></i></button>';
        $btn_iniciar = '<form action="create.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-primary btn-sm" title="Iniciar"><i class="bi bi-play-circle"></i></button></form>';
        $btn_derivar = '<form action="" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-success btn-sm" title="Derivar" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button></form>';
        $btn_historial = '<form action="../derivacion/index.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-info btn-sm" title="Ver historial de derivaciones"><i class="bi bi-list-ul"></i></button></form>';
        $btn_imprimir = '<button type="button" class="btn btn-secondary btn-sm ms-1" style="margin-left:4px;" title="Imprimir" onclick="solicitarPagina('.$correspondencia['id'].')"><i class="bi bi-printer"></i></button>';

        $estado = $correspondencia['estado'];

        if ($usuario_cargo === 'Administrador') {
            // Administrador puede editar y eliminar en cualquier etapa
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
        } else if ($usuario_cargo === 'Secretaria') {
            // Secretaria ve todo. Acciones: editar, eliminar, historial. Iniciar solo si es 'Registrado'.
            $acciones .= $btn_editar . $btn_eliminar;
            if ($estado === 'Registrado') {
                $acciones .= $btn_iniciar;
            }
            $acciones .= $btn_historial;
            $acciones .= $btn_imprimir;
        } else if (in_array($usuario_cargo, ['Gerente', 'Administrativo'])) {
            if ($estado === 'Iniciado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                    $acciones .= $btn_rechazar;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Derivado') {
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
            'anexo' => $correspondencia['anexo'],
            'foto' => $fotoHtml,
            'fecha' => date('d-m-Y H:i:s', strtotime($correspondencia['fecha'])),
            'estado' => $estado_display,
            'acciones' => $acciones
        );
    }
    echo json_encode(array("data" => $data, "counts" => $counts));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>