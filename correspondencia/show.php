<?php
session_start();
require '../db.php';

try {
    $sql = "SELECT c.id, c.hojaruta, c.remitente, c.referencia, c.fojas, c.fecha, c.estado,
                   (SELECT COUNT(1) FROM derivacion d WHERE d.id_correspondencia = c.id) AS deriv_count
            FROM correspondencia c
            ORDER BY c.fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $correspondencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($correspondencias as $correspondencia) {
        $acciones = '';

        // Estado Registrado: permitir Editar, Eliminar e Iniciar
        if ($correspondencia['estado'] == 'Registrado') {
            $acciones = '<form action="" method="post" style="display: inline;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button>
            </form>
            <form action="destroy.php" method="post" style="display: inline; margin-left:4px;">
            <input type="hidden" name="id" value="'.$correspondencia['id'].'">
            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button></form>
            <form action="iniciar.php" method="post" style="display: inline; margin-left:4px;">
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
        } elseif ($correspondencia['estado'] == 'Iniciado') {
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