<?php
session_start();
require '../db.php';

try {
    $sql = "SELECT c.id, c.cite, c.remitente, c.referencia, c.fojas, c.fecha, c.estado,
                   (SELECT COUNT(1) FROM derivacion d WHERE d.id_correspondencia = c.id) AS deriv_count
            FROM correspondencia c
            ORDER BY c.fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $correspondencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($correspondencias as $correspondencia) {
        // Si existe al menos una derivación, forzamos el estado 'Derivado' en la vista
        if (!empty($correspondencia['deriv_count']) && intval($correspondencia['deriv_count']) > 0) {
            $correspondencia['estado'] = 'Derivado';
        }

        $acciones = '';
        if ($correspondencia['estado'] == 'Registrado - Sin Derivar') {
            $acciones = '
                <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button>
                </form>
                <form action="destroy.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>
                <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#derivarCorrespondenciaModal" onclick="derivarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-arrow-right-circle"></i></button>
                </form>
            ';
        } elseif ($correspondencia['estado'] == 'Anulado') {
            $acciones = '
                <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button>
                </form>
                <form action="restore.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-recycle"></i></button>
                </form>
            ';
        } elseif ($correspondencia['estado'] == 'Derivado') {
            // Botón para ver historial/ramificación en el módulo derivacion
            $acciones = '
                <form action="../derivacion/index.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="submit" class="btn btn-info btn-sm" title="Ver historial de derivaciones"><i class="bi bi-list-ul"></i></button>
                </form>
            ';
        }
        $data[] = array(
            'cite' => $correspondencia['cite'],
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