<?php 
session_start(); 
if (isset($_SESSION['mensaje'])) { 
    $tipo = $_SESSION['mensaje_tipo'] ?? 'success';
    $clase_alert = $tipo === 'danger' ? 'alert-danger' : 'alert-success';
    echo ' <div class="alert ' . $clase_alert . ' alert-dismissible fade show" role="alert"> ' . $_SESSION['mensaje'] . ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>'; 
    unset($_SESSION['mensaje']); 
    unset($_SESSION['mensaje_tipo']);
}
require '../db.php';

// Obtener lista de funcionarios para el selector de destino
try {
    $stmtFunc = $pdo->prepare("
        SELECT f.id, f.nombre, f.paterno, f.materno, p.sigla 
        FROM funcionario f 
        LEFT JOIN puesto p ON f.id_puesto = p.id 
        WHERE f.estado = 'Activo' AND LOWER(f.rol) NOT IN ('administrador', 'secretaria', 'archivista central') 
        ORDER BY f.nombre, f.paterno
    ");
    $stmtFunc->execute();
    $funcionarios = $stmtFunc->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $funcionarios = [];
}

// Obtener lista de comisiones para el selector de destino
try {
    $stmtCom = $pdo->prepare("SELECT id, nombre FROM comision WHERE estado = 'Activo' ORDER BY nombre");
    $stmtCom->execute();
    $comisiones = $stmtCom->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $comisiones = [];
}

// Calcular siguiente número de hoja de ruta basado en el total de registros
try {
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM correspondencia");
    $totalCorrespondencia = (int) $stmtCount->fetchColumn();
} catch (Exception $e) {
    $totalCorrespondencia = 0;
}
$siguienteNumeroHojaRuta = $totalCorrespondencia + 1;
$anioActualHojaRuta = date('Y');
$siguienteHojaRuta = $siguienteNumeroHojaRuta . '/' . $anioActualHojaRuta;

$cargo_usuario_sesion = strtolower($_SESSION['usuario_cargo'] ?? '');
$vista = $_GET['view'] ?? 'activas';

if ($vista === 'concluidos' && $cargo_usuario_sesion === 'secretaria') {
    $vista = 'activas'; // Redirigir a sus bandejas activas si intenta entrar por URL
}

$titulo_vista = 'Lista de Correspondencia';
if ($vista === 'concluidos') $titulo_vista = 'Concluidos y Revisión';
if ($vista === 'archivo') $titulo_vista = 'Archivo Central';
?> 
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Correspondencia</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/correspondencia.css?v=<?= time() ?>" >
    <!-- Theme Script -->
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0"><?= $titulo_vista ?></h3>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <?php if (isset($_SESSION['usuario_cargo']) && !in_array(strtolower($_SESSION['usuario_cargo']), ['secretaria'])): ?>
                                <?php if (!in_array(strtolower($_SESSION['usuario_cargo']), ['gerente'])): ?> <form id="formLibroEntregas" action="delivery_history.php" method="POST" class="d-flex align-items-center bg-body-secondary border p-1 rounded">
                                    <span class="ms-1 me-2 small fw-bold text-body">Historial de Entregas:</span>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm me-1" value="<?= date('Y-m-d') ?>" required title="Fecha de inicio">
                                    <input type="date" name="fecha_fin" class="form-control form-control-sm me-2" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required title="Fecha de fin">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Generar Libro de Entregas">
                                        <i class="bi bi-printer"></i> Generar
                                        </button>
                                </form>
                                <?php endif;?><?php endif; ?>
                                <?php if (isset($_SESSION['usuario_cargo']) && in_array(strtolower($_SESSION['usuario_cargo']), ['secretaria', 'administrador'])): ?>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCorrespondenciaModal">
                                    <i class="bi bi-envelope-plus"></i> Nueva Correspondencia
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Pestañas (Tabs) de Filtro según el Rol -->
                        <?php 
                        $cargo_usuario = $cargo_usuario_sesion;
                        
                        if ($vista === 'concluidos'): 
                            $filtro_concluido = in_array($cargo_usuario, ['administrador']) ? 'concluido' : 'concluidos';
                        ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="<?= $filtro_concluido ?>" type="button"><i class="bi bi-check2-all"></i> Concluidos <span class="badge bg-secondary ms-1 count-badge" data-count="<?= $filtro_concluido ?>">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="revision" type="button"><i class="bi bi-search"></i> En Revisión <span class="badge bg-secondary ms-1 count-badge" data-count="revision">0</span></button></li>
                            </ul>
                        <?php elseif ($vista === 'archivo'): 
                            $filtro_archivo = in_array($cargo_usuario, ['administrador', 'secretaria']) ? 'archivado' : 'archivo_central';
                        ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="<?= $filtro_archivo ?>" type="button"><i class="bi bi-archive-fill"></i> Archivo Central <span class="badge bg-secondary ms-1 count-badge" data-count="<?= $filtro_archivo ?>">0</span></button></li>
                            </ul>
                        <?php else: // $vista === 'activas' ?>
                            <?php if ($cargo_usuario === 'administrativo'): ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="pendientes" type="button"><i class="bi bi-clock-history"></i> Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="pendientes">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="despachados" type="button"><i class="bi bi-send"></i> Derivados <span class="badge bg-secondary ms-1 count-badge" data-count="despachados">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="iniciados" type="button"><i class="bi bi-play-circle"></i> Iniciados <span class="badge bg-secondary ms-1 count-badge" data-count="iniciados">0</span></button></li>
                            </ul>
                            <?php elseif ($cargo_usuario === 'gerente'): ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="pendientes" type="button"><i class="bi bi-clock-history"></i> Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="pendientes">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="despachados" type="button"><i class="bi bi-send"></i> Derivados <span class="badge bg-secondary ms-1 count-badge" data-count="despachados">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="para_iniciar" type="button"><i class="bi bi-play-circle"></i> Para iniciar <span class="badge bg-secondary ms-1 count-badge" data-count="para_iniciar">0</span></button></li>
                            </ul>
                            <?php elseif ($cargo_usuario === 'administrador'): ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="todos_activos" type="button">Todos <span class="badge bg-secondary ms-1 count-badge" data-count="todos_activos">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="registrado" type="button">Registrados <span class="badge bg-secondary ms-1 count-badge" data-count="registrado">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="iniciado" type="button">Iniciados <span class="badge bg-secondary ms-1 count-badge" data-count="iniciado">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="derivado" type="button">Derivados <span class="badge bg-secondary ms-1 count-badge" data-count="derivado">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="aceptado" type="button">Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="aceptado">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="agrupado" type="button">Agrupados <span class="badge bg-secondary ms-1 count-badge" data-count="agrupado">0</span></button></li>
                            </ul>
                            <?php elseif ($cargo_usuario === 'secretaria'): ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="todos_activos" type="button"><i class="bi bi-folder2-open"></i> Todos <span class="badge bg-secondary ms-1 count-badge" data-count="todos_activos">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="no_cursadas" type="button"><i class="bi bi-slash-circle"></i> No Cursadas <span class="badge bg-danger ms-1 count-badge" data-count="no_cursadas">0</span></button></li>
                            </ul>
                            <?php elseif ($cargo_usuario === 'archivista central'): ?>
                            <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                                <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button></li>
                                <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="pendientes" type="button"><i class="bi bi-clock-history"></i> Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="pendientes">0</span></button></li>
                            </ul>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="correspondencia" class="table table-striped table-bordered align-middle text-center w-100">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th>Hoja de ruta</th>
                                        <th>Remitente</th>
                                        <th>Referencia</th>
                                        <th>Fojas</th>
                                        <th>Anexo</th>
                                        <th>Foto</th>
                                        <th>Fecha/Hora</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '_modals.php'; ?>
    <!-- ================= JS ================= -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons para Excel -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/correspondencia.js?v=<?= time() ?>"></script>
</body>

</html>