
<?php
session_start();
require '../db.php';
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = "SELECT * FROM correspondencia WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($funcionario);
    exit;
}
?>
