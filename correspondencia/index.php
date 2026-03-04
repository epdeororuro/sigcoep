<?php 
session_start(); 
if (isset($_SESSION['mensaje'])) { 
    echo ' <div class="alert alert-success alert-dismissible fade show" role="alert"> ' . $_SESSION['mensaje'] . ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>'; 
    unset($_SESSION['mensaje']); 
}
require '../db.php';

// Obtener lista de funcionarios para el selector de destino
try {
    $stmtFunc = $pdo->prepare("SELECT id, nombre, paterno, materno FROM funcionario WHERE estado = 'Activo' ORDER BY nombre, paterno");
    $stmtFunc->execute();
    $funcionarios = $stmtFunc->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $funcionarios = [];
}
?> 
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Funcionarios</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 1rem;
        }
        table.dataTable {
            width: 100% !important;
        }
        .card-body {
            padding: 1.5rem;
        }
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }
        table.dataTable th,
        table.dataTable td {
            white-space: nowrap;
        }
        .dataTables_wrapper .dataTables_scroll {
            overflow: auto;
        }

        /* Reducir tamaño de fuente de la tabla */
        #correspondencia th,
        #correspondencia td {
            font-size: 0.75rem;
            padding: 0.4rem !important;
        }

        #correspondencia thead th {
            padding: 0.5rem !important;
        }
        .table-responsive {
            font-size: 0.7rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0">Lista de Correspondencia</h3>
                            <?php if (isset($_SESSION['usuario_cargo']) && in_array(strtolower($_SESSION['usuario_cargo']), ['secretaria', 'administrador'])): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCorrespondenciaModal">
                                <i class="bi bi-envelope-plus"></i> Nueva Correspondencia
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="table-responsive">
                            <table id="correspondencia" class="table table-striped table-bordered align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Hoja Ruta</th>
                                        <th>Remitente</th>
                                        <th>Referencia</th>
                                        <th>Fojas</th>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nueva Correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createCorrespondenciaForm" action="store.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Hoja de Ruta</label>
                            <input type="text" class="form-control" name="hojaruta">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remitente</label>
                            <input type="text" class="form-control" name="remitente">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <textarea class="form-control" name="referencia"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <input type="text" class="form-control" name="fojas">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" form="createCorrespondenciaForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL EDITAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="editCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCorrespondenciaForm" action="update.php" method="post">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label class="form-label">hojaruta</label>
                            <input type="text" class="form-control" id="edit_hojaruta" name="hojaruta">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remitente</label>
                            <input type="text" class="form-control" id="edit_remitente" name="remitente">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <textarea class="form-control" id="edit_referencia" name="referencia"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <textarea class="form-control" id="edit_fojas" name="fojas"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" form="editCorrespondenciaForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL DERIVAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="derivarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Derivar Correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="derivarCorrespondenciaForm" action="../derivacion/store.php" method="post">
                            <input type="hidden" id="derivar_id_correspondencia" name="id_correspondencia">
                            <input type="hidden" id="derivar_id_funcionario" name="id_funcionario">
                        <div class="mb-3">
                            <label class="form-label">Derivar a (seleccione):</label>
                            <select id="derivar_select_funcionario" class="form-select" required>
                                <option value="">-- Seleccione funcionario/área --</option>
                                <?php foreach($funcionarios as $f): ?>
                                        <option value="<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instrucción adicional</label>
                            <textarea class="form-control" id="derivar_instruccion" name="instruccion_adicional" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <input type="number" min="0" class="form-control" id="derivar_fojas" name="fojas">
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
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" form="derivarCorrespondenciaForm">Derivar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL IMPRESIÓN DE PÁGINA ================= -->
    <div class="modal fade" id="printPageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Indica el número de página de hoja de ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="printPageForm" action="reporte.php" method="post" target="_blank">
                        <input type="hidden" id="print_correspondencia_id" name="id">
                        <div class="mb-3">
                            <label class="form-label">Número de página (1-10)</label>
                            <input type="number" min="1" max="10" class="form-control" id="print_page_number" name="page" value="1" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" form="printPageForm">Generar Hoja</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= JS ================= -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#correspondencia').DataTable({
                ajax: 'show.php',
                scrollX: true,
                autoWidth: false,
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                columns: [
                    { data: 'hojaruta' },
                    { data: 'remitente' },
                    { data: 'referencia' },
                    { data: 'fojas' },
                    { data: 'fecha' },
                    { data: 'estado' },
                    { data: 'acciones' }
                ]
            });
        });

        function editarCorrespondencia(id) {
            $.ajax({
                type: 'POST',
                url: 'edit.php',
                data: {id: id},
                dataType: 'json',
                success: function(data) {
                    $('#edit_id').val(data.id);
                    $('#edit_hojaruta').val(data.hojaruta);
                    $('#edit_remitente').val(data.remitente);
                    $('#edit_referencia').val(data.referencia);
                    $('#edit_fojas').val(data.fojas);
                    $('#editCorrespondenciaModal').modal('show');
                }
            });
        }

        function derivarCorrespondencia(id) {
            // Cargar el ID en el campo oculto del modal
            $('#derivar_id_correspondencia').val(id);
            // limpiar campos previos
            $('#derivar_select_funcionario').val('');
            $('#derivar_id_funcionario').val('');
            $('#derivar_instruccion').val('');
            $('#derivar_fojas').val('');
            $('#derivar_caracter').val('Urgente');
            // Mostrar el modal
            $('#derivarCorrespondenciaModal').modal('show');
        }

        // Cuando el usuario seleccione un funcionario, guardamos el id en el input oculto
        $(document).on('change', '#derivar_select_funcionario', function() {
            var fid = $(this).val();
            $('#derivar_id_funcionario').val(fid);
        });

        // función para abrir el modal de impresión y fijar el id
        function solicitarPagina(id) {
            $('#print_correspondencia_id').val(id);
            $('#print_page_number').val(1);
            $('#printPageModal').modal('show');
        }
    </script>
</body>
</html>