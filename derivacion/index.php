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
$stmt = $pdo->prepare("SELECT id, hojaruta, remitente, referencia, fojas, anexo, fecha_registro, estado FROM correspondencia WHERE id = :id");
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

// Calcular total de fojas sumando directamente de la tabla derivacion
$stmtSum = $pdo->prepare("SELECT SUM(CAST(fojas AS UNSIGNED)) FROM derivacion WHERE id_correspondencia = :id");
$stmtSum->execute([':id' => $id]);
$total_fojas = (int) $stmtSum->fetchColumn();

$anexos_acumulados = [];
if (!empty(trim($cor['anexo'] ?? ''))) {
    $anexos_acumulados[] = trim($cor['anexo']);
}
foreach ($derivaciones as $d) {
    // Concatenar el atributo anexos (si existe y no está vacío)
    $anexo_derivacion = trim($d['anexos'] ?? $d['anexo'] ?? '');
    if (!empty($anexo_derivacion)) {
        $anexos_acumulados[] = $anexo_derivacion;
    }
}
// Evitar nombres de anexos duplicados si se registran repetidos
$anexos_acumulados = array_unique($anexos_acumulados);
$texto_anexos = implode(', ', $anexos_acumulados);

// Array para traducir los meses al español
$meses_es = [
    'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
    'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
    'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Derivaciones - <?php echo htmlspecialchars($cor['hojaruta']); ?></title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Estilos de Derivación -->
    <link rel="stylesheet" href="../assets/css/derivacion.css">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>Historial de Derivaciones - Línea de Vida</h4>
            <small class="text-muted">Hoja de ruta: <?php echo htmlspecialchars($cor['hojaruta']); ?></small>
        </div>
        <div>
            <a href="../correspondencia/index.php" class="btn btn-danger">
                <i class="bi bi-arrow-left-circle"></i> Volver a lista de correspondencia
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="timeline">
                <?php if (empty($derivaciones)): ?>
                    <div class="alert alert-info">No hay derivaciones registradas para esta correspondencia.</div>
                <?php else: ?>
                    <?php foreach ($derivaciones as $index => $d): ?>
                        <div class="timeline-item">
                            <div class="time-marker">
                                <div class="circle_blue"><?php echo strtr(date('d-M', strtotime($d['fecha_derivacion'])), $meses_es); ?></div>
                                <?php if ($index > 0): ?>
                                    <div class="circle_green"><?php echo strtr(date('d-M', strtotime($d['fecha_entrega_derivacion'])), $meses_es); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card p-2">
                                <div class="card-body p-2">
                                    <strong><?php echo htmlspecialchars(trim(($d['nombre'] ?? '') . ' ' . ($d['paterno'] ?? '') . ' ' . ($d['materno'] ?? ''))); ?></strong>
                                    <div class="text-info small">Fecha Derivación: <?php echo date('d-m-Y H:i:s', strtotime($d['fecha_derivacion'])); ?></div>
                                    <?php if ($index > 0): ?>
                                        <div class="text-success small">Fecha Entrega: <?php echo date('d-m-Y H:i:s', strtotime($d['fecha_entrega_derivacion'])); ?></div>
                                    <?php endif; ?>
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
            <h5>Detalle de Derivacion</h5>
            <div class="alert alert-secondary py-2 mt-2 mb-3">
                <div class="small">
                    <i class="bi bi-files"></i> <strong>Aproximadamente:</strong> <?= $total_fojas ?> foja(s) en total.
                    <?php if (!empty($texto_anexos)): ?>
                        <br><i class="bi bi-paperclip"></i> <strong>Anexos acumulados:</strong> <?= htmlspecialchars($texto_anexos) ?>
                    <?php endif; ?>
                </div>
            </div>
            <table class="table table-striped text-center align-middle">
                <thead class="text-center">
                <tr>
                    <th>Funcionario</th>
                    <th>Fecha Derivación</th>
                    <th>Fecha Recepción</th>
                    <th>Instrucción</th>
                    <th>Fojas</th>
                    <th>Anexo</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($derivaciones)): ?>
                    <?php foreach ($derivaciones as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(trim(($d['nombre'] ?? '') . ' ' . ($d['paterno'] ?? '') . ' ' . ($d['materno'] ?? ''))); ?></td>
                            <td><?php echo strtr(date('d-M-Y H:i:s', strtotime($d['fecha_derivacion'])), $meses_es); ?></td>
                            <td>
                                <?php if (!empty($d['fecha_entrega_derivacion'])): ?>
                                    <div class="d-flex flex-column align-items-center">
                                        <span>
                                            <?php echo strtr(date('d-M-Y H:i:s', strtotime($d['fecha_entrega_derivacion'])), $meses_es); ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No entregado</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($d['instruccion_adicional'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($d['fojas']); ?></td>
                            <td><?php echo htmlspecialchars($d['anexos'] ?? $d['anexo'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Sin registros</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
