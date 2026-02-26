<?php
session_start();
require 'config.php';

// Protección de sesión
if(!isset($_SESSION['usuario_id'])){
    echo "<p>No tienes permisos para ver esta página.</p>";
    exit;
}
?>
<html>
  <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </head>
<body>

<div class="container-fluid">
    <h2 class="mb-3">Dashboard</h2>
    <p>Bienvenido al sistema de gestión de correspondencia, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>.</p>

    <div class="row mt-4">
        <!-- Registrar Usuarios -->
        <?php if (isset($_SESSION['usuario_cargo']) && strtolower($_SESSION['usuario_cargo']) === 'administrador'): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-plus"></i> Funcionarios</h5>
                    <p class="card-text">Registrar nuevos funcionarios.</p>
                        <a href="funcionario/index.php" target="iframe_content" class="btn btn-light btn-sm">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Registrar Correspondencia -->
        <?php if (isset($_SESSION['usuario_cargo']) && in_array(strtolower($_SESSION['usuario_cargo']), ['administrador','secretaria','administrativo'])): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-folder-plus"></i> Correspondencia</h5>
                    <p class="card-text">Registrar y derivar correspondencia.</p>
                        <a href="correspondencia/index.php" target="iframe_content" class="btn btn-light btn-sm">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
