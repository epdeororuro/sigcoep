<?php
session_start();
require '../db.php';

if (!isset($_SESSION['usuario_cargo']) || strtolower($_SESSION['usuario_cargo']) !== 'administrador') {
    $_SESSION['mensaje'] = 'Acceso denegado. No tiene permisos para realizar esta acción.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $paterno = trim($_POST['paterno'] ?? '');
    $materno = trim($_POST['materno'] ?? '');
    $rol = $_POST['rol'] ?? '';
    $id_puesto = intval($_POST['id_puesto'] ?? 0);

    // Validaciones básicas
    if (empty($ci) || empty($nombre) || empty($paterno) || empty($rol) || $id_puesto <= 0) {
        $_SESSION['mensaje'] = 'Todos los campos son obligatorios.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    // Generar nombre de usuario (ej: jdoe)
    $primera_letra_nombre = strtolower(substr($nombre, 0, 1));
    $apellido_limpio = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $paterno));
    $usuario = $primera_letra_nombre . $apellido_limpio;

    try {
        // Verificar si el usuario ya existe para evitar duplicados
        $stmtCheckUser = $pdo->prepare("SELECT id FROM funcionario WHERE usuario = :usuario");
        $stmtCheckUser->execute([':usuario' => $usuario]);
        $base_usuario = $usuario;
        $contador = 1;
        while ($stmtCheckUser->fetch()) {
            $usuario = $base_usuario . $contador;
            $contador++;
            $stmtCheckUser->execute([':usuario' => $usuario]);
        }
        
        // Verificar si el CI ya existe
        $stmtCheckCI = $pdo->prepare("SELECT id FROM funcionario WHERE ci = :ci");
        $stmtCheckCI->execute([':ci' => $ci]);
        if ($stmtCheckCI->fetch()) {
            $_SESSION['mensaje'] = 'El Carnet de Identidad ingresado ya está registrado.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: index.php');
            exit;
        }

        // Guardamos la contraseña encriptada (password) y en texto plano (contrasenia)
        $contrasenia_plain = $ci;
        $password_hashed = password_hash($contrasenia_plain, PASSWORD_DEFAULT);

        $sql = "INSERT INTO funcionario (ci, nombre, paterno, materno, rol, id_puesto, usuario, password, contrasenia, estado) 
                VALUES (:ci, :nombre, :paterno, :materno, :rol, :id_puesto, :usuario, :password, :contrasenia, 'Activo')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ci' => $ci,
            ':nombre' => $nombre,
            ':paterno' => $paterno,
            ':materno' => $materno,
            ':rol' => $rol,
            ':id_puesto' => $id_puesto,
            ':usuario' => $usuario,
            ':password' => $password_hashed,
            ':contrasenia' => $contrasenia_plain // Guardando la contraseña en texto plano
        ]);

        $_SESSION['mensaje'] = 'Funcionario registrado con éxito. Su usuario es "' . htmlspecialchars($usuario) . '" y su contraseña es su C.I.';
        $_SESSION['mensaje_tipo'] = 'success';

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al registrar el funcionario: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }

    header('Location: index.php');
    exit;
}
?>