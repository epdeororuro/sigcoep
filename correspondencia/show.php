<?php
session_start();
require '../db.php';

try {
    $sql = "SELECT id, cite, remitente, referencia, fojas, fecha, estado FROM correspondencia ORDER BY fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $correspondencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($correspondencias as $correspondencia) {
        $acciones = '';
        if ($correspondencia['estado'] == 'En curso') {
            $acciones = '
                <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCorrespondenciaModal" onclick="editarCorrespondencia('.$correspondencia['id'].')"><i class="bi bi-pencil"></i></button>
                </form>
                <form action="destroy.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$correspondencia['id'].'">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
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