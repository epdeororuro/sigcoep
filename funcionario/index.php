<?php session_start(); if (isset($_SESSION['mensaje'])) { echo ' <div class="alert alert-success alert-dismissible fade show" role="alert"> ' . $_SESSION['mensaje'] . ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div> '; unset($_SESSION['mensaje']); } ?> 
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Funcionarios</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Lista de Funcionarios</h3>
                        <a href="#" class="btn btn-primary mb-3 float-end ms-2" data-bs-toggle="modal" data-bs-target="#createFuncionarioModal"><i class="bi bi-person-plus"></i> Nuevo Funcionario</a>
                        <div class="table-responsive">
                            <table id="funcionarios" class="table table-striped">
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
                                <tbody>
                                    <!-- Aquí se cargarán los datos de los funcionarios -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal nuevo funcionario -->
    <div class="modal fade" id="createFuncionarioModal" tabindex="-1" aria-labelledby="createFuncionarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createFuncionarioModalLabel">Agregar Nuevo Funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createFuncionarioForm" action="store.php" method="post">
                        <!-- Aquí van tus campos de formulario -->
                        <div class="mb-3">
                            <label for="ci" class="form-label">Carnet Identidad</label>
                            <input type="text" class="form-control" id="ci" name="ci">
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre">
                        </div>
                        <div class="mb-3">
                            <label for="paterno" class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="paterno" name="paterno">
                        </div>
                        <div class="mb-3">
                            <label for="materno" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="materno" name="materno">
                        </div>
                        <div class="mb-3">
                            <label for="cargo" class="form-label">Cargo</label>
                            <select class="form-select" id="cargo" name="cargo">
                                <option value="Administrador">Administrador del SI</option>
                                <option value="Administrativo">Administrativo</option>
                                <option value="Operativo">Operativo</option>
                                <option value="Consultor">Consultor</option>
                                <option value="Eventual">Eventual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="area" class="form-label">Área</label>
                            <select class="form-select" id="area" name="area">
                                <option value="Gerencia">Gerencia General</option>
                                <option value="JDAF">JDAF</option>
                                <option value="JDOHTO">JDOHTO</option>
                                <option value="JDOTBO">JDOTBO</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="createFuncionarioForm" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal editar funcionario -->
    <div class="modal fade" id="editFuncionarioModal" tabindex="-1" aria-labelledby="editFuncionarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFuncionarioModalLabel">Editar Funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFuncionarioForm" action="update.php" method="post">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_ci" class="form-label">Carnet Identidad</label>
                            <input type="text" class="form-control" id="edit_ci" name="ci">
                        </div>
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre">
                        </div>
                        <div class="mb-3">
                            <label for="edit_paterno" class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="edit_paterno" name="paterno">
                        </div>
                        <div class="mb-3">
                            <label for="edit_materno" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="edit_materno" name="materno">
                        </div>
                        <div class="mb-3">
                            <label for="edit_cargo" class="form-label">Cargo</label>
                            <select class="form-select" id="edit_cargo" name="cargo">
                                <option value="Administrador">Administrador del SI</option>
                                <option value="Administrativo">Administrativo</option>
                                <option value="Operativo">Operativo</option>
                                <option value="Consultor">Consultor</option>
                                <option value="Eventual">Eventual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_area" class="form-label">Área</label>
                            <select class="form-select" id="edit_area" name="area">
                                <option value="Gerencia">Gerencia General</option>
                                <option value="JDAF">JDAF</option>
                                <option value="JDOHTO">JDOHTO</option>
                                <option value="JDOTBO">JDOTBO</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="editFuncionarioForm" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
$('#funcionarios').DataTable({
ajax: 'getFuncionarios.php',
responsive: true,
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
            }
        });
    }
</script>

</body>
</html>