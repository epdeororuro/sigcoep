<?php
session_start();
// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modificar Contraseña</title>
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
                    <h4 class="mb-0"><i class="bi bi-key"></i> Modificar mi Contraseña</h4>
                </div>
                <div class="card-body p-4">
                    <form action="update_password.php" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nueva contraseña</label>
                            <input type="password" class="form-control" name="nueva_contrasena" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" name="confirmar_contrasena" required>
                        </div>
                        <small class="text-muted d-block mb-3">
                            La nueva contraseña se guardará cifrada para el inicio de sesión, y también en texto plano en el campo interno <code>contrasenia</code> solo para uso del administrador del sistema.
                        </small>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
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

