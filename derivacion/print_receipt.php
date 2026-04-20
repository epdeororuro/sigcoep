<?php
session_start();
require '../db.php';

// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado.");
}

$id_derivacion = $_GET['id'] ?? null;
if (!$id_derivacion) {
    die("No se proporcionó ID de derivación.");
}

// Obtener los datos de la derivación unidos con el historial de impresiones
$sql = "SELECT d.*, c.hojaruta, c.referencia,
               f_dest.nombre AS dest_nombre, f_dest.paterno AS dest_paterno, f_dest.materno AS dest_materno,
               f_sender.nombre AS sender_nombre, f_sender.paterno AS sender_paterno, f_sender.materno AS sender_materno,
               hi.numero_historial
        FROM derivacion d
        INNER JOIN correspondencia c ON d.id_correspondencia = c.id
        LEFT JOIN funcionario f_dest ON d.id_funcionario = f_dest.id
        INNER JOIN historial_impresiones hi ON d.id = hi.id_derivacion
        LEFT JOIN funcionario f_sender ON hi.id_funcionario = f_sender.id
        WHERE d.id = :id AND hi.id_funcionario = :uid";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $id_derivacion, 
    ':uid' => $_SESSION['usuario_id']
]);
$derivacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$derivacion) {
    die("Derivación no encontrada o no tiene permisos para imprimirla.");
}

// Calcular posición en la hoja
$numero_historial = (int)$derivacion['numero_historial'];
$sector_actual = $numero_historial % 10;
if ($sector_actual === 0) {
    $sector_actual = 10; // Si es múltiplo de 10, va en el último sector
}

