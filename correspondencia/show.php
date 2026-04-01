<?php
session_start();
require '../db.php';

try {
    // determinar usuario actual y su rol
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_cargo = $_SESSION['usuario_cargo'] ?? null;
 
    // Filtro desde la URL (o POST), para soportar menú y pestañas
    $filtro = $_REQUEST['filtro'] ?? null;
 
    $base_sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.foto, c.estado, c.idfuncionario_enturno, c.anexo, 
                 c.agrupado_en, madre.hojaruta as hojaruta_madre, c.fecha_registro, c.fecha_conclusion,
                 COALESCE(
                     (SELECT MAX(fecha_entrega_derivacion) FROM derivacion WHERE id_correspondencia = c.id AND id_funcionario = c.idfuncionario_enturno), 
                     c.actualizado_en, 
                     c.fecha_registro
                 ) as fecha_referencia,
                 f.nombre, f.paterno, f.materno 
                 FROM correspondencia c
                 LEFT JOIN funcionario f ON c.idfuncionario_enturno = f.id
                 LEFT JOIN correspondencia madre ON c.agrupado_en = madre.id";
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
            case 'concluido':
                $where_clauses[] = "c.estado = 'Concluido'";
                break;
            case 'archivado':
                $where_clauses[] = "c.estado = 'Archivado'";
                break;
            case 'agrupado':
                $where_clauses[] = "c.estado = 'Agrupado'";
                break;
            // 'todos' o null no añade filtro de estado, muestra todo
        }
    } else if ($usuario_cargo === 'Secretaria') {
        if ($filtro === 'no_cursadas') {
            $where_clauses[] = "c.estado = 'No cursada'";
        } else {
            $where_clauses[] = "c.estado != 'No cursada'";
        }
    } else if ($usuario_cargo === 'Archivista Central') {
        $filtro = $filtro ?? 'entrantes';
        if ($filtro === 'pendientes') {
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Aceptado'";
            $params[':uid'] = $usuario_id;
        } elseif ($filtro === 'archivo_central') {
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Archivado'";
            $params[':uid'] = $usuario_id;
        } else {
            // 'entrantes' por defecto
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Derivado'";
            $params[':uid'] = $usuario_id;
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
        } elseif ($filtro === 'concluidos') {
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Concluido'";
            $params[':uid'] = $usuario_id;
        } elseif ($filtro === 'archivo_central') {
            // Bandeja de Archivo Central: Todos los archivados de la empresa
            $where_clauses[] = "c.estado = 'Archivado'";
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
 
    $sql = $base_sql . " WHERE " . implode(" AND ", $where_clauses) . " ORDER BY c.fecha_registro DESC";
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

        $stmt_c5 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Concluido' AND idfuncionario_enturno = :uid AND eliminado_en IS NULL");
        $stmt_c5->execute([':uid' => $usuario_id]);
        $counts['concluidos'] = $stmt_c5->fetchColumn();

        $stmt_c6 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Archivado' AND eliminado_en IS NULL");
        $stmt_c6->execute();
        $counts['archivo_central'] = $stmt_c6->fetchColumn();
    } elseif ($usuario_cargo === 'Administrador') {
        $stmt_c = $pdo->query("SELECT estado, COUNT(*) as total FROM correspondencia WHERE eliminado_en IS NULL GROUP BY estado");
        $estado_counts = $stmt_c->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $counts['todos'] = array_sum($estado_counts);
        $counts['registrado'] = $estado_counts['Registrado'] ?? 0;
        $counts['iniciado'] = $estado_counts['Iniciado'] ?? 0;
        $counts['derivado'] = $estado_counts['Derivado'] ?? 0;
        $counts['aceptado'] = $estado_counts['Aceptado'] ?? 0;
        $counts['concluido'] = $estado_counts['Concluido'] ?? 0;
        $counts['archivado'] = $estado_counts['Archivado'] ?? 0;
        $counts['agrupado'] = $estado_counts['Agrupado'] ?? 0;
    } elseif ($usuario_cargo === 'Archivista Central') {
        $stmt_a1 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Derivado' AND idfuncionario_enturno = :uid AND eliminado_en IS NULL");
        $stmt_a1->execute([':uid' => $usuario_id]);
        $counts['entrantes'] = $stmt_a1->fetchColumn();

        $stmt_a2 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Aceptado' AND idfuncionario_enturno = :uid AND eliminado_en IS NULL");
        $stmt_a2->execute([':uid' => $usuario_id]);
        $counts['pendientes'] = $stmt_a2->fetchColumn();

        $stmt_a3 = $pdo->prepare("SELECT COUNT(*) FROM correspondencia WHERE estado = 'Archivado' AND idfuncionario_enturno = :uid AND eliminado_en IS NULL");
        $stmt_a3->execute([':uid' => $usuario_id]);
        $counts['archivo_central'] = $stmt_a3->fetchColumn();
    } elseif ($usuario_cargo === 'Secretaria') {
        $stmt_c = $pdo->query("SELECT estado, COUNT(*) as total FROM correspondencia WHERE eliminado_en IS NULL GROUP BY estado");
        $estado_counts = $stmt_c->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $counts['no_cursadas'] = $estado_counts['No cursada'] ?? 0;
        $counts['todos'] = array_sum($estado_counts) - $counts['no_cursadas'];
    }
 
    $data = array();
    foreach ($correspondencias as $correspondencia) {
        $acciones = '';

        // Foto o Archivo (thumbnail o icono si existe)
        $fotoHtml = '';
        if (!empty($correspondencia['foto'])) {
            $urlFoto = '../assets/fotos_correspondencia/' . $correspondencia['foto'];
            $ext = strtolower(pathinfo($correspondencia['foto'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $fotoHtml = '<a href="' . $urlFoto . '" target="_blank" class="text-danger text-decoration-none" title="Ver documento PDF"><i class="bi bi-file-earmark-pdf-fill" style="font-size: 2rem;"></i></a>';
            } else {
                $fotoHtml = '<a href="#" onclick="verFoto(\'' . $urlFoto . '\'); return false;"><img src="' . $urlFoto . '" alt="Foto" style="height:40px;" class="rounded border"></a>';
            }
        }

        // Verificar si está retrasado (estado Aceptado por más de 5 días)
        $es_retrasado = false;
        if ($correspondencia['estado'] === 'Aceptado' && !empty($correspondencia['fecha_referencia'])) {
            $dias_pasados = floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($correspondencia['fecha_referencia'])))) / 86400);
            if ($dias_pasados >= 5) {
                $es_retrasado = true;
            }
        }

        // Formatear el estado con el nombre del funcionario
        $estado = $correspondencia['estado'];
        $estado_display = '';

        if ($estado === 'Agrupado') {
            $estado_texto = 'Agrupado';
            if (!empty($correspondencia['hojaruta_madre'])) {
                $estado_texto .= ' en H.R. ' . htmlspecialchars($correspondencia['hojaruta_madre']);
            }
            $estado_display = '<span class="fw-bold">' . $estado_texto . '</span>';
            $estado_display .= '<br><small class="text-secondary fw-semibold">Trámite concluido</small>';
        } else {
            // Si el estado es Concluido, mostrar la fecha de conclusión
            if ($estado === 'Concluido' && !empty($correspondencia['fecha_conclusion'])) {
                $estado_display .= '<br><small class="text-secondary fw-semibold">Concluido el: ' . date('d-m-Y H:i:s', strtotime($correspondencia['fecha_conclusion'])) . '</small>';
            }

            $estado_texto = $estado;
            $nombre_enturno = trim(($correspondencia['nombre'] ?? '') . ' ' . ($correspondencia['paterno'] ?? '') . ' ' . ($correspondencia['materno'] ?? ''));
            $es_dueno = ($correspondencia['idfuncionario_enturno'] == $usuario_id);

            if (!empty($nombre_enturno)) {
                $prefijos = [
                    'Aceptado'   => 'Aceptado por ',
                    'Derivado'   => 'Derivado a ',
                    'Iniciado'   => 'Iniciado para ',
                    'Rechazado'  => 'Rechazado por ',
                    'Concluido'  => 'Concluido por ',
                    'No cursada' => 'No cursada por ',                    
                    'Archivado'  => 'Archivado por ',
                    'Pendiente Aprobación Archivo' => 'En revisión por ',
                    'Pendiente Archivo' => 'Por archivar '
                ];
                if (isset($prefijos[$estado])) {
                    $estado_texto = $prefijos[$estado];
                }
            }

            $estado_display = '<span class="fw-bold">' . $estado_texto . '</span>';

            // Añadir indicador (punto verde o rojo) si el documento está en poder de este funcionario
            if ($es_dueno) {
                $estado_display .= $es_retrasado ? ' <span class="badge bg-danger blink ms-1" title="Retrasado (más de 5 días)">&bull;</span>' 
                                                 : ' <span class="badge bg-success blink ms-1" title="En su poder">&bull;</span>';
            }

            if (!empty($nombre_enturno) && in_array($estado, ['Aceptado', 'Derivado', 'Iniciado', 'Rechazado', 'No cursada', 'Concluido', 'Archivado', 'Pendiente Aprobación Archivo', 'Pendiente Archivo'])) {
                $colores = ['Rechazado' => 'text-danger', 'No cursada' => 'text-danger', 'Aceptado' => 'text-primary', 'Derivado' => 'text-success', 'Concluido' => 'text-secondary', 'Archivado' => 'text-dark', 'Iniciado' => 'text-info', 'Pendiente Aprobación Archivo' => 'text-warning', 'Pendiente Archivo' => 'text-warning'];
                $color_clase = $colores[$estado] ?? 'text-info';
                $estado_display .= '<br><small class="' . $color_clase . ' fw-semibold">' . htmlspecialchars($nombre_enturno) . '</small>';
            }
        }

        // --- SISTEMA DE BOTONES POR ROL ---
        $btn_aceptar = '<button type="button" class="btn btn-success btn-sm" style="margin-left:4px;" title="Aceptar" onclick="abrirAceptarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-check-circle"></i></button>';

        $btn_rechazar = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Rechazar" onclick="abrirRechazarCorrespondencia('.$correspondencia['id'].', \'Rechazado\')"><i class="bi bi-x-circle"></i></button>';
        $btn_no_cursada = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Marcar como No Cursada" onclick="abrirRechazarCorrespondencia('.$correspondencia['id'].', \'No cursada\')"><i class="bi bi-slash-circle"></i></button>';
        $btn_devolver = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Devolver al remitente" onclick="abrirModalDevolucion('.$correspondencia['id'].')"><i class="bi bi-arrow-return-left"></i></button>';
        $btn_editar = '<form action="" method="post" style="display: inline;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button></form>';
        $btn_eliminar = '<button type="button" class="btn btn-danger btn-sm" style="margin-left:4px;" title="Eliminar" onclick="confirmarEliminacion('.$correspondencia['id'].')"><i class="bi bi-trash"></i></button>';
        $btn_iniciar = '<form action="create.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-primary btn-sm" title="Iniciar"><i class="bi bi-play-circle"></i></button></form>';
        $btn_derivar = '<form action="" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-success btn-sm" title="Derivar" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button></form>';
        $btn_historial = '<form action="../derivacion/index.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-secondary btn-sm" title="Ver historial de derivaciones"><i class="bi bi-list-ul"></i></button></form>';
        $btn_imprimir = '<button type="button" class="btn btn-info btn-sm ms-1" style="margin-left:4px;" title="Imprimir" onclick="solicitarPagina('.$correspondencia['id'].')"><i class="bi bi-printer"></i></button>';
        $btn_concluir = '<button type="button" class="btn btn-outline-dark btn-sm" style="margin-left:4px;" title="Concluir trámite" onclick="abrirConcluirCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-check2-circle"></i></button>';
        $btn_ampliacion = '<button type="button" class="btn btn-outline-primary btn-sm" style="margin-left:4px;" title="Solicitar ampliación de plazo (+5 días)" onclick="solicitarAmpliacion('.$correspondencia['id'].')"><i class="bi bi-calendar-plus"></i></button>';
        $btn_desarchivar = '<button type="button" class="btn btn-outline-success btn-sm" style="margin-left:4px;" title="Desarchivar (Retornar a pendientes)" onclick="abrirDesarchivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-box-arrow-up"></i></button>';
        $btn_agrupar = '<button type="button" class="btn btn-info btn-sm" style="margin-left:4px;" title="Agrupar con otra correspondencia" onclick="abrirModalAgrupar('.$correspondencia['id'].')"><i class="bi bi-folder-symlink"></i></button>';
        $btn_solicitar_archivo = '<button type="button" class="btn btn-dark btn-sm" style="margin-left:4px;" title="Solicitar envío a Archivo Central" onclick="abrirSolicitarArchivo('.$correspondencia['id'].')"><i class="bi bi-archive-fill"></i></button>';

        $estado = $correspondencia['estado'];
        if ($estado === 'Agrupado') {
            // Para correspondencias agrupadas, solo se puede ver el historial.
            $acciones .= $btn_historial;

        } else if ($usuario_cargo === 'Administrador') {
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
                    $acciones .= $btn_agrupar;
                    if ($es_retrasado) {
                        $acciones .= $btn_ampliacion;
                    }
                }
                $acciones .= $btn_historial;
                $acciones .= $btn_imprimir;
            } else {
                // Otros estados: conservar lógica anterior
                if ($estado === 'Archivado') {
                    $acciones .= $btn_desarchivar;
                } elseif ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
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
            if ($estado === 'Archivado' && $correspondencia['idfuncionario_enturno'] == $usuario_id) {
                $acciones .= $btn_desarchivar;
            }
            $acciones .= $btn_historial;
            $acciones .= $btn_imprimir;
        } else if ($usuario_cargo === 'Archivista Central') {
            if ($estado === 'Derivado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_aceptar;
                    $acciones .= $btn_devolver;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Aceptado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                    $acciones .= $btn_concluir;
                    $acciones .= $btn_agrupar;
                    if ($es_retrasado) {
                        $acciones .= $btn_ampliacion;
                    }
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Concluido') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_solicitar_archivo;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Archivado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_desarchivar;
                }
                $acciones .= $btn_historial;
            }
        } else if (in_array($usuario_cargo, ['Gerente', 'Administrativo'])) {
            if ($estado === 'Iniciado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                    $acciones .= ($usuario_cargo === 'Gerente') ? $btn_no_cursada : $btn_rechazar;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Derivado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_aceptar;
                    $acciones .= $btn_devolver;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Aceptado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                    $acciones .= $btn_concluir;
                    $acciones .= $btn_agrupar;
                    if ($es_retrasado) {
                        $acciones .= $btn_ampliacion;
                    }
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Concluido') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_solicitar_archivo;
                }
                $acciones .= $btn_historial;
            } elseif ($estado === 'Archivado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_desarchivar;
                }
                $acciones .= $btn_historial;
            } else {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
            }
        }

        // Construir la cadena de fecha/hora para mostrar en la tabla
        $fecha_display = '<span class="text-nowrap"><strong>Registro:</strong> ' . date('d-m-Y H:i:s', strtotime($correspondencia['fecha_registro'])) . '</span>';
        if ($correspondencia['estado'] === 'Concluido' && !empty($correspondencia['fecha_conclusion'])) {
            $fecha_display .= '<br><span class="text-nowrap text-primary"><strong>Conclusión:</strong> ' . date('d-m-Y H:i:s', strtotime($correspondencia['fecha_conclusion'])) . '</span>';
        }

        $data[] = array(
            'hojaruta' => $correspondencia['hojaruta'],
            'remitente' => $correspondencia['remitente'],
            'referencia' => $correspondencia['referencia'],
            'fojas' => $correspondencia['fojas'],
            'anexo' => $correspondencia['anexo'],
            'foto' => $fotoHtml,
            'fecha' => $fecha_display,
            'estado' => $estado_display,
            'acciones' => $acciones
        );
    }
    echo json_encode(array("data" => $data, "counts" => $counts));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>