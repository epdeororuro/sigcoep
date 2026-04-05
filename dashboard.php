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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <!-- Manejo de Tema -->
    <script src="assets/js/theme.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-resizer" id="sidebarResizer"></div>
        <div class="text-center mt-2 mb-4">
            <img src="assets/img/logo_1.png" alt="Logo EPDEOR" class="img-fluid mb-2" style="max-width: 110px; height: auto;">
            <h4 class="fw-bold m-0" style="line-height: 1.4;"><?= SISTEMA_NOMBRE ?></h4>
        </div>
        <a href="inicio.php" target="iframe_content"><i class="bi bi-house-door"></i> Inicio</a>
        <?php if(strtolower($_SESSION['usuario_cargo']) == 'administrador' || strtolower($_SESSION['usuario_cargo']) == 'secretaria') { ?>
            <a href="<?= BASE_URL ?>funcionario/index.php" target="iframe_content"><i class="bi bi-person text-primary"></i> Funcionario</a>
            <!-- <a href="<?= BASE_URL ?>comision/index.php" target="iframe_content"><i class="bi bi-people"></i> Comisiones</a> -->
        <?php } ?>
        <a href="<?= BASE_URL ?>correspondencia/index.php?view=activas" target="iframe_content"><i class="bi bi-envelope-paper text-success"></i> Correspondencia</a>
        <?php if(!in_array(strtolower($_SESSION['usuario_cargo']), ['archivista central', 'secretaria'])): ?>
            <a href="<?= BASE_URL ?>grupo/index.php" target="iframe_content"><i class="bi bi-diagram-3 text-info"></i> Grupos de Trabajo</a>
        <?php endif; ?>
        <?php if(!in_array(strtolower($_SESSION['usuario_cargo']), ['archivista central', 'secretaria'])): ?>
            <a href="<?= BASE_URL ?>correspondencia/index.php?view=concluidos" target="iframe_content"><i class="bi bi-check2-circle" style="color: #6f42c1;"></i> Procesos Concluidos</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>correspondencia/index.php?view=archivo" target="iframe_content"><i class="bi bi-archive text-warning"></i> Archivo Central</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right text-danger"></i> Cerrar sesión</a>
    </div>
    <!-- Navbar superior -->
    <div class="navbar-custom d-flex justify-content-between align-items-center px-4">
        <div>
            <span class="fw-bold">Rol:</span> <?= htmlspecialchars($_SESSION['usuario_cargo']) ?>
        </div>
        
        <div class="d-flex align-items-center">
            <!-- Toggle Modo Oscuro -->
            <button class="btn btn-dark border-0 shadow-none text-white me-3" id="btnThemeToggle" title="Alternar Modo Oscuro">
                <i class="bi bi-moon-stars" id="iconTheme"></i>
            </button>

            <!-- Dropdown Usuario -->
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle border-0 shadow-none text-white d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">Mi Cuenta</h6></li>
                    <li>
                        <a class="dropdown-item" href="perfil/user.php" target="iframe_content">
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
    </div>
    <!-- Contenido principal -->
    <div class="content">
        <iframe name="iframe_content" src="inicio.php"></iframe>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Controlar el modo oscuro para el panel principal y el iframe incrustado
        document.getElementById('btnThemeToggle').addEventListener('click', () => {
            let currentTheme = document.documentElement.getAttribute('data-bs-theme');
            let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            document.getElementById('iconTheme').className = newTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
            
            // Propagar el cambio al contenido del iframe al instante
            let iframe = document.querySelector('iframe[name="iframe_content"]');
            if (iframe && iframe.contentWindow && iframe.contentWindow.document) {
                iframe.contentWindow.document.documentElement.setAttribute('data-bs-theme', newTheme);
            }
        });

        // Lógica para redimensionar el sidebar
        const resizer = document.getElementById('sidebarResizer');
        let isResizing = false;

        resizer.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.body.style.cursor = 'col-resize';
            resizer.classList.add('is-resizing');
            e.preventDefault(); // Evitar selección de texto accidental
        });

        document.addEventListener('mousemove', (e) => {
            if (!isResizing) return;
            let newWidth = e.clientX;
            // Limites mínimo y máximo del sidebar
            if (newWidth < 200) newWidth = 200;
            if (newWidth > 600) newWidth = 600;
            
            document.documentElement.style.setProperty('--sidebar-width', newWidth + 'px');
        });

        document.addEventListener('mouseup', () => {
            if (isResizing) {
                isResizing = false;
                document.body.style.cursor = 'default';
                resizer.classList.remove('is-resizing');
                // Guardar el ancho en localStorage para recordar la preferencia
                localStorage.setItem('sidebarWidth', document.documentElement.style.getPropertyValue('--sidebar-width'));
                
                // Forzar recálculo de DataTables en el iframe si existe (para que no se rompan las tablas al achicar)
                let iframe = document.querySelector('iframe[name="iframe_content"]');
                if (iframe && iframe.contentWindow && iframe.contentWindow.jQuery) {
                    iframe.contentWindow.jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }
            }
        });

        // Configurar icono inicial
        window.addEventListener('DOMContentLoaded', () => {
            let theme = localStorage.getItem('theme') || 'light';
            document.getElementById('iconTheme').className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';

            // Restaurar ancho del sidebar
            let savedWidth = localStorage.getItem('sidebarWidth');
            if (savedWidth) {
                document.documentElement.style.setProperty('--sidebar-width', savedWidth);
            }
        });
    </script>
</body>
</html>