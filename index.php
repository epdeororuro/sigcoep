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
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <!-- Theme Mode JS -->
  <script src="assets/js/theme.js"></script>
  <style>
    body {
      background-color: var(--bs-tertiary-bg);
    }
    .card {
      border-radius: 1rem;
    }
  </style>
</head>
<body>
  <!-- Toggle Theme Button -->
  <div class="position-absolute top-0 end-0 p-3">
      <button class="btn btn-outline-secondary border-0" id="btnThemeToggle" title="Alternar Modo Oscuro">
          <i class="bi bi-moon-stars" id="iconTheme"></i>
      </button>
  </div>

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
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                  <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#password">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
            </form>

          </div>
        </div>
        
        <!-- SECCIÓN PROVISIONAL DE CREDENCIALES PARA DEMOSTRACIÓN -->
        <div class="alert alert-info mt-3 shadow-sm border-info" style="font-size: 0.85rem;">
          <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-info-circle"></i> Credenciales de Prueba (Todos)</h6>
          <div style="max-height: 200px; overflow-y: auto;" class="pe-2">
              <ul class="mb-0 ps-3 text-dark">
                <li><strong>Administrador:</strong> admin / 123456789</li>
                <li><strong>Gerente (Elizabeth Martinez):</strong> emartinez / 7343846</li>
                <li><strong>Secretaria (Ventanilla):</strong> vunica / 123456</li>
                <li><strong>Archivista Central:</strong> acentral / 987654</li>
                <li><strong>Admin. (Mirian Rada):</strong> mrada / 5067188</li>
                <li><strong>Admin. (Carmen Rufino):</strong> crufino / 3544712</li>
                <li><strong>Admin. (Maricruz Mamani):</strong> mmamani / 5778923</li>
                <li><strong>Admin. (Belinda Perez):</strong> bperez / 4058090</li>
                <li><strong>Admin. (Erwin Gonzales):</strong> egonzales / 7260666</li>
                <li><strong>Admin. (Carlos Rodriguez):</strong> crodriguez / 5732101</li>
                <li><strong>Admin. (David Ticona):</strong> dticona / 7423343</li>
                <li><strong>Admin. (Jeanneth Chambi):</strong> jchambi / 7270861</li>
                <li><strong>Admin. (Guadalupe Gutierrez):</strong> ggutierrez / 13857686</li>
                <li><strong>Admin. (Marina Alegre):</strong> malegre / 5755448</li>
                <li><strong>Admin. (Maria Colque):</strong> mcolque / 73007898</li>
                <li><strong>Admin. (Reynaldo Flores):</strong> rflores / 7403044</li>
                <li><strong>Admin. (Milton Torrez):</strong> mtorrez / 7292221</li>
                <li><strong>Admin. (Marina Alejandro):</strong> malejandro / 7376273</li>
              </ul>
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
              Busque por número de hoja de ruta o referencia para ver con quién se encuentra actualmente la correspondencia.
            </p>
            <div class="table-responsive">
              <table id="tablaHojasRuta" class="table table-striped table-bordered align-middle text-center w-100">
                <thead class="table-primary text-center">
                  <tr>
                    <th>Hoja <br>de ruta</th>
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
          { data: 'hojaruta', className: 'fw-bold text-primary' },
          { data: 'referencia', width: '45%', className: 'text-start text-wrap text-break' },
          { data: 'en_curso_con', width: '25%', className: 'text-wrap' },
          { data: 'estado', width: '15%' }
        ]
      });
    });

    // Toggle de visibilidad de contraseñas (ojito siempre disponible)
    document.querySelectorAll('.toggle-password-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var targetSelector = btn.getAttribute('data-target');
        var input = document.querySelector(targetSelector);
        if (!input) return;
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
          }
        } else {
          input.type = 'password';
          if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
          }
        }
      });
    });

    // Toggle Modo Oscuro Login
    document.getElementById('btnThemeToggle').addEventListener('click', () => {
        let currentTheme = document.documentElement.getAttribute('data-bs-theme');
        let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        document.getElementById('iconTheme').className = newTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
    });
    window.addEventListener('DOMContentLoaded', () => {
        let theme = localStorage.getItem('theme') || 'light';
        document.getElementById('iconTheme').className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
    });
  </script>
</body>
</html>
