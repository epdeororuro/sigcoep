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
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h3 class="card-title text-center mb-4"><?= SISTEMA_NOMBRE ?></h3>

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
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
