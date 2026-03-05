<?php
session_start();
require 'db.php';

// Verificación de seguridad estricta: Solo Admnistradores
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['usuario_cargo']) !== 'administrador') {
    header('HTTP/1.0 403 Forbidden');
    echo "Acceso denegado. No tienes permisos para realizar esta acción.";
    exit;
}

try {
    // Nombre del archivo de respaldo
    $filename = "sigcoep_backup_" . date("Y-m-d_H-i-s") . ".sql";
    
    // Obtener todas las tablas de la base de datos
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $sqlScript = "-- Respaldo de Base de Datos SIGCOEP\n";
    $sqlScript .= "-- Fecha de generacion: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        $sqlScript .= "-- --------------------------------------------------------\n";
        $sqlScript .= "-- Estructura de la tabla `$table`\n";
        $sqlScript .= "-- --------------------------------------------------------\n\n";
        
        $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
        
        $stmtCreate = $pdo->query("SHOW CREATE TABLE `$table`");
        $rowCreate = $stmtCreate->fetch(PDO::FETCH_NUM);
        $sqlScript .= $rowCreate[1] . ";\n\n";
        
        $stmtData = $pdo->query("SELECT * FROM `$table`");
        $rowCount = $stmtData->rowCount();
        
        if ($rowCount > 0) {
            $sqlScript .= "-- Volcado de datos para la tabla `$table`\n";
            $sqlScript .= "INSERT INTO `$table` VALUES\n";
            
            $columnCount = $stmtData->columnCount();
            $rowIndex = 0;
            while ($row = $stmtData->fetch(PDO::FETCH_NUM)) {
                $sqlScript .= "(";
                for ($j = 0; $j < $columnCount; $j++) {
                    $row[$j] = $row[$j] === null ? "NULL" : $pdo->quote($row[$j]);
                    $sqlScript .= $row[$j];
                    if ($j < ($columnCount - 1)) {
                        $sqlScript .= ", ";
                    }
                }
                $sqlScript .= ")";
                
                $rowIndex++;
                if ($rowIndex < $rowCount) {
                    $sqlScript .= ",\n";
                } else {
                    $sqlScript .= ";\n";
                }
            }
            $sqlScript .= "\n";
        }
    }
    
    // Forzar la descarga del archivo generado
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $sqlScript;
    exit;

} catch (PDOException $e) {
    echo "Error al generar el respaldo: " . $e->getMessage();
    exit;
}
?>
