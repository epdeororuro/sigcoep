<?php
session_start();
require '../db.php';

try {
    // determinar usuario actual y su rol
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_cargo = $_SESSION['usuario_cargo'] ?? null;

    // Roles que pueden ver todas las correspondencias
    $roles_ver_todas = ['Administrador', 'Secretaria', 'Gerente'];

    // Verificar si el usuario puede ver todas las correspondencias
    $puede_ver_todas = in_array($usuario_cargo, $roles_ver_todas);

    if ($puede_ver_todas) {
        // Gerente, Secretaria y Administrador ven todas las correspondencias
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado,
                       (SELECT COUNT(1) FROM derivacion d WHERE d.id_correspondencia = c.id) AS deriv_count
                FROM correspondencia c
                WHERE c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else if ($usuario_cargo === 'Administrativo') {
        // Administrativo solo ve correspondencias donde es remitente interno
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado,
                       (SELECT COUNT(1) FROM derivacion d WHERE d.id_correspondencia = c.id) AS deriv_count
                FROM correspondencia c
                WHERE c.remitente_id = :uid
                  AND c.eliminado_en IS NULL
                ORDER BY c.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':uid' => $usuario_id]);
    } else {
        // Otros roles (por compatibilidad): solo correspondencias derivadas al usuario
        $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado,
                       (SELECT COUNT(1) FROM derivacion d WHERE d.id_correspondencia = c.id) AS deriv_count
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

        // Estado Registrado: permitir Editar, Eliminar e Iniciar
        if ($correspondencia['estado'] == 'Registrado' && $usuario_cargo !== 'Administrativo') {
            $acciones = '<form action="" method="post" style="display: inline;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button>
            </form>
            <form action="destroy.php" method="post" style="display: inline; margin-left:4px;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button></form>
            <form action="create.php" method="post" style="display: inline; margin-left:4px;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="submit" class="btn btn-primary btn-sm" title="Iniciar"><i class="bi bi-play-circle"></i></button>
            </form>';

        // Estado Anulado: permitir Editar y Restaurar
        } elseif ($correspondencia['estado'] == 'Anulado') {
            $acciones = '
            <form action="" method="post" style="display: inline;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button>
            </form>
            <form action="restore.php" method="post" style="display: inline; margin-left:4px;"><input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="submit" class="btn btn-success btn-sm" title="Restaurar"><i class="bi bi-recycle"></i></button>
            </form>';

        // Estado Iniciado: permitir solo Derivar (abrir modal existente)
        } elseif ($correspondencia['estado'] == 'Iniciado'&& $usuario_cargo !== 'Administrativo') {
            $acciones = '
            <form action="" method="post" style="display: inline;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="button" class="btn btn-primary btn-sm" title="Derivar" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button>
            </form>';

        // Estado Derivado: mostrar historial (módulo derivacion)
        } elseif ($correspondencia['estado'] == 'Derivado') {
            $acciones = '
            <form action="" method="post" style="display: inline;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="button" class="btn btn-primary btn-sm" title="Derivar" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button>
            </form>
            <form action="../derivacion/index.php" method="post" style="display: inline;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="submit" class="btn btn-info btn-sm" title="Ver historial de derivaciones"><i class="bi bi-list-ul"></i></button>
            </form>';
        }

        // si no está en estado Registrado añadimos botón de impresión (oculto para administrativos)
        if ($correspondencia['estado'] != 'Registrado' && $usuario_cargo !== 'Administrativo') {
            $acciones .= '<button type="button" class="btn btn-secondary btn-sm ms-1" title="Imprimir" onclick="solicitarPagina('.$correspondencia['id'].')"><i class="bi bi-printer"></i></button>';
        }

        $data[] = array(
            'hojaruta' => $correspondencia['hojaruta'],
            'remitente' => $correspondencia['remitente'],
            'referencia' => $correspondencia['referencia'],
            'fojas' => $correspondencia['fojas'],
            'fecha' => $correspondencia['fecha'],
            'estado' => $correspondencia['estado'],
            'acciones' => $acciones
        );
    }
    echo json_encode(array("data" => $data));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>