<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$child_id = isset($_GET['child_id']) ? intval($_GET['child_id']) : 0;

if ($child_id <= 0) {
    echo json_encode(['error' => 'ID de correspondencia hija no válido']);
    exit;
}

try {
    // Buscamos correspondencias en poder del usuario, en estado 'Aceptado',
    // que no sean la misma correspondencia hija y que no estén ya agrupadas.
    $sql = "SELECT id, hojaruta, referencia 
            FROM correspondencia 
            WHERE idfuncionario_enturno = :uid 
              AND estado = 'Aceptado'
              AND id != :child_id
              AND agrupado_en IS NULL
              AND eliminado_en IS NULL
            ORDER BY fecha_registro DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $usuario_id, ':child_id' => $child_id]);
    $madres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($madres);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>