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
    $stmtFunc = $pdo->prepare("SELECT id, nombre, paterno, materno FROM funcionario WHERE estado = 'Activo' AND LOWER(rol) NOT IN ('administrador', 'secretaria') ORDER BY nombre, paterno");
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
                            <h3 class="mb-0">Lista de Correspondencia</h3>
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
                        $cargo_usuario = strtolower($_SESSION['usuario_cargo'] ?? '');
                        if ($cargo_usuario === 'administrativo'): 
                        ?>
                        <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                            <li class="nav-item">
                                <button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="pendientes" type="button"><i class="bi bi-clock-history"></i> Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="pendientes">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="despachados" type="button"><i class="bi bi-send"></i> Derivados <span class="badge bg-secondary ms-1 count-badge" data-count="despachados">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="iniciados" type="button"><i class="bi bi-play-circle"></i> Iniciados <span class="badge bg-secondary ms-1 count-badge" data-count="iniciados">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="mis_archivos" type="button"><i class="bi bi-archive"></i> Mis Archivos <span class="badge bg-secondary ms-1 count-badge" data-count="mis_archivos">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="archivo_central" type="button"><i class="bi bi-archive-fill"></i> Archivo Central <span class="badge bg-secondary ms-1 count-badge" data-count="archivo_central">0</span></button>
                            </li>
                        </ul>
                        <?php elseif ($cargo_usuario === 'gerente'): ?>
                        <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                            <li class="nav-item">
                                <button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Bandeja de Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="pendientes" type="button"><i class="bi bi-clock-history"></i> Bandeja de Pendientes <span class="badge bg-secondary ms-1 count-badge" data-count="pendientes">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="despachados" type="button"><i class="bi bi-send"></i> Bandeja de Despachados <span class="badge bg-secondary ms-1 count-badge" data-count="despachados">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="para_iniciar" type="button"><i class="bi bi-play-circle"></i> Bandeja para Iniciar <span class="badge bg-secondary ms-1 count-badge" data-count="para_iniciar">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="mis_archivos" type="button"><i class="bi bi-archive"></i> Mis Archivos <span class="badge bg-secondary ms-1 count-badge" data-count="mis_archivos">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="archivo_central" type="button"><i class="bi bi-archive-fill"></i> Archivo Central <span class="badge bg-secondary ms-1 count-badge" data-count="archivo_central">0</span></button>
                            </li>
                        </ul>
                        <?php elseif ($cargo_usuario === 'administrador'): ?>
                        <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                            <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="todos" type="button">Todos <span class="badge bg-secondary ms-1 count-badge" data-count="todos">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="registrado" type="button">Registrados <span class="badge bg-secondary ms-1 count-badge" data-count="registrado">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="iniciado" type="button">Iniciados <span class="badge bg-secondary ms-1 count-badge" data-count="iniciado">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="derivado" type="button">Derivados <span class="badge bg-secondary ms-1 count-badge" data-count="derivado">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="aceptado" type="button">Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="aceptado">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="archivado" type="button">Archivados <span class="badge bg-secondary ms-1 count-badge" data-count="archivado">0</span></button></li>
                        </ul>
                        <?php elseif ($cargo_usuario === 'secretaria'): ?>
                        <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                            <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="todos" type="button"><i class="bi bi-folder2-open"></i> Todos <span class="badge bg-secondary ms-1 count-badge" data-count="todos">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="no_cursadas" type="button"><i class="bi bi-slash-circle"></i> No Cursadas <span class="badge bg-danger ms-1 count-badge" data-count="no_cursadas">0</span></button></li>
                        </ul>
                        <?php elseif ($cargo_usuario === 'archivista central'): ?>
                        <ul class="nav nav-tabs mb-3" id="correspondenciaTabs">
                            <li class="nav-item">
                                <button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Bandeja de Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="pendientes" type="button"><i class="bi bi-clock-history"></i> Bandeja de Pendientes <span class="badge bg-secondary ms-1 count-badge" data-count="pendientes">0</span></button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link filtro-tab" data-filtro="archivo_central" type="button"><i class="bi bi-archive-fill"></i> Archivo Central <span class="badge bg-secondary ms-1 count-badge" data-count="archivo_central">0</span></button>
                            </li>
                        </ul>
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
    <!-- ================= MODAL NUEVA CORRESPONDENCIA ================= -->
    <div class="modal fade" id="createCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-envelope-plus"></i> Agregar Nueva Correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createCorrespondenciaForm" action="store.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Hoja de ruta</label>
                            <input type="text" class="form-control" name="hojaruta" value="<?= htmlspecialchars($siguienteHojaRuta) ?>" readonly>
                        </div>
                        
                        <!-- Remitente Interno (Select) -->
                        <div class="mb-3" id="div_remitente_interno">
                            <label class="form-label">Seleccione Funcionario</label>
                            <select class="form-select border-4" id="select_remitente_interno" name="remitente_id">
                                <option value="">-- Seleccione un funcionario --</option>
                                <?php foreach($funcionarios as $f): ?>
                                    <option value="<?= htmlspecialchars($f['id']) ?>">
                                        <?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Remitente Externo (Input) -->
                        <div class="mb-3" id="div_remitente_externo" style="display: none;">
                            <label class="form-label">Nombre del Remitente Externo</label>
                            <input type="text" class="form-control border-4" id="input_remitente_externo" name="remitente_externo">
                        </div>
                        
                        <!-- Tipo de Remitente (Checkbox) -->
                        <div class="mb-3">
                            <input type="hidden" name="tipo_remitente" id="hidden_tipo_remitente" value="interno">
                            <div class="border p-2 rounded bg-light">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input class="form-check-input fs-5 mt-0" type="checkbox" id="checkbox_remitente_externo">
                                    <label class="form-check-label fw-bold ms-2" for="checkbox_remitente_externo">
                                        Remitente Externo
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <textarea class="form-control border-4" name="referencia" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <textarea class="form-control border-4" id="fojas" name="fojas" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Anexo</label>
                            <textarea class="form-control border-4" id="anexo" name="anexo"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto obligatorio</label>
                            <input type="file" class="form-control border-4" name="foto" accept="image/*" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" form="createCorrespondenciaForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL EDITAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="editCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCorrespondenciaForm" action="update.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id" name="id">
                        <input type="hidden" id="edit_foto_actual" name="foto_actual">
                        <div class="mb-3">
                            <label class="form-label">Hoja de ruta</label>
                            <input type="text" class="form-control border-4" id="edit_hojaruta" name="hojaruta">
                        </div>
                        <!-- Remitente Interno (Select) -->
                        <div class="mb-3" id="edit_div_remitente_interno">
                            <label class="form-label">Seleccione Funcionario</label>
                            <select class="form-select border-4" id="edit_select_remitente_interno" name="edit_remitente_id">
                                <option value="">-- Seleccione un funcionario --</option>
                                <?php foreach($funcionarios as $f): ?>
                                    <option value="<?= htmlspecialchars($f['id']) ?>">
                                        <?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Remitente Externo (Input) -->
                        <div class="mb-3" id="edit_div_remitente_externo" style="display: none;">
                            <label class="form-label">Nombre del Remitente Externo</label>
                            <input type="text" class="form-control border-4" id="edit_input_remitente_externo" name="edit_remitente_externo">
                        </div>
                        <!-- Tipo de Remitente (Checkbox) -->
                        <div class="mb-3">
                            <input type="hidden" name="edit_tipo_remitente" id="edit_hidden_tipo_remitente" value="interno">
                            <div class="border p-2 rounded bg-light">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input class="form-check-input fs-5 mt-0" type="checkbox" id="edit_checkbox_remitente_externo">
                                    <label class="form-check-label fw-bold ms-2" for="edit_checkbox_remitente_externo">
                                        Remitente Externo
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <textarea class="form-control border-4" id="edit_referencia" name="referencia"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <textarea class="form-control border-4" id="edit_fojas" name="fojas"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Anexo</label>
                            <textarea class="form-control border-4" id="edit_anexo" name="edit_anexo"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto actual</label>
                            <div id="edit_foto_preview" class="mb-2 border-4"></div>
                            <label class="form-label">Cambiar foto (opcional)</label>
                            <input type="file" class="form-control" name="foto_nueva" accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark" form="editCorrespondenciaForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL VER FOTO ================= -->
    <div class="modal fade" id="fotoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto de la correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="fotoModalImg" src="" alt="Foto de la correspondencia" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL DERIVAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="derivarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-right-circle"></i> Derivar Correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="derivarCorrespondenciaForm" action="../derivacion/store.php" method="post">
                        <input type="hidden" id="derivar_id_correspondencia" name="id_correspondencia">
                        <input type="hidden" id="derivar_id_funcionario" name="id_funcionario">
                        <div class="mb-3">
                            <label class="form-label">Derivar a (seleccione):</label>
                            <select id="derivar_select_funcionario" class="form-select border-4" required>
                                <option value="">-- Funcionario --</option>
                                <!--<optgroup label="Comisiones">
                                    <?php foreach($comisiones as $c): ?>
                                        <option value="c_<?= htmlspecialchars($c['id']) ?>"><i class="bi bi-people"></i> <?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>-->
                                <optgroup label="Funcionarios">
                                    <?php foreach($funcionarios as $f): ?>
                                    <option value="f_<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instrucción adicional</label>
                            <textarea class="form-control border-4" id="derivar_instruccion" name="instruccion_adicional"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas a añadir</label>
                            <input type="number" min="0" class="form-control border-4" id="derivar_fojas"
                                name="fojas">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Carácter</label>
                            <select class="form-select" id="derivar_caracter" name="caracter" required>
                                <option value="Urgente">Urgente</option>
                                <option value="Para conocimiento">Para conocimiento</option>
                                <option value="Preparar Respuesta">Preparar Respuesta</option>
                                <option value="Procesar">Procesar</option>
                                <option value="Preparar Informe">Preparar Informe</option>
                                <option value="Archivo">Archivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" form="derivarCorrespondenciaForm">Derivar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL IMPRESIÓN DE PÁGINA ================= -->
    <div class="modal fade" id="printPageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-info">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title"><i class="bi bi-printer"></i> Indica el número de página de hoja de ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="printPageForm" action="report.php" method="post">
                        <input type="hidden" id="print_correspondencia_id" name="id">
                        <div class="mb-3">
                            <label class="form-label">Número de página (1-10)</label>
                            <input type="number" min="1" max="10" class="form-control border-4" id="print_page_number" name="page" value="1" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" form="printPageForm">Generar hoja</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL ELIMINAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="deleteCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash"></i> Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta correspondencia?</p>
                    <form id="deleteCorrespondenciaForm" action="destroy.php" method="post">
                        <input type="hidden" id="delete_correspondencia_id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="deleteCorrespondenciaForm">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL ACEPTAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="aceptarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Aceptar correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="aceptarCorrespondenciaForm" action="accept.php" method="post">
                        <input type="hidden" id="aceptar_correspondencia_id" name="id">
                        <p class="mb-0 fs-6">¿Confirma que desea marcar esta correspondencia como <strong>Aceptada</strong>?</p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" form="aceptarCorrespondenciaForm">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL RECHAZAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="rechazarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="rechazarModalTitle">Rechazar correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rechazarCorrespondenciaForm" action="reject.php" method="post">
                        <input type="hidden" id="rechazar_correspondencia_id" name="id">
                        <input type="hidden" id="rechazar_estado_destino" name="estado_destino" value="Rechazado">
                        <input type="hidden" id="rechazar_action_type" name="action_type" value="final">

                        <p id="rechazarModalText">¿Confirma que desea <strong>Rechazar</strong> esta correspondencia?</p>

                        <div class="mb-3" id="motivoRechazoContainer" style="display: none;">
                            <label for="motivo_rechazo" class="form-label">Motivo de la Devolución:</label>
                            <textarea class="form-control border border-danger border-2 shadow-sm" id="motivo_rechazo" name="motivo_rechazo" rows="3" placeholder="Describa el motivo por el cual devuelve esta correspondencia..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="rechazarCorrespondenciaForm" id="rechazarModalSubmitBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL ARCHIVAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="archivarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-dark">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-archive"></i> Archivar Correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="archivarCorrespondenciaForm" action="archive.php" method="post">
                        <input type="hidden" id="archivar_correspondencia_id" name="id">
                        
                        <?php if ($cargo_usuario === 'archivista central'): ?>
                            <input type="hidden" name="tipo_archivo" value="personal">
                            <p class="mb-3 text-center">El documento será resguardado definitivamente en el <strong>Archivo Central</strong>.</p>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">Destino del Archivo:</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input border-dark" type="radio" name="tipo_archivo" id="archivo_personal" value="personal" checked>
                                    <label class="form-check-label" for="archivo_personal"><strong>Archivo Personal</strong> (El trámite finaliza en su escritorio)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input border-dark" type="radio" name="tipo_archivo" id="archivo_central" value="central">
                                    <label class="form-check-label" for="archivo_central"><strong>Archivo Central</strong> (Enviar al Archivista Institucional)</label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nota o Ubicación Física <small class="text-muted">(Opcional)</small></label>
                            <textarea class="form-control border-dark" name="nota_archivo" rows="2" placeholder="Ej: Gaveta 3, Archivador A..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" form="archivarCorrespondenciaForm">Archivar</button>
                </div>
            </div>
        </div>
    </div>
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