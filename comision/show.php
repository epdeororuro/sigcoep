<?php
require '../db.php';

// Consulta para obtener datos de comisiones con información relacionada
$stmt = $pdo->prepare("
SELECT
    c.id,
    c.nombre,
    c.descripcion,
    c.estado,
    f.nombre AS responsable_nombre,
    f.paterno AS responsable_paterno,
    f.materno AS responsable_materno
FROM
    comision c
LEFT JOIN
    funcionario f ON c.responsable_id = f.id
WHERE
    c.eliminado_en IS NULL
ORDER BY
    c.nombre;
");
$stmt->execute();
$comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar el array de respuesta
$data = [];
$numero = 1;

foreach ($comisiones as $comision) {
    // Obtener miembros de la comisión
    $stmtMiembros = $pdo->prepare("
    SELECT
        f.nombre,
        f.paterno,
        f.materno
    FROM
        comision_miembro cm
    JOIN
        funcionario f ON cm.funcionario_id = f.id
    WHERE
        cm.comision_id = :comision_id
    ORDER BY
        f.nombre;
    ");
    $stmtMiembros->execute([':comision_id' => $comision['id']]);
    $miembros = $stmtMiembros->fetchAll(PDO::FETCH_ASSOC);

    // Formatear la lista de miembros
    $listaMiembros = '';
    foreach ($miembros as $miembro) {
        $listaMiembros .= htmlspecialchars(trim($miembro['nombre'] . ' ' . ($miembro['paterno'] ?? '') . ' ' . ($miembro['materno'] ?? ''))) . '<br>';
    }

    // Botones de acción (editar, eliminar)
    $acciones = '<div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-warning" title="Editar" onclick="editarComision(' . $comision['id'] . ')"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarComision(' . $comision['id'] . ')"><i class="bi bi-trash"></i></button>
                 </div>';

    $data[] = [
        'numero' => $numero++,
        'nombre' => htmlspecialchars($comision['nombre']),
        'descripcion' => htmlspecialchars($comision['descripcion']),
        'responsable' => htmlspecialchars(trim($comision['responsable_nombre'] . ' ' . ($comision['responsable_paterno'] ?? '') . ' ' . ($comision['responsable_materno'] ?? ''))),
        'miembros' => $listaMiembros,
        'estado' => htmlspecialchars($comision['estado']),
        'acciones' => $acciones,
        'id' => $comision['id']
    ];
}

// Devolver los datos en formato JSON
echo json_encode(['data' => $data]);
