<?php
session_start();
require 'config.php';
require 'db.php';

// Protección de sesión
if(!isset($_SESSION['usuario_id'])){
    echo "<p>No tienes permisos para ver esta página.</p>";
    exit;
}

// Verificar si el usuario tiene correspondencia aceptada hace más de 2 días
$usuario_id = $_SESSION['usuario_id'];
$stmt_retrasos = $pdo->prepare("
    SELECT c.hojaruta, c.referencia, 
           COALESCE(
               (SELECT MAX(fecha_entrega_derivacion) FROM derivacion WHERE id_correspondencia = c.id AND id_funcionario = c.idfuncionario_enturno), 
               c.actualizado_en, 
               c.fecha_registro
           ) as fecha_referencia
    FROM correspondencia c 
    WHERE c.estado = 'Aceptado' AND c.idfuncionario_enturno = :uid AND c.eliminado_en IS NULL
    HAVING DATEDIFF(CURDATE(), DATE(fecha_referencia)) >= 2
");
$stmt_retrasos->execute([':uid' => $usuario_id]);
$retrasos = $stmt_retrasos->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
  <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Efecto sutil para que la tarjeta parezca un botón clickeable */
        .hover-card { transition: transform 0.2s ease; }
        .hover-card:hover { transform: translateY(-5px); }
    </style>
  </head>
<body>

<div class="container-fluid">
    <h2 class="mb-3">Dashboard</h2>
    <p>Bienvenido al sistema de gestión de correspondencia, <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>.</p>

    <div class="row mt-4">
        <!-- Registrar Usuarios -->
        <?php if (isset($_SESSION['usuario_cargo']) && in_array(strtolower($_SESSION['usuario_cargo']), ['administrador', 'secretaria'])): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-white bg-primary shadow hover-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person"></i> Funcionarios</h5>
                    <p class="card-text">Registrar y gestionar.</p>
                        <a href="funcionario/index.php" class="btn btn-light btn-sm stretched-link">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Correspondencia (Bandejas Activas) -->
        <?php if (isset($_SESSION['usuario_cargo'])): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-white bg-success shadow hover-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-envelope-paper"></i> Correspondencia</h5>
                    <p class="card-text">Bandejas activas y en curso.</p>
                    <a href="correspondencia/index.php?view=activas" class="btn btn-light btn-sm stretched-link">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grupos de Trabajo -->
        <?php if (isset($_SESSION['usuario_cargo']) && !in_array(strtolower($_SESSION['usuario_cargo']), ['archivista central', 'secretaria'])): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-dark bg-info shadow hover-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-diagram-3"></i> Grupos de Trabajo</h5>
                    <p class="card-text">Tareas y comisiones simultáneas.</p>
                    <a href="grupo/index.php" class="btn btn-light btn-sm stretched-link">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Concluidos y Revisión -->
        <?php if (isset($_SESSION['usuario_cargo']) && !in_array(strtolower($_SESSION['usuario_cargo']), ['archivista central', 'secretaria'])): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-white shadow hover-card" style="background-color: #6f42c1;">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-check2-circle"></i> Procesos Concluidos</h5>
                    <p class="card-text">Trámites finalizados o en revisión.</p>
                    <a href="correspondencia/index.php?view=concluidos" class="btn btn-light btn-sm stretched-link">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Archivo Central -->
        <?php if (isset($_SESSION['usuario_cargo'])): ?>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card text-dark bg-warning shadow hover-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-archive"></i> Archivo Central</h5>
                    <p class="card-text">Resguardo físico definitivo.</p>
                    <a href="correspondencia/index.php?view=archivo" class="btn btn-light btn-sm stretched-link">Ir</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php if (count($retrasos) > 0): ?>
<!-- Modal de Alerta de Retrasos -->
<div class="modal fade" id="modalRetrasos" tabindex="-1" aria-labelledby="modalRetrasosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalRetrasosLabel"><i class="bi bi-exclamation-triangle-fill"></i> Alerta de Correspondencia Retrasada</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Tiene <strong><?= count($retrasos) ?></strong> correspondencia(s) en su poder desde hace más de 2 días que aún no ha(n) sido derivada(s):</p>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover mt-3 text-center align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Hoja de Ruta</th>
                        <th>Referencia</th>
                        <th>Aceptado el</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($retrasos as $r): ?>
                    <tr>
                        <td><span class="badge bg-danger"><?= htmlspecialchars($r['hojaruta']) ?></span></td>
                        <td><?= htmlspecialchars($r['referencia']) ?></td>
                        <td><?= date('d-m-Y H:i:s', strtotime($r['fecha_referencia'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <a href="correspondencia/index.php" class="btn btn-danger">Ir a Bandeja de Pendientes</a>
      </div>
    </div>
  </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalRetrasos = new bootstrap.Modal(document.getElementById('modalRetrasos'));
        modalRetrasos.show();
    });
</script>
<?php endif; ?>

</body>
</html>
