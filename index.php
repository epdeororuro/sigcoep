<?php
session_start();
require 'config.php';  // <-- Esto carga la constante SISTEMA_NOMBRE
require 'db.php';      // <-- Si necesitas conexión a BD

// Si ya está logueado, redirige al dashboard
if(isset($_SESSION['usuario_id'])){
    header('Location: dashboard.php'); // Cambia según tu dashboard real
    exit;
}
// Captura error enviado desde login.php
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SIGCOEP</title>
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
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
    <div class="row justify-content-center mt-5">
      <div class="col-md-5 col-lg-4">
        <div class="card shadow">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <img src="assets/img/logo_1.png" alt="<?= SISTEMA_NOMBRE ?>" class="img-fluid mb-3" style="max-height: 120px;">
              <h4 class="card-title"><?= SISTEMA_NOMBRE ?></h4>
            </div>

            <?php if($error == 1): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Usuario o contraseña incorrectos.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
              <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
            </form>

          </div>
        </div>
      </div>
    </div>

    <!-- Sección de consulta pública de hojas de ruta -->
    <div class="row justify-content-center mt-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Consulta de hojas de ruta</h5>
            <p class="text-muted mb-3">
              Busque por número de hoja de ruta, referencia o funcionario para ver con quién se encuentra actualmente la correspondencia.
            </p>
            <div class="table-responsive">
              <table id="tablaHojasRuta" class="table table-striped table-bordered align-middle w-100">
                <thead class="table-primary">
                  <tr>
                    <th>Hoja de ruta</th>
                    <th>Referencia</th>
                    <th>En curso con</th>
                    <th>Estado</th>
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

  <!-- jQuery y DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#tablaHojasRuta').DataTable({
        ajax: {
          url: 'correspondencia/show_public.php',
          type: 'POST'
        },
        autoWidth: false,
        responsive: true,
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        columns: [
          { data: 'hojaruta' },
          { data: 'referencia' },
          { data: 'en_curso_con' },
          { data: 'estado' },
        ]
      });
    });
  </script>
</body>
</html>
