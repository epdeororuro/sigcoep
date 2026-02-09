<?php session_start(); if (isset($_SESSION['mensaje'])) { echo ' <div class="alert alert-success alert-dismissible fade show" role="alert"> ' . $_SESSION['mensaje'] . ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>'; unset($_SESSION['mensaje']); } ?> 
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

        /* Card estilo AdminLTE */
        .card {
            border-radius: 1rem;
        }

        /* DataTable full width */
        table.dataTable {
            width: 100% !important;
        }

        /* Quitar padding excesivo */
        .card-body {
            padding: 1.5rem;
        }

        /* Header DataTable */
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }
        /* Evita que las celdas se aplasten */
        table.dataTable th,
        table.dataTable td {
            white-space: nowrap;
        }

        /* Scroll limpio */
        .dataTables_wrapper .dataTables_scroll {
            overflow: auto;
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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCorrespondenciaModal">
                                <i class="bi bi-envelope-plus"></i> Nueva Correspondencia
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table id="correspondencia" class="table table-striped table-bordered align-middle w-100">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Cite</th>
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
                            <label class="form-label">Cite</label>
                            <input type="text" class="form-control" name="cite">
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
                            <label class="form-label">Cite</label>
                            <input type="text" class="form-control" id="edit_cite" name="cite">
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
                    { data: 'cite' },
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
                    $('#edit_cite').val(data.cite);
                    $('#edit_remitente').val(data.remitente);
                    $('#edit_referencia').val(data.referencia);
                    $('#edit_fojas').val(data.fojas);
                    $('#editCorrespondenciaModal').modal('show');
                }
            });
        }
    </script>
</body>
</html>