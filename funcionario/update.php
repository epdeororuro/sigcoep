<?php
session_start();
require '../db.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $paterno = $_POST['paterno'];
    $materno = $_POST['materno'];
    $rol = $_POST['rol'];
    $id_puesto = $_POST['id_puesto'];
    $actualizado_en = date('Y-m-d H:i:s');
    // Recalcular y encriptar la contraseña en función de los datos editados
    $password_plain = $ci;
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    try {
        $sql = "UPDATE funcionario SET ci = :ci, nombre = :nombre, paterno = :paterno, materno = :materno, rol = :rol, id_puesto = :id_puesto, password = :password, actualizado_en = :actualizado_en WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ci', $ci);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':paterno', $paterno);
        $stmt->bindParam(':materno', $materno);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':id_puesto', $id_puesto);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':actualizado_en', $actualizado_en);
        $stmt->execute();

        // Mostrar mensaje de alerta y redirigir
        $_SESSION['mensaje'] = 'Funcionario actualizado con éxito';
        $_SESSION['mensaje_tipo'] = 'success';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        // Verificar si es un error de clave duplicada (CI ya existe)
        if ($e->getCode() == 23000) {
            $_SESSION['mensaje'] = 'El carnet de identidad ya está registrado en el sistema';
        } else {
            $_SESSION['mensaje'] = 'Error al actualizar funcionario: Por favor intente nuevamente';
        }
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }
} else {
    $_SESSION['mensaje'] = 'No se proporcionó el ID del funcionario';
    header('Location: index.php');
    exit;
}
?>