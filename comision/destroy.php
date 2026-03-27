<?php
session_start();
require '../db.php';

// Validar acceso (Solo Administrador)
if (!isset($_SESSION['usuario_cargo']) || strtolower(trim($_SESSION['usuario_cargo'])) !== 'administrador') {
    $_SESSION['mensaje'] = 'Acceso denegado.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if ($id !== false) {
        try {
            // 1. Obtener el nombre de la comisión para buscar en derivaciones
            $stmtComision = $pdo->prepare("SELECT nombre FROM comision WHERE id = ?");
            $stmtComision->execute([$id]);
            $comision = $stmtComision->fetch(PDO::FETCH_ASSOC);

            if ($comision) {
                $searchString = "[Comisión: " . $comision['nombre'] . "]%";

                // 2. Verificar si la comisión tiene correspondencia derivada (historial o actual)
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM derivacion WHERE instruccion_adicional LIKE ?");
                $stmtCheck->execute([$searchString]);
                $tieneDerivaciones = $stmtCheck->fetchColumn() > 0;

                if ($tieneDerivaciones) {
                    $_SESSION['mensaje'] = 'No se puede eliminar: La comisión tiene correspondencia en su historial o en turno.';
                    $_SESSION['mensaje_tipo'] = 'danger';
                } else {
                    // 3. Eliminación lógica (Soft delete) de la comisión
                    $stmt = $pdo->prepare("UPDATE comision SET estado = 'Inactivo', eliminado_en = CURRENT_TIMESTAMP() WHERE id = ?");
                    $stmt->execute([$id]);

                    $_SESSION['mensaje'] = 'Comisión eliminada exitosamente.';
                    $_SESSION['mensaje_tipo'] = 'success';
                }
            } else {
                $_SESSION['mensaje'] = 'Comisión no encontrada.';
                $_SESSION['mensaje_tipo'] = 'danger';
            }
        } catch (PDOException $e) {
            $_SESSION['mensaje'] = 'Error al eliminar la comisión: ' . $e->getMessage();
            $_SESSION['mensaje_tipo'] = 'danger';
        }
    } else {
        $_SESSION['mensaje'] = 'ID de comisión no válido.';
        $_SESSION['mensaje_tipo'] = 'danger';
    }
} else {
    $_SESSION['mensaje'] = 'Solicitud no válida.';
    $_SESSION['mensaje_tipo'] = 'danger';
}

header('Location: index.php');
exit;
?>