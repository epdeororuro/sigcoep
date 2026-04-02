<?php
// Funciones auxiliares para formatear los datos de la tabla en show.php

function obtenerFotoHtml($foto) {
    if (empty($foto)) return '';
    
    $urlFoto = '../assets/fotos_correspondencia/' . $foto;
    $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
    
    if ($ext === 'pdf') {
        return '<a href="' . $urlFoto . '" target="_blank" class="text-danger text-decoration-none" title="Ver documento PDF"><i class="bi bi-file-earmark-pdf-fill" style="font-size: 2rem;"></i></a>';
    }
    return '<a href="#" onclick="verFoto(\'' . $urlFoto . '\'); return false;"><img src="' . $urlFoto . '" alt="Foto" style="height:40px;" class="rounded border"></a>';
}

function obtenerEstadoHtml($correspondencia, $es_retrasado, $usuario_id) {
    $estado = $correspondencia['estado'];
    
    if ($estado === 'Agrupado') {
        $estado_texto = 'Agrupado' . (!empty($correspondencia['hojaruta_madre']) ? ' en H.R. ' . htmlspecialchars($correspondencia['hojaruta_madre']) : '');
        return '<span class="fw-bold">' . $estado_texto . '</span><br><small class="text-secondary fw-semibold">Trámite concluido</small>';
    }

    $estado_texto = $estado;
    $nombre_enturno = trim(($correspondencia['nombre'] ?? '') . ' ' . ($correspondencia['paterno'] ?? '') . ' ' . ($correspondencia['materno'] ?? ''));
    $es_dueno = ($correspondencia['idfuncionario_enturno'] == $usuario_id);

    if (!empty($nombre_enturno)) {
        $prefijos = [
            'Aceptado' => 'Aceptado por ', 'Derivado' => 'Derivado a ', 'Iniciado' => 'Iniciado para ',
            'Rechazado' => 'Rechazado por ', 'Concluido' => 'Concluido por ', 'No cursada' => 'No cursada por ',                    
            'Archivado' => 'Archivado por ', 'Revisión Archivo' => 'En revisión por ', 'Pendiente Archivo' => 'Por archivar '
        ];
        if (isset($prefijos[$estado])) $estado_texto = $prefijos[$estado];
    }

    $html = '<span class="fw-bold">' . $estado_texto . '</span>';

    if ($es_dueno) {
        $html .= $es_retrasado ? ' <span class="badge bg-danger blink ms-1" title="Retrasado (más de 5 días)">&bull;</span>' 
                               : ' <span class="badge bg-success blink ms-1" title="En su poder">&bull;</span>';
    }

    if (!empty($nombre_enturno) && in_array($estado, ['Aceptado', 'Derivado', 'Iniciado', 'Rechazado', 'No cursada', 'Concluido', 'Archivado', 'Revisión Archivo', 'Pendiente Archivo'])) {
        $colores = ['Rechazado' => 'text-danger', 'No cursada' => 'text-danger', 'Aceptado' => 'text-primary', 'Derivado' => 'text-success', 'Concluido' => 'text-secondary', 'Archivado' => 'text-dark', 'Iniciado' => 'text-info', 'Revisión Archivo' => 'text-warning', 'Pendiente Archivo' => 'text-warning'];
        $color_clase = $colores[$estado] ?? 'text-info';
        $html .= '<br><small class="' . $color_clase . ' fw-semibold">' . htmlspecialchars($nombre_enturno) . '</small>';
    }

    return $html;
}

