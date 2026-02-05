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
    <div class="navbar-custom">
        <div>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> (<?= htmlspecialchars($_SESSION['usuario_cargo']) ?>)</div>
        <div>Área: <?= htmlspecialchars($_SESSION['usuario_area']) ?></div>
    </div>
    <!-- Contenido principal -->
    <div class="content">
        <iframe name="iframe_content" src="inicio.php"></iframe>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>