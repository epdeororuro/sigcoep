<?php
session_start();
require '../db.php';

try {
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    $filtro = $_POST['filtro'] ?? 'entrantes';

    // 1. Obtener los contadores para las burbujas (badges)
    $sql_counts = "SELECT 
        (SELECT COUNT(*) FROM derivacion_grupo_detalle dgd JOIN derivacion_grupo dg ON dgd.derivacion_grupo_id = dg.id WHERE dgd.funcionario_id = :u1 AND dgd.estado = 'pendiente' AND dg.estado IN ('activo', 'en_proceso')) as entrantes,
        (SELECT COUNT(*) FROM derivacion_grupo_detalle dgd JOIN derivacion_grupo dg ON dgd.derivacion_grupo_id = dg.id WHERE dgd.funcionario_id = :u2 AND dgd.estado IN ('aceptado', 'rechazado') AND dg.estado IN ('activo', 'en_proceso')) as aceptados,
        (SELECT COUNT(*) FROM derivacion_grupo_detalle dgd JOIN derivacion_grupo dg ON dgd.derivacion_grupo_id = dg.id WHERE dgd.funcionario_id = :u3 AND dgd.estado IN ('enviado', 'aprobado') AND dg.responsable_id != dgd.funcionario_id) as enviados,
        (SELECT COUNT(*) FROM derivacion_grupo dg WHERE dg.responsable_id = :u4 AND dg.estado IN ('activo', 'en_proceso')) as supervision,
        (SELECT COUNT(*) FROM derivacion_grupo dg WHERE dg.creado_por = :u5 AND dg.estado IN ('activo', 'en_proceso')) as monitoreo
    ";
    $stmt_counts = $pdo->prepare($sql_counts);
    $stmt_counts->execute([':u1' => $usuario_id, ':u2' => $usuario_id, ':u3' => $usuario_id, ':u4' => $usuario_id, ':u5' => $usuario_id]);
    $counts = $stmt_counts->fetch(PDO::FETCH_ASSOC);

    // 2. Construir la consulta según la pestaña seleccionada
    $base_sql = "
        SELECT 
            c.id as correspondencia_id, c.hojaruta, c.referencia,
            dg.id as grupo_id, dg.fecha_limite, dg.estado as estado_grupo,
            dgd.estado as estado_detalle, dgd.id as detalle_id,
            f.nombre, f.paterno, p.sigla as puesto_sigla,
            i.archivo_adjunto, i.contenido, i.observaciones
        FROM derivacion_grupo dg
        JOIN correspondencia c ON dg.correspondencia_id = c.id
        JOIN derivacion_grupo_detalle dgd ON dg.id = dgd.derivacion_grupo_id
        LEFT JOIN funcionario f ON dg.creado_por = f.id
        LEFT JOIN puesto p ON f.id_puesto = p.id
        LEFT JOIN informes i ON i.derivacion_grupo_detalle_id = dgd.id
    ";

    $where = [];
    $params = [];

    if ($filtro === 'entrantes') {
        $where[] = "dgd.funcionario_id = :uid";
        $where[] = "dgd.estado = 'pendiente'";
        $where[] = "dg.estado IN ('activo', 'en_proceso')";
        $params[':uid'] = $usuario_id;
    } elseif ($filtro === 'aceptados') {
        $where[] = "dgd.funcionario_id = :uid";
        $where[] = "dgd.estado IN ('aceptado', 'rechazado')";
        $where[] = "dg.estado IN ('activo', 'en_proceso')";
        $params[':uid'] = $usuario_id;
    } elseif ($filtro === 'enviados') {
        $where[] = "dgd.funcionario_id = :uid";
        $where[] = "dgd.estado IN ('enviado', 'aprobado')";
        $where[] = "dg.responsable_id != dgd.funcionario_id"; // Ocultarlo de aquí si él es el responsable
        $params[':uid'] = $usuario_id;
    } elseif ($filtro === 'supervision') {
        // Si es el supervisor, solo debe ver UN registro por Grupo, no duplicado por participante
        // PERO unimos su propio detalle e informe para permitirle editar su propio trabajo
        $base_sql = "
            SELECT 
                c.id as correspondencia_id, c.hojaruta, c.referencia,
                dg.id as grupo_id, dg.fecha_limite, dg.estado as estado_grupo,
                'Líder' as estado_detalle, dgd_lider.id as detalle_id,
                f.nombre, f.paterno, p.sigla as puesto_sigla,
                i_lider.archivo_adjunto, i_lider.contenido, i_lider.observaciones
            FROM derivacion_grupo dg
            JOIN correspondencia c ON dg.correspondencia_id = c.id
            LEFT JOIN funcionario f ON dg.creado_por = f.id
            LEFT JOIN puesto p ON f.id_puesto = p.id
            LEFT JOIN derivacion_grupo_detalle dgd_lider ON dg.id = dgd_lider.derivacion_grupo_id AND dgd_lider.funcionario_id = :uid_lider
            LEFT JOIN informes i_lider ON i_lider.derivacion_grupo_detalle_id = dgd_lider.id
        ";
        $where[] = "dg.responsable_id = :uid";
        $where[] = "dg.estado IN ('activo', 'en_proceso')";
        $params[':uid'] = $usuario_id;
        $params[':uid_lider'] = $usuario_id;
    } elseif ($filtro === 'monitoreo') {
        // El Gerente que lo creó monitorea quién está de responsable y su avance
        $base_sql = "
            SELECT 
                c.id as correspondencia_id, c.hojaruta, c.referencia,
                dg.id as grupo_id, dg.fecha_limite, dg.estado as estado_grupo,
                'Líder' as estado_detalle, 0 as detalle_id,
                f.nombre, f.paterno, p.sigla as puesto_sigla,
                NULL as archivo_adjunto, NULL as contenido, NULL as observaciones
            FROM derivacion_grupo dg
            JOIN correspondencia c ON dg.correspondencia_id = c.id
            LEFT JOIN funcionario f ON dg.responsable_id = f.id
            LEFT JOIN puesto p ON f.id_puesto = p.id
        ";
        $where[] = "dg.creado_por = :uid";
        $where[] = "dg.estado IN ('activo', 'en_proceso')";
        $params[':uid'] = $usuario_id;
    }

    $sql = $base_sql . " WHERE " . implode(" AND ", $where) . " ORDER BY dg.fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparamos la consulta para buscar a los integrantes de cada grupo
    $stmt_integrantes = $pdo->prepare("
        SELECT f.nombre, f.paterno, dgd.es_principal, dgd.estado, dgd.id as detalle_id, i.contenido, i.archivo_adjunto 
        FROM derivacion_grupo_detalle dgd 
        JOIN funcionario f ON dgd.funcionario_id = f.id 
        LEFT JOIN informes i ON i.derivacion_grupo_detalle_id = dgd.id
        WHERE dgd.derivacion_grupo_id = ?
        ORDER BY dgd.es_principal DESC, f.nombre ASC
    ");

    $data = [];
    foreach ($resultados as $row) {
        $creador = htmlspecialchars(trim($row['nombre'] . ' ' . $row['paterno'])) . '<br><small class="text-muted">' . htmlspecialchars($row['puesto_sigla']) . '</small>';
        
        $fecha_limite = !empty($row['fecha_limite']) ? date('d-m-Y H:i', strtotime($row['fecha_limite'])) : '<span class="text-muted">Sin límite</span>';
        if (!empty($row['fecha_limite']) && strtotime($row['fecha_limite']) < time() && in_array($row['estado_detalle'], ['pendiente'])) {
            $fecha_limite = '<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Vencido<br>'.$fecha_limite.'</span>';
        }

        // Obtener la lista de integrantes
        $stmt_integrantes->execute([$row['grupo_id']]);
        $ints = $stmt_integrantes->fetchAll(PDO::FETCH_ASSOC);
        $integrantes_html = '<ul class="list-unstyled mb-0 text-start" style="font-size: 0.85rem;">';
        
        $total_integrantes = count($ints);
        $integrantes_completados = 0;

        foreach($ints as $int) {
            $badge = $int['es_principal'] ? ' <span class="badge bg-warning text-dark p-1" title="Responsable / Líder"><i class="bi bi-star-fill"></i></span>' : '';
            
            $icono_estado = '<i class="bi bi-person text-secondary"></i>';
            $accion_revisar = '';
            
            if ($int['estado'] === 'pendiente') {
                $icono_estado = '<i class="bi bi-clock text-danger" title="Pendiente de revisión"></i>';
            } elseif ($int['estado'] === 'aceptado') {
                $icono_estado = '<i class="bi bi-check-circle text-primary" title="Tarea aceptada (En elaboración)"></i>';
            } elseif ($int['estado'] === 'enviado') {
                $icono_estado = '<i class="bi bi-info-circle-fill text-info" title="Informe enviado (Pendiente de revisión)"></i>';
                // Botón de revisión solo visible para el líder en la pestaña de Supervisión
                if ($filtro === 'supervision' && !$int['es_principal']) {
                    $js_nombre = htmlspecialchars(json_encode(trim($int['nombre'] . ' ' . $int['paterno'])), ENT_QUOTES, 'UTF-8');
                    $js_contenido = htmlspecialchars(json_encode($int['contenido'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $js_archivo = htmlspecialchars(json_encode($int['archivo_adjunto'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $accion_revisar = ' <a href="#" onclick=\'abrirRevisarInformeUnico('.$int['detalle_id'].', '.$js_nombre.', '.$js_contenido.', '.$js_archivo.'); return false;\' class="badge bg-primary text-white text-decoration-none ms-1 shadow-sm"><i class="bi bi-search"></i> Revisar</a>';
                }
            } elseif ($int['estado'] === 'aprobado') {
                $icono_estado = '<i class="bi bi-check-circle-fill text-success" title="Aprobado por el responsable"></i>';
            } elseif ($int['estado'] === 'rechazado') {
                $icono_estado = '<i class="bi bi-x-circle-fill text-danger" title="Rechazado"></i>';
            }

            // Llenado de barra de progreso: Solo cuenta APROBADOS (El responsable salta directo a este estado)
            if ($int['estado'] === 'aprobado') {
                $integrantes_completados++;
            }

            $integrantes_html .= '<li class="mb-1">' . $icono_estado . ' ' . htmlspecialchars(trim($int['nombre'] . ' ' . $int['paterno'])) . $badge . $accion_revisar . '</li>';
        }
        $integrantes_html .= '</ul>';

        $estado = '';
        $acciones_list = '';

        if ($filtro === 'entrantes') {
            $estado = '<span class="fw-bold">Pendiente</span><br><small class="text-danger fw-semibold">Esperando Aceptación</small>';
            $acciones_list .= '<li><a class="dropdown-item" href="#" onclick="abrirAceptarTarea('.$row['detalle_id'].'); return false;"><i class="bi bi-check-circle text-success me-2"></i> Aceptar Tarea</a></li>';
            $acciones_list .= '<li><a class="dropdown-item" href="#" onclick="abrirRechazarTarea('.$row['detalle_id'].'); return false;"><i class="bi bi-x-circle text-danger me-2"></i> Rechazar</a></li>';
        } elseif ($filtro === 'aceptados') {
            if ($row['estado_detalle'] === 'rechazado') {
                $estado = '<span class="fw-bold text-danger">Observado</span><br><small class="text-danger fw-semibold" title="'.htmlspecialchars($row['observaciones'] ?? '').'"><i class="bi bi-exclamation-triangle"></i> Debe corregir</small>';
                $contenido_js = htmlspecialchars(json_encode($row['contenido'] ?? ''), ENT_QUOTES, 'UTF-8');
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick=\'abrirModalEditarInforme('.$row['correspondencia_id'].', '.$contenido_js.'); return false;\'><i class="bi bi-pencil-square text-warning me-2"></i> Corregir Informe</a></li>';
            } else {
                $estado = '<span class="fw-bold">Aceptado</span><br><small class="text-primary fw-semibold">En Elaboración</small>';
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick="abrirModalSubirInforme('.$row['correspondencia_id'].'); return false;"><i class="bi bi-cloud-upload text-primary me-2"></i> Subir Informe PDF</a></li>';
            }
        } elseif ($filtro === 'enviados') {
            $ruta_archivo = '../assets/informes_grupo/' . $row['archivo_adjunto'];
            
            if ($row['estado_detalle'] === 'aprobado') {
                $estado = '<span class="fw-bold text-success">Aprobado</span><br><small class="text-success fw-semibold"><i class="bi bi-check-all"></i> Revisión superada</small>';
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick="verInforme(\''.$ruta_archivo.'\'); return false;"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Ver Mi Informe</a></li>';
            } else {
                $estado = '<span class="fw-bold">Enviado al Responsable</span><br><small class="text-secondary fw-semibold">Esperando revisión...</small>';
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick="verInforme(\''.$ruta_archivo.'\'); return false;"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Ver Mi Informe</a></li>';
                
                $contenido_js = htmlspecialchars(json_encode($row['contenido'] ?? ''), ENT_QUOTES, 'UTF-8');
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick=\'abrirModalEditarInforme('.$row['correspondencia_id'].', '.$contenido_js.'); return false;\'><i class="bi bi-pencil-square text-warning me-2"></i> Editar Informe</a></li>';
            }
        } elseif ($filtro === 'supervision') {
            $porcentaje = $total_integrantes > 0 ? round(($integrantes_completados / $total_integrantes) * 100) : 0;
            $color_barra = $porcentaje == 100 ? 'bg-success' : 'bg-primary';
            $estado = '<span class="fw-bold">En Proceso</span><br>
                       <div class="progress mt-1 mb-1 shadow" style="height: 18px; border: 2px solid #adb5bd; border-radius: 8px;" title="' . $integrantes_completados . ' de ' . $total_integrantes . ' informes subidos">
                           <div class="progress-bar ' . $color_barra . ' progress-bar-striped progress-bar-animated text-white" role="progressbar" style="width: ' . $porcentaje . '%; font-size: 0.75rem;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"><strong>' . $porcentaje . '%</strong></div>
                       </div>
                       <small class="text-muted fw-semibold">' . $integrantes_completados . ' de ' . $total_integrantes . ' subidos</small>';
            $acciones_list .= '<li><a class="dropdown-item fw-bold" href="#" onclick="abrirRevisarInformes('.$row['grupo_id'].'); return false;"><i class="bi bi-list-check text-warning text-dark me-2"></i> Revisar y Consolidar</a></li>';
            
            // Acciones para que el Responsable pueda ver/editar su propio informe ya auto-aprobado
            if (!empty($row['archivo_adjunto'])) {
                $ruta_archivo = '../assets/informes_grupo/' . $row['archivo_adjunto'];
                $acciones_list .= '<li><hr class="dropdown-divider"></li>';
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick="verInforme(\''.$ruta_archivo.'\'); return false;"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Ver Mi Informe</a></li>';
                
                $contenido_js = htmlspecialchars(json_encode($row['contenido'] ?? ''), ENT_QUOTES, 'UTF-8');
                $acciones_list .= '<li><a class="dropdown-item" href="#" onclick=\'abrirModalEditarInforme('.$row['correspondencia_id'].', '.$contenido_js.'); return false;\'><i class="bi bi-pencil-square text-warning me-2"></i> Editar Mi Informe</a></li>';
            }
        } elseif ($filtro === 'monitoreo') {
            $porcentaje = $total_integrantes > 0 ? round(($integrantes_completados / $total_integrantes) * 100) : 0;
            $color_barra = $porcentaje == 100 ? 'bg-success' : 'bg-primary';
            $estado = '<span class="fw-bold">Supervisando...</span><br>
                       <div class="progress mt-1 mb-1 shadow" style="height: 18px; border: 2px solid #adb5bd; border-radius: 8px;" title="' . $integrantes_completados . ' de ' . $total_integrantes . ' informes subidos">
                           <div class="progress-bar ' . $color_barra . ' progress-bar-striped progress-bar-animated text-white" role="progressbar" style="width: ' . $porcentaje . '%; font-size: 0.75rem;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"><strong>' . $porcentaje . '%</strong></div>
                       </div>
                       <small class="text-muted fw-semibold">' . $integrantes_completados . ' de ' . $total_integrantes . ' Aprobados</small>';
        }

        if ($filtro === 'monitoreo') {
            // Sin botón de opciones, solo lectura
            $acciones = '<span class="text-muted fst-italic"><i class="bi bi-eye"></i> Solo Lectura</span>';
        } else {
            // Renderizado del botón Dropdown idéntico al index principal
            $acciones = '
            <div class="dropdown text-center">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones">
                    <i class="bi bi-gear"></i> Acciones
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 text-start" style="font-size: 0.9rem;">
                    ' . $acciones_list . '
                </ul>
            </div>';
        }

        $data[] = [
            'hojaruta' => $row['hojaruta'],
            'referencia' => htmlspecialchars($row['referencia']),
            'creador' => $creador,
            'integrantes' => $integrantes_html,
            'fecha_limite' => $fecha_limite,
            'estado' => $estado,
            'acciones' => $acciones
        ];
    }

    echo json_encode([
        "data" => $data, 
        "counts" => $counts
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>