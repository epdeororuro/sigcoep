<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $paterno = $_POST['paterno'];
    $materno = $_POST['materno'];
    $cargo = $_POST['cargo'];
    $area = $_POST['area'];
    $actualizado_en = date('Y-m-d H:i:s');

    try {
        $sql = "UPDATE funcionario SET ci = :ci, nombre = :nombre, paterno = :paterno, materno = :materno, cargo = :cargo, area = :area, actualizado_en = :actualizado_en WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ci', $ci);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':paterno', $paterno);
        $stmt->bindParam(':materno', $materno);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':area', $area);
        $stmt->bindParam(':actualizado_en', $actualizado_en);
        $stmt->execute();

        // Mostrar mensaje de alerta y redirigir
        $_SESSION['mensaje'] = 'Funcionario actualizado con éxito';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al actualizar funcionario: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['mensaje'] = 'No se proporcionó el ID del funcionario';
    header('Location: index.php');
    exit;
}
?>