function obtenerAccionesHtml($correspondencia, $usuario_cargo, $usuario_id, $es_retrasado) {
    $id_corr = $correspondencia['id'];
    $estado = $correspondencia['estado'];

    // Elementos base
    $btn_aceptar = '<li><a class="dropdown-item" href="#" onclick="abrirAceptarCorrespondencia('.$id_corr.'); return false;"><i class="bi bi-check-circle text-success me-2"></i> Aceptar</a></li>';
    $btn_rechazar = '<li><a class="dropdown-item" href="#" onclick="abrirRechazarCorrespondencia('.$id_corr.', \'Rechazado\'); return false;"><i class="bi bi-x-circle text-danger me-2"></i> Rechazar</a></li>';
    $btn_no_cursada = '<li><a class="dropdown-item" href="#" onclick="abrirRechazarCorrespondencia('.$id_corr.', \'No cursada\'); return false;"><i class="bi bi-slash-circle text-danger me-2"></i> No Cursada</a></li>';
    $btn_devolver = '<li><a class="dropdown-item" href="#" onclick="abrirModalDevolucion('.$id_corr.'); return false;"><i class="bi bi-arrow-return-left text-danger me-2"></i> Devolver</a></li>';
    $btn_editar = '<li><a class="dropdown-item" href="#" onclick="editarCorrespondencia('.$id_corr.'); return false;"><i class="bi bi-pencil text-warning me-2"></i> Editar</a></li>';
    $btn_eliminar = '<li><a class="dropdown-item" href="#" onclick="confirmarEliminacion('.$id_corr.'); return false;"><i class="bi bi-trash text-danger me-2"></i> Eliminar</a></li>';
    $btn_iniciar = '<li><form action="create.php" method="post" class="m-0"><input type="hidden" name="id" value="'.$id_corr.'"><button type="submit" class="dropdown-item"><i class="bi bi-play-circle text-primary me-2"></i> Iniciar Trámite</button></form></li>';
    $btn_derivar = '<li><a class="dropdown-item" href="#" onclick="derivarCorrespondencia('.$id_corr.'); return false;"><i class="bi bi-arrow-right-circle text-success me-2"></i> Derivar</a></li>';
    $btn_historial = '<li><form action="../derivacion/index.php" method="post" class="m-0"><input type="hidden" name="id" value="'.$id_corr.'"><button type="submit" class="dropdown-item"><i class="bi bi-list-ul text-secondary me-2"></i> Ver Historial</button></form></li>';
    $btn_imprimir = '<li><a class="dropdown-item" href="#" onclick="solicitarPagina('.$id_corr.'); return false;"><i class="bi bi-printer text-info me-2"></i> Imprimir Hoja de Ruta</a></li>';
    $btn_concluir = '<li><a class="dropdown-item" href="#" onclick="abrirConcluirCorrespondencia('.$id_corr.'); return false;"><i class="bi bi-check2-circle text-dark me-2"></i> Concluir Trámite</a></li>';
    $btn_ampliacion = '<li><a class="dropdown-item" href="#" onclick="solicitarAmpliacion('.$id_corr.'); return false;"><i class="bi bi-calendar-plus text-primary me-2"></i> Solicitar Ampliación (+5 días)</a></li>';
    $btn_desarchivar = '<li><a class="dropdown-item" href="#" onclick="abrirDesarchivarCorrespondencia('.$id_corr.'); return false;"><i class="bi bi-box-arrow-up text-success me-2"></i> Desarchivar</a></li>';
    $btn_agrupar = '<li><a class="dropdown-item" href="#" onclick="abrirModalAgrupar('.$id_corr.'); return false;"><i class="bi bi-folder-symlink text-info me-2"></i> Agrupar Trámite</a></li>';
    $btn_solicitar_archivo = '<li><a class="dropdown-item" href="#" onclick="abrirSolicitarArchivo('.$id_corr.'); return false;"><i class="bi bi-archive-fill text-dark me-2"></i> Enviar a Archivo Central</a></li>';
    $btn_aprobar_archivo = '<li><a class="dropdown-item" href="#" onclick="abrirAprobarArchivo('.$id_corr.'); return false;"><i class="bi bi-check-all text-success me-2"></i> Aprobar Archivo</a></li>';
    $btn_archivar_definitivo = '<li><a class="dropdown-item" href="#" onclick="abrirArchivarDefinitivo('.$id_corr.'); return false;"><i class="bi bi-archive-fill text-dark me-2"></i> Archivar Físicamente</a></li>';

    $acciones_list = '';
    $es_dueno = ($correspondencia['idfuncionario_enturno'] == $usuario_id);

    if ($estado === 'Agrupado') {
        $acciones_list .= $btn_historial;
    } else if ($usuario_cargo === 'Administrador') {
        $acciones_list .= $btn_editar . $btn_eliminar;
        if ($estado === 'Registrado') $acciones_list .= $btn_iniciar;
        elseif ($estado === 'Derivado') {
            if ($es_dueno) $acciones_list .= $btn_aceptar;
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Aceptado') {
            if ($es_dueno) {
                $acciones_list .= $btn_derivar . $btn_agrupar . ($es_retrasado ? $btn_ampliacion : '');
            }
            $acciones_list .= $btn_historial;
        } else {
            if ($estado === 'Archivado') $acciones_list .= $btn_desarchivar;
            elseif (in_array($estado, ['Revisión Archivo', 'Revision Archivo', 'Pendiente Aprobación Archivo'])) {
                if ($es_dueno) $acciones_list .= $btn_aprobar_archivo . $btn_devolver;
            } elseif ($es_dueno) $acciones_list .= $btn_derivar;
            $acciones_list .= $btn_historial;
        }
    } else if ($usuario_cargo === 'Secretaria') {
        $acciones_list .= $btn_editar . $btn_eliminar;
        if ($estado === 'Registrado') $acciones_list .= $btn_iniciar;
        if ($estado === 'Archivado' && $es_dueno) $acciones_list .= $btn_desarchivar;
        $acciones_list .= $btn_historial;
    } else if ($usuario_cargo === 'Archivista Central') {
        if ($estado === 'Derivado') {
            if ($es_dueno) $acciones_list .= $btn_aceptar . $btn_devolver;
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Aceptado') {
            if ($es_dueno) $acciones_list .= $btn_archivar_definitivo;
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Concluido') {
            if ($es_dueno) $acciones_list .= $btn_solicitar_archivo;
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Archivado') {
            if ($es_dueno) $acciones_list .= $btn_desarchivar;
            $acciones_list .= $btn_historial;
        }
    } else if (in_array($usuario_cargo, ['Gerente', 'Administrativo'])) {
        if ($estado === 'Iniciado') {
            if ($es_dueno) $acciones_list .= $btn_derivar . (($usuario_cargo === 'Gerente') ? $btn_no_cursada : $btn_rechazar);
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Derivado') {
            if ($es_dueno) $acciones_list .= $btn_aceptar . $btn_devolver;
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Aceptado') {
            if ($es_dueno) {
                $acciones_list .= $btn_derivar . $btn_concluir . $btn_agrupar . ($es_retrasado ? $btn_ampliacion : '');
            }
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Concluido') {
            if ($es_dueno) $acciones_list .= $btn_solicitar_archivo;
            $acciones_list .= $btn_historial;
        } elseif ($estado === 'Archivado') {
            if ($es_dueno) $acciones_list .= $btn_desarchivar;
            $acciones_list .= $btn_historial;
        } elseif (in_array($estado, ['Revisión Archivo', 'Revision Archivo', 'Pendiente Aprobación Archivo'])) {
            if ($es_dueno) $acciones_list .= $btn_aprobar_archivo . $btn_devolver;
            $acciones_list .= $btn_historial;
        } else {
            if ($es_dueno) $acciones_list .= $btn_derivar;
            $acciones_list .= $btn_historial;
        }
    }

    // Botón transversal de Imprimir
    $acciones_list .= '<li><hr class="dropdown-divider"></li>' . $btn_imprimir;

    // Renderizado en Dropdown Bootstrap 5
    return '
    <div class="dropdown text-center">
        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones">
            <i class="bi bi-gear"></i> Acciones
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 text-start" style="font-size: 0.9rem;">
            ' . $acciones_list . '
        </ul>
    </div>';
}
?>