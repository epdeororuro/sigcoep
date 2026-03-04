<?php
session_start();
require '../db.php';
// cargar lista de puestos para los selects
$puestos = $pdo->query("SELECT id, descripcion FROM puesto ORDER BY descripcion")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['mensaje'])) {
    $tipo = isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : 'success';
    $clase_alert = $tipo === 'danger' ? 'alert-danger' : 'alert-success';
    echo '
    <div class="alert ' . $clase_alert . ' alert-dismissible fade show" role="alert">
        ' . $_SESSION['mensaje'] . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
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
                        <h3 class="mb-0">Lista de Funcionarios</h3>
                        <?php if (isset($_SESSION['usuario_cargo']) && strtolower($_SESSION['usuario_cargo']) === 'administrador'): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFuncionarioModal">
                            <i class="bi bi-person-plus"></i> Nuevo Funcionario
                        </button>
                        <?php endif; ?>
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
                                    <th>Puesto</th>
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
                        <input type="number" class="form-control" name="ci" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
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
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="rol">
                            <option>Administrador</option>
                            <option>Gerente</option>
                            <option>Administrativo</option>
                            <option>Secretaria</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Puesto</label>
                        <select class="form-select" name="id_puesto">
                            <?php foreach ($puestos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['descripcion']) ?></option>
                            <?php endforeach; ?>
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
                        <input type="number" class="form-control" id="edit_ci" name="ci" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
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
                        <label class="form-label">Rol</label>
                        <select class="form-select" id="edit_rol" name="rol">
                            <option>Administrador</option>
                            <option>Gerente</option>
                            <option>Administrativo</option>
                            <option>Secretaria</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Puesto</label>
                        <select class="form-select" id="edit_id_puesto" name="id_puesto">
                            <?php foreach ($puestos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['descripcion']) ?></option>
                            <?php endforeach; ?>
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
        ajax: 'show.php',
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
            { data: 'puesto' },
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
            $('#edit_rol').val(data.rol);
            $('#edit_id_puesto').val(data.id_puesto);
            $('#editFuncionarioModal').modal('show');
        }
    });
}
</script>

</body>
</html>
