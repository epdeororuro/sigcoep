<?php
session_start();
require 'config.php';

// Protección de sesión
if(!isset($_SESSION['usuario_id'])){
    header('Location: index.php');
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - <?= SISTEMA_NOMBRE ?></title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      min-height: 100vh;
      margin: 0;
      display: flex;
    }

    /* Sidebar estilo AdminLTE */
    .sidebar {
      width: 220px;
      background-color: #343a40;
      color: #fff;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding-top: 60px;
    }

    .sidebar h4 {
      text-align: center;
      margin-bottom: 20px;
      padding: 0 10px;
    }

    .sidebar a {
      color: #adb5bd;
      display: block;
      padding: 10px 20px;
      text-decoration: none;
    }

    .sidebar a:hover {
      background-color: #495057;
      color: #fff;
      border-radius: 4px;
    }

    /* Navbar superior */
    .navbar-custom {
      background-color: #343a40;
      color: #fff;
      height: 60px;
      line-height: 60px;
      padding: 0 20px;
      position: fixed;
      top: 0;
      left: 220px;
      right: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* Contenido principal con iframe */
    .content {
      margin-left: 220px;
      margin-top: 60px;
      padding: 20px;
      width: calc(100% - 220px);
      height: calc(100vh - 60px);
    }

    iframe {
      width: 100%;
      height: 100%;
      border: none;
    }
  </style>
</head>
<body>

    <!-- Contenido principal -->
  <div class="content">
    <?php if($mensaje): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <h2>Dashboard</h2>
    <p>Bienvenido al sistema de gestión de correspondencia.</p>

    <!-- Tarjetas tipo AdminLTE -->
    <div class="row mt-4">
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="card text-white bg-primary shadow">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-person-plus"></i> Usuarios</h5>
            <p class="card-text">Registrar nuevos usuarios.</p>
            <a href="registrar_usuarios.php" class="btn btn-light btn-sm">Ir</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-3 mb-3">
        <div class="card text-white bg-success shadow">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-folder-plus"></i> Correspondencia</h5>
            <p class="card-text">Registrar y derivar correspondencia.</p>
            <a href="registrar_correspondencia.php" class="btn btn-light btn-sm">Ir</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