$hoja_actual = ceil($numero_historial / 10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante #<?= $numero_historial ?> - <?= htmlspecialchars($derivacion['referencia'] ?? '') ?></title>
    <style>
        body, html { margin: 0; padding: 0; background-color: #525659; font-family: Arial, sans-serif; }
        
        /* Contenedor de la hoja física tamaño Carta */
        .hoja-carta {
            width: 21.5cm; 
            height: 27.5cm; /* Reducido unos mm para evitar el salto a una 2da hoja */
            background: white; 
            margin: 1cm auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            display: flex; 
            flex-direction: column;
            box-sizing: border-box;
            padding: 0.5cm 1cm; /* Margen interno */
            overflow: hidden; /* Evita que cualquier sub-píxel empuje una página nueva */
        }
        
        /* Encabezado y Pie de página global */
        .print-header {
            display: flex; justify-content: space-between; align-items: center;
            height: 1.5cm; border-bottom: 2px solid #000; padding-bottom: 0.1cm; margin-bottom: 0.1cm;
        }
        .print-header img { max-height: 45px; }
        
        .print-footer {
            height: 0.8cm; border-top: 1px solid #000; margin-top: 0.1cm; padding-top: 0.1cm;
            text-align: center; font-size: 8px; line-height: 1.2; color: #333;
        }

        /* Contenedor de los 10 sectores (1 Columna ancha, 10 Filas) */
        .grid-container {
            flex-grow: 1;
            display: grid; 
            grid-template-columns: 1fr;
            grid-template-rows: repeat(10, 1fr);
            gap: 0.15cm;
        }
        
        .sector { box-sizing: border-box; overflow: hidden; }
        
        /* Mostrar guías en pantalla para ayudar al usuario a ver el orden, ocultas al imprimir */
        @media screen { .sector { border: 1px dashed #ddd; } }
        
        /* Diseño del comprobante estilo Planilla Historial */
        .contenido-ticket {
            border: 1px solid #000; height: 100%; display: flex; position: relative; color: #000; font-family: Arial, sans-serif;
        }
        .ticket-col-1 {
            width: 22%; border-right: 1px solid #000; padding: 5px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
        }
        .ticket-col-2 {
            width: 58%; border-right: 1px solid #000; padding: 5px 10px; display: flex; flex-direction: column; justify-content: space-evenly; font-size: 10px; text-transform: uppercase; overflow: hidden;
        }
        .ticket-col-3 {
            width: 20%; padding: 5px; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; background: rgba(255,255,255,0.9);
        }
        .ticket-index {
            position: absolute; top: 0; right: 0; border-left: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 8px; font-size: 11px; font-weight: bold; background: #eee;
        }
        .firma-label {
            font-size: 8px; color: #555; text-align: center; width: 100%; border-top: 1px dashed #ccc; padding-top: 4px;
        }
        .ticket-date { font-size: 11px; margin-bottom: 4px; }
        .ticket-hr { font-size: 18px; font-weight: 900; }
        .ticket-row { margin-bottom: 2px; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ticket-row strong { font-weight: 900; margin-right: 2px; }
        .ticket-footer-row { display: flex; justify-content: space-between; margin-top: 2px; padding-top: 2px; border-top: 1px dotted #ccc; }
        
        .no-print { text-align: center; padding: 15px; color: white; font-family: sans-serif;}
        .no-print button { padding: 10px 20px; font-size: 16px; cursor: pointer; font-weight: bold; background: #0d6efd; color: white; border: none; border-radius: 5px;}

        /* Configuración estricta para la impresora */
        @media print {
            body, html { background: none; margin: 0; padding: 0; }
            .hoja-carta { margin: 0; box-shadow: none; border: none; width: 21.5cm; height: 27.5cm; padding: 0.5cm 1cm;}
            .no-print { display: none; }
            @page { size: letter portrait; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <h2>Atención</h2>
        <p style="font-size: 1.2rem;">Por favor, asegúrese de insertar la <strong>Hoja N° <?= $hoja_actual ?></strong> de su historial en la impresora.</p>
        <p>El comprobante se imprimirá en el <strong>Sector <?= $sector_actual ?></strong>.</p>
        
        <div style="margin: 15px 0; background: rgba(0,0,0,0.2); padding: 10px; display: inline-block; border-radius: 8px;">
            <label style="cursor: pointer; font-size: 1rem; font-weight: bold;">
                <input type="checkbox" id="chkMembrete" checked onchange="toggleMembrete()" style="transform: scale(1.5); margin-right: 10px;"> 
                Imprimir Encabezado y Pie de Página
            </label>
            <br><small style="color: #ffc107;">(Desmárquelo si la hoja física ya tiene el membrete impreso de un sector anterior)</small>
        </div><br>

        <button onclick="window.print()">Imprimir Ahora</button>
    </div>

    <script>
        function toggleMembrete() {
            const isChecked = document.getElementById('chkMembrete').checked;
            // visibility: hidden oculta la tinta pero MANTIENE el espacio para no desfasar la cuadrícula
            document.querySelectorAll('.print-header, .print-footer').forEach(el => {
                el.style.visibility = isChecked ? 'visible' : 'hidden';
            });
        }
    </script>
    
    <div class="hoja-carta">
        <?php
            // Construir nombres completos para mostrar
            $sender_fullname = trim(($derivacion['sender_nombre'] ?? '') . ' ' . ($derivacion['sender_paterno'] ?? '') . ' ' . ($derivacion['sender_materno'] ?? ''));
            $dest_fullname = trim(($derivacion['dest_nombre'] ?? '') . ' ' . ($derivacion['dest_paterno'] ?? '') . ' ' . ($derivacion['dest_materno'] ?? ''));
        ?>
        <!-- Encabezado Global -->
        <div class="print-header">
            <img src="../assets/img/logo_1.png" alt="EPDEOR LOGO">
            <img src="../assets/img/logo_2.png" alt="EPDEOR">
            <img src="../assets/img/logo_3.png" alt="G.A.D.OR.">
        </div>

        <!-- Cuadrícula de 10 sectores -->
        <div class="grid-container">
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <div class="sector">
                    <?php if ($i === $sector_actual): ?>
                        <div class="contenido-ticket">
                            <div class="ticket-col-1">
                                <div class="ticket-date"><?= date('d/m/Y H:i', strtotime($derivacion['fecha_derivacion'])) ?></div>
                                <div class="ticket-hr"><?= htmlspecialchars($derivacion['hojaruta'] ?? '') ?></div>
                            </div>
                            <div class="ticket-col-2">
                                <div class="ticket-row"><strong>DE:</strong> <?= htmlspecialchars($sender_fullname) ?></div>
                                <div class="ticket-row"><strong>A:</strong> <?= htmlspecialchars($dest_fullname) ?></div>
                                
                                <div class="ticket-row"><strong>REF INICIAL:</strong> <?= htmlspecialchars(substr($derivacion['referencia'] ?? '', 0, 100)) ?><?= strlen($derivacion['referencia'] ?? '') > 100 ? '...' : '' ?></div>
                                <div class="ticket-row"><strong>PROV.:</strong> <?= htmlspecialchars(substr($derivacion['instruccion_adicional'] ?? '', 0, 100)) ?><?= strlen($derivacion['instruccion_adicional'] ?? '') > 100 ? '...' : '' ?></div>
                                
                                <div class="ticket-footer-row">
                                    <span><strong>FOJAS:</strong> <?= htmlspecialchars($derivacion['fojas'] ?? '0') ?></span>
                                    <span><strong>TIPO:</strong> <?= htmlspecialchars(substr($derivacion['caracter'] ?? '', 0, 15)) ?></span>
                                </div>
                            </div>
                            <div class="ticket-col-3">
                                <div class="ticket-index"><?= $numero_historial ?></div>
                                <div class="firma-label">FIRMA/SELLO</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Pie de Página Global -->
        <div class="print-footer">
            <div>Dirección: Rajka Bacovick entre Aroma y Villarroel – Edificio Empresa Pública Departamental Hotel Terminal-Terminal de Buses de Oruro “EPDEOR”</div>
            <div>Teléfonos: (591-2) 5276389 – 5279535 *Hotel Terminal de Oruro (591-2) 5276227</div>
            <div>Correo: epdeororuro@gmail.com</div>
            <div>Oruro – Bolivia</div>
        </div>
    </div>
</body>
</html>