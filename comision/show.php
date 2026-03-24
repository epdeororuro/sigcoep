<?php
error_reporting(0); // Evitar que advertencias de PHP rompan el JSON
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT c.id, c.nombre, c.descripcion, c.estado, 
                   f.nombre AS r_nombre, f.paterno AS r_paterno, f.materno AS r_materno,
                   (SELECT COUNT(*) FROM comision_miembro cm WHERE cm.comision_id = c.id) as total_miembros
            FROM comision c
            LEFT JOIN funcionario f ON c.responsable_id = f.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $data = array();
    $n = 1;
    foreach ($comisiones as $com) {
        $acciones = '';
        if ($com['estado'] == 'Activo') {
            $acciones = '
                <button type="button" class="btn btn-warning btn-sm" onclick="editarComision('.$com['id'].')"><i class="bi bi-pencil"></i></button>
                <form action="destroy.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$com['id'].'">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>';
        } else {
            $acciones = '
                <form action="restore.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$com['id'].'">
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-recycle"></i></button>
                </form>';
        }

        $resp_full = trim(($com['r_nombre'] ?? '') . ' ' . ($com['r_paterno'] ?? '') . ' ' . ($com['r_materno'] ?? ''));

        $data[] = array(
            'numero' => $n,
            'nombre' => htmlspecialchars($com['nombre'] ?? ''),
            'descripcion' => htmlspecialchars($com['descripcion'] ?? ''),
            'responsable' => htmlspecialchars($resp_full),
            'miembros' => '<span class="badge bg-primary">'.$com['total_miembros'].' Integrantes</span>',
            'estado' => htmlspecialchars($com['estado'] ?? ''),
            'acciones' => $acciones
        );
        $n++;
    }
    
    echo json_encode(array("data" => $data));

} catch (Exception $e) {
    echo json_encode(array("error" => "Error de BD: " . $e->getMessage()));
}