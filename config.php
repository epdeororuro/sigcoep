<?php
// config.php - Configuraciones generales del sistema SIGCOEP

// Nombre del sistema
define('SISTEMA_NOMBRE', 'SIGCOEP - Sistema de Gestión de Correspondencia EPDEOR');

// URL base de la intranet (si es local o ruta de pruebas)
define('BASE_URL', 'http://localhost/sigcoep/');

// Opciones de seguridad
define('SESSION_TIMEOUT', 3600); // Tiempo en segundos para expiración de sesión (1 hora)

// Configuración para generación de código único
define('CODIGO_PREFIX', 'CORR-'); // Prefijo de correspondencia
define('CODIGO_LONGITUD', 4);    // Número de dígitos del código (ej: 0001)

// Puedes agregar otras configuraciones globales aquí
