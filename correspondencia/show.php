<?php
session_start();
require '../db.php';
require_once 'helper_show.php';

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
            case 'revision':
                $where_clauses[] = "c.estado IN ('Revisión Archivo', 'Pendiente Archivo')";
                break;
            case 'todos_activos':
                $where_clauses[] = "c.estado NOT IN ('Concluido', 'Archivado', 'Revisión Archivo', 'Pendiente Archivo')";
                break;
            // 'todos' o null no añade filtro de estado, muestra todo
        }
    } else if ($usuario_cargo === 'Secretaria') {
        $filtro = empty($filtro) ? 'todos_activos' : $filtro; // Forzar vista por defecto
        if ($filtro === 'no_cursadas') {
            $where_clauses[] = "c.estado = 'No cursada'";
        } elseif ($filtro === 'archivado') {
            $where_clauses[] = "c.estado = 'Archivado'";
        } else {
            // 'todos_activos'
            $where_clauses[] = "c.estado NOT IN ('Concluido', 'Archivado', 'Revisión Archivo', 'Pendiente Archivo', 'No cursada')";
        }
    } else if ($usuario_cargo === 'Archivista Central') {
        $filtro = $filtro ?? 'entrantes';
        if ($filtro === 'pendientes') {
            $where_clauses[] = "c.idfuncionario_enturno = :uid";
            $where_clauses[] = "c.estado = 'Aceptado'";
            $params[':uid'] = $usuario_id;
        } elseif ($filtro === 'revision') {
            $where_clauses[] = "(c.idfuncionario_enturno = :uid OR c.remitente_id = :uid OR EXISTS (
                SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :uid
            ))";
            $where_clauses[] = "c.estado IN ('Revisión Archivo', 'Pendiente Archivo')";
            $params[':uid1'] = $usuario_id;
            $params[':uid2'] = $usuario_id;
            $params[':uid3'] = $usuario_id;
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
        } elseif ($filtro === 'revision') {
            $where_clauses[] = "(c.idfuncionario_enturno = :uid1 OR c.remitente_id = :uid2 OR EXISTS (
                SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :uid3
            ))";
            $where_clauses[] = "c.estado IN ('Revisión Archivo', 'Pendiente Archivo')";
            $params[':uid1'] = $usuario_id;
            $params[':uid2'] = $usuario_id;
            $params[':uid3'] = $usuario_id;
        } elseif ($filtro === 'archivo_central') {
            // Bandeja de Archivo Central: Solo archivados donde participó en la línea de vida
            $where_clauses[] = "(c.idfuncionario_enturno = :uid1 OR c.remitente_id = :uid2 OR EXISTS (
                SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :uid3
            ))";
            $where_clauses[] = "c.estado = 'Archivado'";
            $params[':uid1'] = $usuario_id;
            $params[':uid2'] = $usuario_id;
            $params[':uid3'] = $usuario_id;
        } elseif ($filtro === 'despachados') {
            // Bandeja de Despachados: Correspondencias que pasaron por el usuario y derivó
            $where_clauses[] = "EXISTS (
                SELECT 1 FROM derivacion d2
                WHERE d2.id_correspondencia = c.id
                  AND d2.id_funcionario = :uid1
            )";
            $where_clauses[] = "c.idfuncionario_enturno != :uid2";
            $where_clauses[] = "c.estado NOT IN ('Concluido', 'Archivado', 'Revisión Archivo', 'Pendiente Archivo')";
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
        $sql_counts = "SELECT 
            SUM(estado = 'Derivado' AND idfuncionario_enturno = :u1) as entrantes,
            SUM(estado = 'Aceptado' AND idfuncionario_enturno = :u2) as pendientes,
            SUM(EXISTS (SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :u3) AND idfuncionario_enturno != :u4 AND estado NOT IN ('Concluido', 'Archivado', 'Revisión Archivo', 'Pendiente Archivo')) as despachados,
            SUM(remitente_id = :u5) as iniciados,
            SUM(estado = 'Iniciado') as para_iniciar,
            SUM(estado = 'Concluido' AND idfuncionario_enturno = :u6) as concluidos,
            SUM(estado IN ('Revisión Archivo', 'Pendiente Archivo') AND (idfuncionario_enturno = :u7 OR remitente_id = :u8 OR EXISTS (SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :u9))) as revision,
                SUM(estado = 'Archivado' AND (idfuncionario_enturno = :u10 OR remitente_id = :u11 OR EXISTS (SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :u12))) as archivo_central
        FROM correspondencia c WHERE eliminado_en IS NULL";
        $stmt_counts = $pdo->prepare($sql_counts);
        $stmt_counts->execute([
            ':u1' => $usuario_id, ':u2' => $usuario_id, ':u3' => $usuario_id, ':u4' => $usuario_id,
            ':u5' => $usuario_id, ':u6' => $usuario_id, ':u7' => $usuario_id, ':u8' => $usuario_id,
            ':u9' => $usuario_id, ':u10' => $usuario_id, ':u11' => $usuario_id, ':u12' => $usuario_id
        ]);
        $res = $stmt_counts->fetch(PDO::FETCH_ASSOC);
        $counts['entrantes'] = (int)($res['entrantes'] ?? 0);
        $counts['pendientes'] = (int)($res['pendientes'] ?? 0);
        $counts['despachados'] = (int)($res['despachados'] ?? 0);
        if ($usuario_cargo === 'Administrativo') $counts['iniciados'] = (int)($res['iniciados'] ?? 0);
        if ($usuario_cargo === 'Gerente') $counts['para_iniciar'] = (int)($res['para_iniciar'] ?? 0);
        $counts['concluidos'] = (int)($res['concluidos'] ?? 0);
        $counts['revision'] = (int)($res['revision'] ?? 0);
        $counts['archivo_central'] = (int)($res['archivo_central'] ?? 0);
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
        $counts['revision'] = ($estado_counts['Revisión Archivo'] ?? 0) + ($estado_counts['Pendiente Archivo'] ?? 0);
        $counts['todos_activos'] = $counts['todos'] - $counts['concluido'] - $counts['archivado'] - $counts['revision'];
    } elseif ($usuario_cargo === 'Archivista Central') {
        $sql_counts = "SELECT 
            SUM(estado = 'Derivado' AND idfuncionario_enturno = :u1) as entrantes,
            SUM(estado = 'Aceptado' AND idfuncionario_enturno = :u2) as pendientes,
            SUM(estado IN ('Revisión Archivo', 'Pendiente Archivo') AND (idfuncionario_enturno = :u3 OR remitente_id = :u4 OR EXISTS (SELECT 1 FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.id_funcionario = :u5))) as revision,
            SUM(estado = 'Archivado' AND idfuncionario_enturno = :u6) as archivo_central
        FROM correspondencia c WHERE eliminado_en IS NULL";
        $stmt_counts = $pdo->prepare($sql_counts);
        $stmt_counts->execute([
            ':u1' => $usuario_id, ':u2' => $usuario_id, ':u3' => $usuario_id, 
            ':u4' => $usuario_id, ':u5' => $usuario_id, ':u6' => $usuario_id
        ]);
        $res = $stmt_counts->fetch(PDO::FETCH_ASSOC);
        $counts['entrantes'] = (int)($res['entrantes'] ?? 0);
        $counts['pendientes'] = (int)($res['pendientes'] ?? 0);
        $counts['revision'] = (int)($res['revision'] ?? 0);
        $counts['archivo_central'] = (int)($res['archivo_central'] ?? 0);
    } elseif ($usuario_cargo === 'Secretaria') {
        $stmt_c = $pdo->query("SELECT estado, COUNT(*) as total FROM correspondencia WHERE eliminado_en IS NULL GROUP BY estado");
        $estado_counts = $stmt_c->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $counts['no_cursadas'] = $estado_counts['No cursada'] ?? 0;
        $counts['todos_activos'] = array_sum($estado_counts) - ($estado_counts['Concluido'] ?? 0) - ($estado_counts['Archivado'] ?? 0) - ($estado_counts['Revisión Archivo'] ?? 0) - ($estado_counts['Pendiente Archivo'] ?? 0) - $counts['no_cursadas'];
    }
 
    $data = array();
    foreach ($correspondencias as $correspondencia) {
        // Verificar si está retrasado (estado Aceptado por más de 5 días)
        $es_retrasado = false;
        if ($correspondencia['estado'] === 'Aceptado' && !empty($correspondencia['fecha_referencia'])) {
            $dias_pasados = floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($correspondencia['fecha_referencia'])))) / 86400);
            if ($dias_pasados >= 5) {
                $es_retrasado = true;
            }
        }

        // Formateo usando el archivo helper
        $fotoHtml = obtenerFotoHtml($correspondencia['foto']);
        $estado_display = obtenerEstadoHtml($correspondencia, $es_retrasado, $usuario_id);
        $acciones = obtenerAccionesHtml($correspondencia, $usuario_cargo, $usuario_id, $es_retrasado);

        // Construir la cadena de fecha/hora para mostrar en la tabla
        $fecha_display = '<div class="text-nowrap"><strong>Registro:</strong> ' . date('d-m-Y', strtotime($correspondencia['fecha_registro'])) . '<br>a horas: ' . date('H:i:s', strtotime($correspondencia['fecha_registro'])) . '</div>';
        if ($correspondencia['estado'] === 'Concluido' && !empty($correspondencia['fecha_conclusion'])) {
            $fecha_display .= '<div class="text-nowrap text-primary mt-1"><strong>Conclusión:</strong> ' . date('d-m-Y', strtotime($correspondencia['fecha_conclusion'])) . '<br>a horas: ' . date('H:i:s', strtotime($correspondencia['fecha_conclusion'])) . '</div>';
        }

        // Combinar Fojas y Anexos en un solo bloque
        $fojas_anexo = '<div class="text-start"><strong>Fojas:</strong> ' . htmlspecialchars($correspondencia['fojas']) . '</div>';
        if (!empty(trim($correspondencia['anexo'] ?? ''))) {
            $fojas_anexo .= '<div class="text-start mt-1"><strong>Anexo:</strong><br>' . nl2br(htmlspecialchars($correspondencia['anexo'])) . '</div>';
        }

        $data[] = array(
            'hojaruta' => $correspondencia['hojaruta'],
            'remitente' => $correspondencia['remitente'],
            'referencia' => $correspondencia['referencia'],
            'fojas_anexo' => $fojas_anexo,
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