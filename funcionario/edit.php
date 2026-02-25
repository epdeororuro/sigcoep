
<?php
session_start();
require '../db.php';
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = "SELECT * FROM funcionario WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($funcionario);
    exit;
}
?>
