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
        header('Location: user.php');
        exit;
    }

    try {
        // Verificar si el nombre de usuario ya existe en otro registro
        $stmt_check = $pdo->prepare("SELECT id FROM funcionario WHERE usuario = :usuario AND id != :id");
        $stmt_check->execute([':usuario' => $nuevo_usuario, ':id' => $usuario_id]);
        
        if ($stmt_check->rowCount() > 0) {
            // Obtener datos del usuario para crear sugerencias
            $stmt_info = $pdo->prepare("SELECT nombre, paterno, ci FROM funcionario WHERE id = :id");
            $stmt_info->execute([':id' => $usuario_id]);
            $u_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
            
            $paterno_limpio = preg_replace('/[^a-zA-Z0-9]/', '', $u_info['paterno'] ?? '');
            $nombre_letra = substr($u_info['nombre'] ?? 'u', 0, 1);
            $ci_fin = substr($u_info['ci'] ?? '123', -3);

            // Crear combinaciones base
            $bases = [
                $nuevo_usuario . rand(10, 99), // El nombre que intentó + dos números aleatorios
                strtolower($nombre_letra . $paterno_limpio), // Inicial + Paterno
                strtolower($paterno_limpio . $ci_fin) // Paterno + últimos 3 del CI
            ];
            
            $sugs = [];
            $stmt_sug = $pdo->prepare("SELECT id FROM funcionario WHERE usuario = :user");
            foreach ($bases as $base) {
                $test = $base;
                $c = 1;
                // Verificar que la sugerencia generada tampoco esté ocupada
                while (true) {
                    $stmt_sug->execute([':user' => $test]);
                    if ($stmt_sug->rowCount() == 0) {
                        $sugs[] = $test;
                        break;
                    }
                    $test = $base . $c;
                    $c++;
                }
            }
            $sugerencias_str = implode(', ', array_unique($sugs));

            $_SESSION['mensaje'] = 'El nombre de usuario ya está en uso. Sugerencias: <strong>' . htmlspecialchars($sugerencias_str) . '</strong>';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: user.php');
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
        header('Location: user.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['mensaje'] = 'Error al actualizar: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: user.php');
        exit;
    }
} else {
    header('Location: user.php');
    exit;
}
?>
