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
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar estilo AdminLTE */
        .sidebar {
            width: 260px;
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
            margin-left: 260px;
            margin-top: 60px;
            padding: 20px;
            width: calc(100% - 260px);
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
    <!-- Sidebar -->
    <div class="sidebar">
        <h4><?= SISTEMA_NOMBRE ?></h4>
        <a href="inicio.php" target="iframe_content"><i class="bi bi-house-door"></i> Inicio</a>
        <?php if($_SESSION['usuario_cargo'] == 'Administrador') { ?>
            <a href="<?= BASE_URL ?>funcionario/index.php" target="iframe_content"><i class="bi bi-person"></i> Funcionario</a>
        <?php } ?>
        <a href="<?= BASE_URL ?>correspondencia/index.php" target="iframe_content"><i class="bi bi-folder"></i> Correspondencia</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
    </div>
    <!-- Navbar superior -->
    <div class="navbar-custom d-flex justify-content-between align-items-center px-4">
        <div>
            <span class="fw-bold">Área:</span> <?= htmlspecialchars($_SESSION['usuario_cargo']) ?>
        </div>
        
        <!-- Dropdown Usuario -->
        <div class="dropdown">
            <button class="btn btn-dark dropdown-toggle border-0 shadow-none text-white d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5"></i>
                <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                <li><h6 class="dropdown-header">Mi Cuenta</h6></li>
                <li>
                    <a class="dropdown-item" href="perfil/index.php" target="iframe_content">
                        <i class="bi bi-pencil-square me-2"></i> Modificar mi usuario
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="perfil/password.php" target="iframe_content">
                        <i class="bi bi-key me-2"></i> Modificar contraseña
                    </a>
                </li>
                <?php if(strtolower($_SESSION['usuario_cargo']) === 'administrador'): ?>
                <li>
                    <a class="dropdown-item text-success" href="backup.php">
                        <i class="bi bi-database-down me-2"></i> Respaldar Base de Datos
                    </a>
                </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Contenido principal -->
    <div class="content">
        <iframe name="iframe_content" src="inicio.php"></iframe>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>