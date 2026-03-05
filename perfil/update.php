<?php
session_start();
require '../db.php';

// Protección de sesión
if(!isset($_SESSION['usuario_id'])){
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];

    if (empty($nuevo_usuario)) {
        $_SESSION['mensaje'] = 'Debe ingresar un nombre de usuario.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        // Verificar si el nombre de usuario ya existe en otro registro
        $stmt_check = $pdo->prepare("SELECT id FROM funcionario WHERE usuario = :usuario AND id != :id");
        $stmt_check->execute([':usuario' => $nuevo_usuario, ':id' => $usuario_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $_SESSION['mensaje'] = 'El nombre de usuario ya está en uso por otro funcionario.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: index.php');
            exit;
        }

        $actualizado_en = date('Y-m-d H:i:s');

        // Actualizar datos
        $sql = "UPDATE funcionario SET usuario = :usuario, actualizado_en = :actualizado_en WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario' => $nuevo_usuario,
            ':actualizado_en' => $actualizado_en,
            ':id' => $usuario_id
        ]);

        $_SESSION['mensaje'] = 'Nombre de usuario actualizado correctamente. Los cambios se aplicarán en su próximo inicio de sesión.';
        $_SESSION['mensaje_tipo'] = 'success';
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al actualizar: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
