<?php
// reporte.php (reconstruido)
session_start();
require '../db.php';

// recibir parámetros preferentemente por POST
$id = 0;
$page = 1;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
} else {
    // fallback mínimo
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
}
if ($id <= 0 || $page < 1 || $page > 10) {
    echo "<p>Parámetros inválidos.</p>";
    exit;
}

// obtener datos de correspondencia
$stmt = $pdo->prepare("SELECT * FROM correspondencia WHERE id = :id");
$stmt->execute([':id' => $id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) {
    echo "<p>Correspondencia no encontrada.</p>";
    exit;
}

// extraer información básica
$hojaruta = htmlspecialchars($c['hojaruta']);
$remitente = htmlspecialchars($c['remitente']);
$referencia = htmlspecialchars($c['referencia']);
$fojas = htmlspecialchars($c['fojas']);
$fecha_ts = $c['fecha'];
$fecha_date = date('d-m-Y', strtotime($fecha_ts));
$fecha_time = date('H:i', strtotime($fecha_ts));

// preparar listado de destinatarios según derivaciones
$stmt = $pdo->prepare(
    "SELECT f.nombre, f.paterno, f.materno
     FROM derivacion d
     JOIN funcionario f ON f.id = d.id_funcionario
     WHERE d.id_correspondencia = :id
     ORDER BY d.id ASC"
);
$stmt->execute([':id' => $id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$destinos = [];
foreach ($rows as $r) {
    $nombre = trim($r['nombre'] . ' ' . $r['paterno'] . ' ' . $r['materno']);
    if ($nombre === '') { $nombre = '(sin nombre)'; }
    $destinos[] = $nombre;
}

// obtener nombre del Gerente General activo (id_puesto = 1)
$stmtG = $pdo->prepare("SELECT nombre, paterno, materno FROM funcionario WHERE id_puesto = 1 AND estado = 'Activo' LIMIT 1");
$stmtG->execute();
$g = $stmtG->fetch(PDO::FETCH_ASSOC);
$gerente = '';
if ($g) {
    $gerente = trim($g['nombre'] . ' ' . $g['paterno'] . ' ' . $g['materno']) . ' - GERENTE GENERAL';
}

// función auxiliar para obtener destino por índice 1-based
function destino($index, $destinos) {
    if (isset($destinos[$index-1])) {
        return htmlspecialchars($destinos[$index-1]);
    }
    return '';
}

// calcular rango de destinatarios para la página solicitada
$dest_range = [];
if ($page === 1) {
    // la caja pequeña muestra el destinatario 1 (Gerente), en la misma página mostramos 2..4
    $dest_range = range(2, 4);
} else {
    // páginas 2..n: 5-7, 8-10, ...
    $start = 5 + ($page - 2) * 3;
    $dest_range = range($start, $start + 2);
}

?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Hoja de Ruta - página <?= $page ?></title>
    <!-- Bootstrap CSS for button styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-..." crossorigin="anonymous">
    <!-- FontAwesome for print icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @page { size: 21.59cm 27.94cm; margin: 1cm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .page { width: 19.59cm; height: 25.94cm; position: relative; padding: 0.5cm 1cm; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; height: 2.5cm; }
        /* make the middle logo a bit wider */
        @media print { #printBtn { display:none; } }
        /* position print button using bootstrap utility spacing */
        #printBtn { position:fixed; top:10px; right:10px; z-index:1000; }
        .referencia {  border:1px solid #000; font-family: 'Arial Black', Arial, sans-serif; font-style: italic; font-weight: bold; font-size: 10px; margin-top: 0.05cm; height:0.8cm; }
        .referencia td { vertical-align: center; padding: 1px 2px; }
        .small-box { margin-top:0.05cm; height:0.6cm; }
        .small-box table { width:100%; height:100%; border-collapse:collapse; border:1px solid #000; }
        .small-box td { padding:2px; font-family: Cambria, serif; font-weight:bold; font-size:8px; vertical-align:center; }
        .box { border: 1px solid #000; padding: 6px; margin-top: 0.25cm; font-family: Cambria, serif; font-weight: bold; font-size: 9px; height: 6.2cm; position: relative; box-sizing: border-box; }
        .box-left-inner { display:flex; gap:6px; width:100%; }
        .box-left-content { flex:0 0 44%; padding-right:6px; box-sizing:border-box; }
        .box-left-checkboxes { flex:0 0 24%; font-size:7px; display:flex; flex-direction:column; }
        .box-left-checkboxes label { display:block; margin-bottom:3px; white-space:nowrap; }
        .box-left-recibo { flex:0 0 28%; }
        .box-title { font-weight: bold; text-transform: uppercase; margin-bottom: 4px; font-size: 12px; }
        .recibo { width:100%; height:4.6cm; border:1px solid #000; font-size:9px; text-align:center; padding-top:0.3cm; box-sizing:border-box; }
        .footer { position: absolute; bottom: 0.5cm; left: 1cm; right: 1cm; font-size: 8px; text-align: center; }
        .footer div { line-height: 1.1; }
        .signature { position:absolute; bottom:4px; left:0; width:100%; text-align:center; font-size:9px; padding:2px; }
    </style>
</head>
<body>
<button id="printBtn" onclick="window.print()" class="btn btn-primary btn-sm" title="Imprimir hoja">
        <i class="fa fa-print" aria-hidden="true"></i> Imprimir
    </button>
<div class="page">
    <div class="header">
        <img src="../assets/img/logo_1.png" alt="EPDEOR LOGO" style="max-height: 80px; max-width: 320px;">
        <img src="../assets/img/logo_2.png" alt="EPDEOR" style="max-height: 100px; max-width: 320px;">
        <img src="../assets/img/logo_3.png" alt="G.A.D.OR." style="max-height: 70px; max-width: 320px;">
    </div>
    <!-- datos referenciales -->
    <table class="referencia" width="100%">
        <tr>
            <td colspan="2"><strong>Remitente:</strong> <?= $remitente ?></td>
            <td><strong>Hoja de ruta G.G.:</strong> <?= $hojaruta ?></td>
            <td><strong>Página:</strong> <?= $page ?></td>
        </tr>
        <tr>
            <td colspan="4"><strong>Referencia:</strong> <?= $referencia ?></td>
        </tr>
        <tr>
            <td><strong>Fecha:</strong> <?= $fecha_date ?></td>
            <td><strong>Hora:</strong> <?= $fecha_time ?></td>
            <td><strong>Fojas:</strong> <?= $fojas ?></td>
            <td></td>
        </tr>
    </table>

    <!-- Caja 1 pequeña: 1 DESTINATARIO (Gerente General) con fecha y hora -->
    <div class="small-box">
        <table>
            <tr>
                <td style="width:50%;"><strong>1 DESTINATARIO:</strong> <?= htmlspecialchars($gerente) ?></td>
                <td style="width:25%;"><strong>FECHA:</strong> </td>
                <td style="width:25%;"><strong>HORA:</strong> </td>
            </tr>
        </table>
    </div>

    <!-- cajas de destinatarios -->
    <?php foreach ($dest_range as $destIndex): ?>
        <div class="box">
        <div class="box-inner">
            <div class="box-left">
                <div class="box-left-inner">
                    <div class="box-left-content">
                        <div class="box-title"><?php echo ($destIndex == 1 ? '1 DESTINATARIO' : $destIndex.' DESTINATARIO'); ?>:</div>
                        <div></div>
                        <div><strong>LUGAR:</strong></div>
                        <div style="margin-top:6px;"><strong>INSTRUCCIÓN ADICIONAL:</strong></div>
                        <div style="height:3.2cm; margin-top:4px;"></div>
                    </div>
                    <div class="box-left-checkboxes">
                        <label><input type="checkbox" disabled> Urgente</label>
                        <label><input type="checkbox" disabled> Para conocimiento</label>
                        <label><input type="checkbox" disabled> Preparar respuesta</label>
                        <label><input type="checkbox" disabled> Procesar</label>
                        <label><input type="checkbox" disabled> Preparar informe</label>
                        <label><input type="checkbox" disabled> Archivo</label>
                    </div>
                    <div class="box-left-recibo">
                        <div style="font-size:8px; margin-bottom:4px;"><strong>Fojas:</strong><br><strong>Anexos:</strong></div>
                        <div class="recibo">RECIBIDO/SELLO/FIRMA</div>
                    </div>
                </div>
            </div>
            </div>
            <div class="signature">
                <div>FECHA (DD-MM-AA) &nbsp;&nbsp;&nbsp; HORA (HH.MM) &nbsp;&nbsp;&nbsp; FIRMA/NOMBRE/CARGO</div>
            </div>
        </div>
    <?php endforeach; ?>
    <!-- pie de página con datos de contacto -->
    <div class="footer">
        <div>Dirección: Rajka Bacovick entre Aroma y Villarroel – Edificio Empresa Pública Departamental Hotel Terminal-Terminal de Buses de Oruro “EPDEOR”</div>
        <div>Teléfonos: (591-2) 5276389 – 5279535 *Hotel Terminal de Oruro (591-2) 5276227</div>
        <div>Correo: epdeororuro@gmail.com</div>
        <div>Oruro – Bolivia</div>
    </div>
</div>
</body>
</html>
