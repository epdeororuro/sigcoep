<?php
session_start();

if (isset($_SESSION['mensaje'])) {
    echo '
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        ' . $_SESSION['mensaje'] . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
    unset($_SESSION['mensaje']);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Correspondencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
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

        .table thead th {
            text-align: center;
            vertical-align: middle;
        }

        .dataTables_wrapper .row {
            width: 100%;
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
                        <h3 class="mb-0">
                            <i class="bi bi-envelope-paper"></i>
                            Correspondencia
                        </h3>

                        <a href="create.php" class="btn btn-primary">
                            <i class="bi bi-envelope-plus"></i>
                            Nueva Correspondencia
                        </a>
                    </div>

                    <table id="correspondencia" class="table table-striped table-bordered align-middle w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>N°</th>
                                <th>CITE</th>
                                <th>Fecha</th>
                                <th>Remitente</th>
                                <th>Referencia</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables -->
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#correspondencia').DataTable({
        ajax: 'getCorrespondencia.php',
        responsive: true,
        autoWidth: false,
        columns: [
            { data: 'numero' },
            { data: 'cite' },
            { data: 'fecha' },
            { data: 'remitente' },
            { data: 'referencia' },
            { data: 'tipo' },
            { data: 'estado' },
            { data: 'acciones' }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });
});
</script>

</body>
</html>
