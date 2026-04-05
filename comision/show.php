<?php
require '../db.php';

// Consulta para obtener datos de comisiones con información relacionada
$stmt = $pdo->prepare("
SELECT
    c.id,
    c.nombre,
    c.descripcion,
    c.estado,
    resp.nombre AS responsable_nombre,
    resp.paterno AS responsable_paterno,
    resp.materno AS responsable_materno
FROM
    comision c
LEFT JOIN 
    puesto p ON c.responsable_puesto_id = p.id
LEFT JOIN 
    funcionario resp ON p.id = resp.id_puesto AND resp.estado = 'Activo'
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

    // Botones de acción (editar, eliminar) en Menú Desplegable
    $acciones = '<div class="dropdown text-center">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones">
                        <i class="bi bi-gear"></i> Acciones
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 text-start" style="font-size: 0.9rem;">
                        <li><a class="dropdown-item" href="#" onclick="editarComision(' . $comision['id'] . '); return false;"><i class="bi bi-pencil text-warning me-2"></i> Editar</a></li>
                        <li><a class="dropdown-item" href="#" onclick="eliminarComision(' . $comision['id'] . '); return false;"><i class="bi bi-trash text-danger me-2"></i> Eliminar</a></li>
                    </ul>
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
