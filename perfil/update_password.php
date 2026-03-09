<?php
session_start();
require '../db.php';

// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = $_POST['nueva_contrasena'] ?? '';
    $confirmar = $_POST['confirmar_contrasena'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    if (empty($nueva) || empty($confirmar)) {
        $_SESSION['mensaje'] = 'Debe completar ambos campos de contraseña.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: password.php');
        exit;
    }

    if ($nueva !== $confirmar) {
        $_SESSION['mensaje'] = 'Las contraseñas no coinciden.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: password.php');
        exit;
    }

    // Puede añadirse más validación (longitud mínima, etc.)

    try {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $actualizado_en = date('Y-m-d H:i:s');

        $sql = "UPDATE funcionario 
                SET password = :password,
                    contrasenia = :contrasenia,
                    actualizado_en = :actualizado_en
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':password' => $hash,
            ':contrasenia' => $nueva,
            ':actualizado_en' => $actualizado_en,
            ':id' => $usuario_id
        ]);

        $_SESSION['mensaje'] = 'Contraseña actualizada correctamente.';
        $_SESSION['mensaje_tipo'] = 'success';
        header('Location: password.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al actualizar la contraseña: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: password.php');
        exit;
    }
} else {
    header('Location: password.php');
    exit;
}

