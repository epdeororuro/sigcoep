<?php
session_start();
if(!isset($_SESSION['usuario_id'])){
    header('Location: login.php');
    exit;
}

$cargos = array(
    'Gerente' => 'Gerente',
    'Subgerente' => 'Subgerente',
    'Jefe de Departamento' => 'Jefe de Departamento',
    'Empleado' => 'Empleado'
);

$areas = array(
    'Administración' => 'Administración',
    'Finanzas' => 'Finanzas',
    'Recursos Humanos' => 'Recursos Humanos',
    'Operaciones' => 'Operaciones'
);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Funcionario</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
        <div class="row justify-content-center mt-9">
            <div class="col-md-12 col-lg-12">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Registro de Funcionario</h3>
                        <form method="POST" action="store.php">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa el nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="paterno" class="form-label">Apellido Paterno</label>
                                <input type="text" class="form-control" id="paterno" name="paterno" placeholder="Ingresa el apellido paterno" required>
                            </div>
                            <div class="mb-3">
                                <label for="materno" class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" id="materno" name="materno" placeholder="Ingresa el apellido materno" required>
                            </div>
                            <div class="mb-3">
                                <label for="cargo" class="form-label">Cargo</label>
                                <select class="form-select" id="cargo" name="cargo" required>
                                    <option value="">Seleccione un cargo</option>
                                    <?php foreach ($cargos as $key => $value) { ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="area" class="form-label">Área</label>
                                <select class="form-select" id="area" name="area" required>
                                    <option value="">Seleccione un área</option>
                                    <?php foreach ($areas as $key => $value) { ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Registrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if($_SESSION['usuario_cargo'] == 'admin') { ?>
            <div class="row justify-content-center mt-5">
                <div class="col-md-8 col-lg-7">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="card-title text-center mb-4">Funcionarios Registrados</h3>
                            <table id="funcionarios" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Apellido Paterno</th>
                                        <th>Apellido Materno</th>
                                        <th>Cargo</th>
                                        <th>Área</th>
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
        <?php } ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#funcionarios').DataTable({
                ajax: 'getFuncionarios.php',
                columns: [
                    { data: 'nombre' },
                    { data: 'paterno' },
                    { data: 'materno' },
                    { data: 'cargo' },
                    { data: 'area' }
                ]
            });
        });
    </script>
</body>
</html>