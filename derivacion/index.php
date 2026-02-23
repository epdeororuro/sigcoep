<?php
session_start();
require '../db.php';

// Obtener id de correspondencia (POST o GET)
$id = $_POST['id'] ?? $_GET['id'] ?? null;
if (empty($id)) {
    echo "<div class='alert alert-danger'>No se proporcionó el ID de la correspondencia.</div>";
    exit;
}

// Obtener datos de la correspondencia
$stmt = $pdo->prepare("SELECT id, cite, remitente, referencia, fojas, fecha, estado FROM correspondencia WHERE id = :id");
$stmt->execute([':id' => $id]);
$cor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cor) {
    echo "<div class='alert alert-danger'>Correspondencia no encontrada.</div>";
    exit;
}

// Obtener historial de derivaciones
$sql = "SELECT d.*, f.nombre, f.paterno, f.materno
        FROM derivacion d
        LEFT JOIN funcionario f ON f.id = d.id_funcionario
        WHERE d.id_correspondencia = :id
        ORDER BY d.fecha_derivacion ASC";
$stmt2 = $pdo->prepare($sql);
$stmt2->execute([':id' => $id]);
$derivaciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Derivaciones - <?php echo htmlspecialchars($cor['cite']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Timeline simple */
        .timeline {
            position: relative;
            padding: 1rem 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-left: 60px;
            margin-bottom: 1.5rem;
        }
        .timeline-item .time-marker {
            position: absolute;
            left: -43px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .timeline-item .card {
            box-shadow: none;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>Historial de Derivaciones</h4>
            <small class="text-muted">Cite: <?php echo htmlspecialchars($cor['cite']); ?></small>
        </div>
        <div>
            <a href="../correspondencia/index.php" class="btn btn-secondary">Volver a correspondencia</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="timeline">
                <?php if (empty($derivaciones)): ?>
                    <div class="alert alert-info">No hay derivaciones registradas para esta correspondencia.</div>
                <?php else: ?>
                    <?php foreach ($derivaciones as $d): ?>
                        <div class="timeline-item">
                            <div class="time-marker"><?php echo date('d', strtotime($d['fecha_derivacion'])); ?></div>
                            <div class="card p-2">
                                <div class="card-body p-2">
                                    <strong><?php echo htmlspecialchars(trim(($d['nombre'] ?? '') . ' ' . ($d['paterno'] ?? '') . ' ' . ($d['materno'] ?? ''))); ?></strong>
                                    <div class="text-muted small"><?php echo htmlspecialchars($d['fecha_derivacion']); ?></div>
                                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($d['instruccion_adicional'] ?? '')); ?></p>
                                    <div class="small text-muted mt-1">Fojas: <?php echo htmlspecialchars($d['fojas']); ?> — Carácter: <?php echo htmlspecialchars($d['caracter']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <h5>Detalle (lista)</h5>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Funcionario</th>
                    <th>Fecha</th>
                    <th>Instrucción</th>
                    <th>Fojas</th>
                    <th>Carácter</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($derivaciones)): ?>
                    <?php foreach ($derivaciones as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(trim(($d['nombre'] ?? '') . ' ' . ($d['paterno'] ?? '') . ' ' . ($d['materno'] ?? ''))); ?></td>
                            <td><?php echo htmlspecialchars($d['fecha_derivacion']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($d['instruccion_adicional'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($d['fojas']); ?></td>
                            <td><?php echo htmlspecialchars($d['caracter']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Sin registros</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
