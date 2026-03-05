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
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado, c.idfuncionario_enturno
                FROM correspondencia c
                WHERE c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else if ($usuario_cargo === 'Gerente') {
        // Gerente ve todas desde estado Iniciado en adelante
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado, c.idfuncionario_enturno
                FROM correspondencia c
                WHERE c.estado != 'Registrado'
                  AND c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else if ($usuario_cargo === 'Administrativo') {
        if ($filtro_admin === 'iniciados') {
            // Correspondencias iniciadas por el Administrativo (donde él fue remitente original)
            $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado, c.idfuncionario_enturno
                    FROM correspondencia c
                    WHERE c.remitente_id = :uid
                      AND c.eliminado_en IS NULL
                    ORDER BY c.fecha DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':uid' => $usuario_id]);
        } else {
            // Por defecto: Correspondencias derivadas a él en algún momento
            $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado, c.idfuncionario_enturno
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
    } else {
        // Otros roles (fallback general): derivadas
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado, c.idfuncionario_enturno
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

        // Añadir indicador (punto rojo) si el documento está en poder de este funcionario
        if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
            $estado_display .= ' <span class="badge bg-danger blink ms-2" title="En su poder">&bull;</span>';
        }

        // --- SISTEMA DE BOTONES POR ROL ---
        
        $btn_editar = '<form action="" method="post" style="display: inline;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button></form>';
        $btn_eliminar = '<form action="destroy.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button></form>';
        $btn_iniciar = '<form action="create.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-primary btn-sm" title="Iniciar"><i class="bi bi-play-circle"></i></button></form>';
        $btn_derivar = '<form action="" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="button" class="btn btn-success btn-sm" title="Derivar" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button></form>';
        $btn_historial = '<form action="../derivacion/index.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'"><button type="submit" class="btn btn-info btn-sm" title="Ver historial de derivaciones"><i class="bi bi-list-ul"></i></button></form>';
        $btn_imprimir = '<button type="button" class="btn btn-secondary btn-sm ms-1" style="margin-left:4px;" title="Imprimir" onclick="solicitarPagina('.$correspondencia['id'].')"><i class="bi bi-printer"></i></button>';

        if (in_array($usuario_cargo, ['Administrador', 'Secretaria'])) {
            if ($correspondencia['estado'] === 'Registrado') {
                $acciones = $btn_editar . $btn_eliminar . $btn_iniciar;
            } else {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
                $acciones .= $btn_imprimir;
            }
        } else if ($usuario_cargo === 'Gerente') {
            if ($correspondencia['estado'] !== 'Registrado') {
                if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                    $acciones .= $btn_derivar;
                }
                $acciones .= $btn_historial;
            }
        } else if ($usuario_cargo === 'Administrativo') {
            if ($correspondencia['idfuncionario_enturno'] == $usuario_id) {
                $acciones .= $btn_derivar;
            }
            $acciones .= $btn_historial;
        }

        $data[] = array(
            'hojaruta' => $correspondencia['hojaruta'],
            'remitente' => $correspondencia['remitente'],
            'referencia' => $correspondencia['referencia'],
            'fojas' => $correspondencia['fojas'],
            'fecha' => $correspondencia['fecha'],
            'estado' => $estado_display,
            'acciones' => $acciones
        );
    }
    echo json_encode(array("data" => $data));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>