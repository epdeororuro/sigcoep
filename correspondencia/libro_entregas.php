
<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

$usuario_id = $_SESSION['usuario_id'];
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');

// Consultar derivaciones hechas por este usuario en la fecha seleccionada
// Al no tener una columna explícita de remitente, deducimos quién lo envió comprobando 
// si es el creador original (en la 1ra derivación) o si fue el receptor en el paso anterior.
$sql = "SELECT d.fecha_derivacion, c.hojaruta, c.referencia, d.instruccion_adicional, 
               f.nombre, f.paterno, f.materno, d.fojas
        FROM derivacion d
        INNER JOIN correspondencia c ON d.id_correspondencia = c.id
        LEFT JOIN funcionario f ON d.id_funcionario = f.id
        WHERE DATE(d.fecha_derivacion) BETWEEN :fecha_inicio AND :fecha_fin
          AND ( (c.remitente_id = :uid1 AND (SELECT COUNT(*) FROM derivacion d2 WHERE d2.id_correspondencia = c.id AND d2.fecha_derivacion < d.fecha_derivacion) = 0)
                OR (:uid2 = (SELECT d3.id_funcionario FROM derivacion d3 WHERE d3.id_correspondencia = c.id AND d3.fecha_derivacion < d.fecha_derivacion ORDER BY d3.fecha_derivacion DESC LIMIT 1)) )
        ORDER BY d.fecha_derivacion ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid1' => $usuario_id, ':uid2' => $usuario_id, ':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro de Entregas - <?= date('d/m/Y', strtotime($fecha_inicio)) ?> al <?= date('d/m/Y', strtotime($fecha_fin)) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .report-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 20px; min-height: 800px; }
        /* Bordes estrictamente negros para impresión formal */
        .table-signature th, .table-signature td { vertical-align: middle !important; border: 1px solid #000 !important; }
        .table-signature thead th { border-bottom: 2px solid #000 !important; }
        .signature-box { height: 70px; }
        /* Cabecera idéntica a report.php */
        .header { display: flex; justify-content: space-between; align-items: center; height: 2.5cm; border-bottom: 2px solid #000; margin-bottom: 15px; padding-bottom: 10px; }
        @media print {
            body { background-color: #fff; }
            .no-print { display: none !important; }
            .report-container { box-shadow: none; margin-top: 0; padding: 0; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>
    <div class="container report-container">
        <!-- Controles no imprimibles -->
        <div class="text-end mb-4 no-print border-bottom pb-3">
            <a href="index.php" class="btn btn-danger me-2"><i class="bi bi-arrow-left-circle"></i> Volver a Correspondencia</a>
            <button onclick="window.print()" class="btn btn-success"><i class="bi bi-printer"></i> Imprimir Planilla</button>
        </div>

        <!-- Cabecera Formal idéntica a report.php -->
        <div class="header">
            <img src="../assets/img/logo.png" alt="Logo Izquierdo" style="height: 70px; width: auto;" onerror="this.style.display='none'">
            <div class="text-center" style="flex-grow: 1;">
                <h4 class="text-uppercase fw-bold m-0" style="font-family: 'Arial Black', Arial, sans-serif;">Cuaderno de Cargo</h4>
                <h6 class="text-uppercase m-0" style="font-family: Cambria, serif; font-weight: bold;">Libro de Entregas Físicas</h6>
                <p class="mb-0 text-muted" style="font-size: 0.85rem;">Sistema de Gestión de Correspondencia</p>
            </div>
            <img src="../assets/img/logo2.png" alt="Logo Derecho" style="height: 70px; width: auto;" onerror="this.style.display='none'">
        </div>
        
        <div class="row mb-3" style="font-size: 0.9rem;">
            <div class="col-6">
                <strong>Funcionario Remitente:</strong> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?><br>
                <strong>Fecha Impresión:</strong> <?= date('d/m/Y H:i') ?>
            </div>
            <div class="col-6 text-end">
                <strong>Rango de Despachos:</strong><br>
                Del <?= date('d/m/Y', strtotime($fecha_inicio)) ?> al <?= date('d/m/Y', strtotime($fecha_fin)) ?>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="table-responsive">
            <table class="table table-sm table-signature">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 5%;">Hora</th>
                        <th style="width: 12%;">Hoja de Ruta</th>
                        <th style="width: 23%;">Destinatario / Área</th>
                        <th style="width: 30%;">Ref. e Instrucción</th>
                        <th style="width: 15%;">Fecha/Hora Recepción</th>
                        <th style="width: 15%;">Firma / Sello</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?><tr><td colspan="6" class="text-center text-muted py-4">No se realizaron despachos en este rango de fechas.</td></tr><?php else: ?>
                        <?php foreach ($registros as $r): ?><tr>
                            <td class="text-center"><?= date('H:i', strtotime($r['fecha_derivacion'])) ?><br><small class="text-muted"><?= date('d/m', strtotime($r['fecha_derivacion'])) ?></small></td>
                            <td class="text-center fw-bold"><?= htmlspecialchars($r['hojaruta']) ?></td>
                            <td style="font-size: 0.85rem;" class="fw-semibold"><?= htmlspecialchars(trim(($r['nombre']??'').' '.($r['paterno']??'').' '.($r['materno']??''))) ?></td>
                            <td style="font-size: 0.75rem;"><strong>Ref:</strong> <?= htmlspecialchars($r['referencia']) ?><br><span class="text-muted"><strong>Inst:</strong> <?= htmlspecialchars($r['instruccion_adicional']) ?></span></td>
                            <td class="signature-box text-center text-black-50" style="font-size:0.7rem; vertical-align:bottom !important; padding-bottom:5px;">_____/_____/_____ &nbsp;&nbsp; ____:____</td>
                            <td class="signature-box"></td>
                        </tr><?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($registros)): ?>
                <div class="text-end fw-bold mt-2" style="font-size: 0.85rem;">Total de trámites despachados: <?= count($registros) ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
