<?php
session_start();
require '../db.php';

// Protección de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $usuario_id = $_SESSION['usuario_id'];
    $justificacion = trim($_POST['justificacion'] ?? '');

    if ($id <= 0) {
        $_SESSION['mensaje'] = 'ID de correspondencia inválido.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    if (empty($justificacion)) {
        $_SESSION['mensaje'] = 'Debe ingresar una justificación para solicitar la ampliación.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Verificar que la correspondencia pertenece al usuario y está en estado 'Aceptado'
        $stmtCheck = $pdo->prepare("SELECT estado FROM correspondencia WHERE id = :id AND idfuncionario_enturno = :uid");
        $stmtCheck->execute([':id' => $id, ':uid' => $usuario_id]);
        $correspondencia = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$correspondencia || $correspondencia['estado'] !== 'Aceptado') {
            throw new Exception("No se puede solicitar ampliación. La correspondencia no está en su poder o no tiene el estado 'Aceptado'.");
        }

        // 2. Insertar el registro de la ampliación en el historial de derivaciones
        //    Guardamos la justificación en "instruccion_adicional" y actualizamos el carácter.
        $instruccion = "[AMPLIACIÓN SOLICITADA] Justificación: " . $justificacion;
        $sqlDerivacion = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, fecha_entrega_derivacion, instruccion_adicional, fojas, caracter) 
                          VALUES (:id_corr, :id_func, NOW(), NOW(), :instruccion, 0, 'Con ampliacion')";
        $stmtDerivacion = $pdo->prepare($sqlDerivacion);
        $stmtDerivacion->execute([
            ':id_corr' => $id, 
            ':id_func' => $usuario_id, 
            ':instruccion' => $instruccion
        ]);

        // 3. (Opcional) La fecha de referencia se actualiza automáticamente por la subconsulta en show.php
        //    al tomar el MAX(fecha_entrega_derivacion), dando los 2 días.

        $pdo->commit();
        $_SESSION['mensaje'] = 'Ampliación de plazo solicitada con éxito. Dispone de 2 días adicionales.';
        $_SESSION['mensaje_tipo'] = 'success';

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensaje'] = 'Error al procesar la solicitud: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }

    header('Location: index.php');
    exit;
}
?>