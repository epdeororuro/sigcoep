<?php
// db.php - Conexión a la base de datos PDO

// Configuración de la base de datos
$DB_HOST = 'localhost';       // Cambia si tu MySQL está en otro host
$DB_NAME = 'sigcoep';
$DB_USER = 'root';            // Cambia por tu usuario MySQL
$DB_PASS = '';                // Cambia por tu contraseña MySQL

try {
    // DSN (Data Source Name)
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

    // Opciones recomendadas para PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Mostrar errores como excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve resultados como array asociativo
        PDO::ATTR_EMULATE_PREPARES => false, // Desactivar emulación de prepared statements
    ];

    // Crear la conexión PDO
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);

} catch (PDOException $e) {
    // Si falla la conexión, mostramos un mensaje
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
