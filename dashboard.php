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
            <!-- Notificaciones (Campanita) -->
            <div class="dropdown me-3">
                <button class="btn btn-dark border-0 shadow-none text-white position-relative d-flex align-items-center" style="height: 42px;" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="noti-badge" style="display: none; font-size: 0.65rem;">
                        0
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notificationDropdown" style="width: 320px;" id="noti-list">
                    <li><h6 class="dropdown-header">Notificaciones</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="text-center p-3 text-muted small" id="noti-empty">No tienes notificaciones nuevas</li>
                </ul>
            </div>

            <!-- Toggle Modo Oscuro -->
            <button class="btn btn-dark border-0 shadow-none text-white me-3 d-flex align-items-center" style="height: 42px;" id="btnThemeToggle" title="Alternar Modo Oscuro">
                <i class="bi bi-moon-stars fs-5" id="iconTheme"></i>
            </button>

            <!-- Dropdown Usuario -->
            <div class="dropdown">
                <button class="btn btn-dark dropdown-toggle border-0 shadow-none text-white d-flex align-items-center gap-2" style="height: 42px;" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
            document.getElementById('iconTheme').className = newTheme === 'dark' ? 'bi bi-sun-fill fs-5' : 'bi bi-moon-stars fs-5';
            
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

        // Lógica de Notificaciones en Tiempo Real (AJAX)
        function fetchNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    let total = data.total;
                    let badge = document.getElementById('noti-badge');
                    let list = document.getElementById('noti-list');
                    
                    if(total > 0) {
                        badge.innerText = total > 99 ? '99+' : total;
                        badge.style.display = 'block';
                        
                        let html = '<li><h6 class="dropdown-header fw-bold text-dark">Notificaciones ('+total+')</h6></li><li><hr class="dropdown-divider"></li>';
                        
                        if(data.correspondencia > 0) {
                            html += '<li><a class="dropdown-item py-2" href="correspondencia/index.php?view=activas" target="iframe_content"><div class="d-flex align-items-center"><i class="bi bi-envelope-paper text-success fs-4 me-3"></i><div><p class="mb-0 fw-bold text-wrap">Correspondencia Nueva</p><small class="text-muted text-wrap">Tienes '+data.correspondencia+' trámite(s) por aceptar</small></div></div></a></li>';
                        }
                        
                        if(data.grupos > 0) {
                            html += '<li><a class="dropdown-item py-2" href="grupo/index.php" target="iframe_content"><div class="d-flex align-items-center"><i class="bi bi-diagram-3 text-info fs-4 me-3"></i><div><p class="mb-0 fw-bold text-wrap">Tareas de Grupo</p><small class="text-muted text-wrap">Tienes '+data.grupos+' tarea(s) pendiente(s)</small></div></div></a></li>';
                        }
                        
                        list.innerHTML = html;
                    } else {
                        badge.style.display = 'none';
                        list.innerHTML = '<li><h6 class="dropdown-header">Notificaciones</h6></li><li><hr class="dropdown-divider"></li><li class="text-center p-3 text-muted small">No tienes notificaciones nuevas</li>';
                    }
                })
                .catch(error => console.error('Error obteniendo notificaciones:', error));
        }

        // Configurar icono inicial
        window.addEventListener('DOMContentLoaded', () => {
            let theme = localStorage.getItem('theme') || 'light';
            document.getElementById('iconTheme').className = theme === 'dark' ? 'bi bi-sun-fill fs-5' : 'bi bi-moon-stars fs-5';

            // Restaurar ancho del sidebar
            let savedWidth = localStorage.getItem('sidebarWidth');
            if (savedWidth) {
                document.documentElement.style.setProperty('--sidebar-width', savedWidth);
            }

            // Iniciar notificaciones y configurar polling cada 60 segundos
            fetchNotifications();
            setInterval(fetchNotifications, 60000);
        });
    </script>
</body>
</html>