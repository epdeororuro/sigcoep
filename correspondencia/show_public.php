<?php
require '../db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "SELECT 
                c.hojaruta,
                c.referencia,
                c.fecha,
                c.estado,
                c.idfuncionario_enturno,
                f.nombre,
                f.paterno,
                f.materno,
                p.descripcion AS puesto_descripcion,
                p.sigla AS puesto_sigla
            FROM correspondencia c
            LEFT JOIN funcionario f ON c.idfuncionario_enturno = f.id
            LEFT JOIN puesto p ON f.id_puesto = p.id
            WHERE c.eliminado_en IS NULL
            ORDER BY c.fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $r) {
        // Construir nombre completo del funcionario (si existe)
        $nombreCompleto = '';
        if (!empty($r['nombre']) || !empty($r['paterno']) || !empty($r['materno'])) {
            $nombreCompleto = trim(
                ($r['nombre'] ?? '') . ' ' .
                ($r['paterno'] ?? '') . ' ' .
                ($r['materno'] ?? '')
            );
        }

        // Texto que indique con quién está en curso
        if (!empty($nombreCompleto)) {
            $enCursoCon = $nombreCompleto;
            if (!empty($r['puesto_sigla'])) {
                $enCursoCon .= ' (' . $r['puesto_sigla'] . ')';
            }
        } else {
            $enCursoCon = 'Sin asignar';
        }

        $data[] = [
            'hojaruta'    => $r['hojaruta'],
            'referencia'  => $r['referencia'],
            'en_curso_con'=> $enCursoCon,
            'estado'      => $r['estado'],
        ];
    }

    echo json_encode(['data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['data' => [], 'error' => $e->getMessage()]);
}

