<?php
session_start();
require '../db.php';

try {
    // determinar usuario actual y su rol
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_cargo = $_SESSION['usuario_cargo'] ?? null;
 
    // Filtro desde la URL (o POST), para soportar menú y pestañas
    $filtro = $_REQUEST['filtro'] ?? null;
 
    $base_sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.fecha, c.estado, c.idfuncionario_enturno, c.anexo FROM correspondencia c";
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
        // Secretaria ve solo correspondencias con estado 'Registrado'
        $where_clauses[] = "c.estado = 'Registrado'";
    } else if ($usuario_cargo === 'Gerente') {
        // Gerente ve solo correspondencias con estado 'Iniciado'
        $where_clauses[] = "c.estado = 'Iniciado'";
    } else if ($usuario_cargo === 'Administrativo') {
        // El rol Administrativo usa pestañas para diferentes bandejas
        $filtro = $filtro ?? 'entrantes'; // Filtro por defecto: Bandeja de Entrada
 
        if ($filtro === 'iniciados') {
            // Bandeja de Salida: Correspondencias iniciadas por el usuario
            $where_clauses[] = "c.remitente_id = :uid";
            $params[':uid'] = $usuario_id;
        } elseif ($filtro === 'pendientes') {
            // Bandeja de Pendientes: Aceptados y en poder del usuario
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Aceptado'";
            $params[':uid'] = $usuario_id;
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
            // Secretaria solo ve 'Registrado'. Acciones: editar, eliminar, iniciar, historial.
            $acciones .= $btn_editar . $btn_eliminar;
            if ($estado === 'Registrado') {
                $acciones .= $btn_iniciar;
                // El historial estará vacío, pero se añade por petición.
                $acciones .= $btn_historial;
            }
        } else if ($usuario_cargo === 'Gerente') {
            // Gerente solo ve 'Iniciado'. Acciones: derivar, historial.
            if ($estado === 'Iniciado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
            }
        } else if ($usuario_cargo === 'Administrativo') {
            if ($estado === 'Derivado') {
                // Bandeja de Entrantes: Aceptar/Rechazar, Historial
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_aceptar;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Aceptado') {
                // Bandeja de Pendientes: Derivar, Historial
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
            } else {
                // Bandeja de Salida (Iniciados) y otros: Derivar (si es dueño), Historial
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
    echo json_encode(array("data" => $data));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>