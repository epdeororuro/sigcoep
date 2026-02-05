<?php
session_start();
if (isset($_SESSION['mensaje'])) {
    echo '
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ' . $_SESSION['mensaje'] . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    unset($_SESSION['mensaje']);
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
                        <h3 class="mb-0">Lista de Funcionarios</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFuncionarioModal">
                            <i class="bi bi-person-plus"></i> Nuevo Funcionario
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="funcionarios" class="table table-striped table-bordered align-middle w-100">
                            <thead class="table-primary">
                                <tr>
                                    <th>N°</th>
                                    <th>C.I.</th>
                                    <th>Nombre</th>
                                    <th>Apellido Paterno</th>
                                    <th>Apellido Materno</th>
                                    <th>Cargo</th>
                                    <th>Área</th>
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

<!-- ================= MODAL NUEVO FUNCIONARIO ================= -->
<div class="modal fade" id="createFuncionarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Funcionario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createFuncionarioForm" action="store.php" method="post">

                    <div class="mb-3">
                        <label class="form-label">Carnet Identidad</label>
                        <input type="text" class="form-control" name="ci">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" name="paterno">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" name="materno">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <select class="form-select" name="cargo">
                            <option>Administrador</option>
                            <option>Administrativo</option>
                            <option>Operativo</option>
                            <option>Consultor</option>
                            <option>Eventual</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Área</label>
                        <select class="form-select" name="area">
                            <option>Gerencia</option>
                            <option>JDAF</option>
                            <option>JDOHTO</option>
                            <option>JDOTBO</option>
                        </select>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" form="createFuncionarioForm">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL EDITAR FUNCIONARIO ================= -->
<div class="modal fade" id="editFuncionarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Funcionario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editFuncionarioForm" action="update.php" method="post">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label class="form-label">Carnet Identidad</label>
                        <input type="text" class="form-control" id="edit_ci" name="ci">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="edit_paterno" name="paterno">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" id="edit_materno" name="materno">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <select class="form-select" id="edit_cargo" name="cargo">
                            <option>Administrador</option>
                            <option>Administrativo</option>
                            <option>Operativo</option>
                            <option>Consultor</option>
                            <option>Eventual</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Área</label>
                        <select class="form-select" id="edit_area" name="area">
                            <option>Gerencia</option>
                            <option>JDAF</option>
                            <option>JDOHTO</option>
                            <option>JDOTBO</option>
                        </select>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" form="editFuncionarioForm">Guardar</button>
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
    $('#funcionarios').DataTable({
        ajax: 'getFuncionarios.php',
        scrollX: true,
        autoWidth: false,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        columns: [
            { data: 'numero' },
            { data: 'ci' },
            { data: 'nombre' },
            { data: 'paterno' },
            { data: 'materno' },
            { data: 'cargo' },
            { data: 'area' },
            { data: 'estado' },
            { data: 'acciones' }
        ]
    });
});

function editarFuncionario(id) {
    $.ajax({
        type: 'POST',
        url: 'edit.php',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            $('#edit_id').val(data.id);
            $('#edit_ci').val(data.ci);
            $('#edit_nombre').val(data.nombre);
            $('#edit_paterno').val(data.paterno);
            $('#edit_materno').val(data.materno);
            $('#edit_cargo').val(data.cargo);
            $('#edit_area').val(data.area);
            $('#editFuncionarioModal').modal('show');
        }
    });
}
</script>

</body>
</html>
