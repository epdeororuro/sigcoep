<?php
session_start();
// Protección de sesión
if(!isset($_SESSION['usuario_id'])){
    header('Location: ../index.php');
    exit;
}

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
    <title>Mi Perfil</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 1rem;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-gear"></i> Modificar mi Usuario</h4>
                </div>
                <div class="card-body p-4">
                    <form action="update.php" method="post">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nuevo Nombre de Usuario</label>
                            <input type="text" class="form-control" name="nuevo_usuario" placeholder="Ej: admin123" required>
                            <small class="text-muted">Este será el usuario con el que ingresarás al sistema. Tu contraseña por defecto seguirá siendo tu C.I.</small>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
