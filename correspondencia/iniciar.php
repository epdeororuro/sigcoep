<?php
session_start();
require '../db.php';

if (!isset($_POST['id'])) {
    $_SESSION['mensaje'] = 'ID de correspondencia no proporcionado';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

$id_correspondencia = intval($_POST['id']);

try {
    // Obtener fojas de la correspondencia
    $stmt = $pdo->prepare("SELECT fojas FROM correspondencia WHERE id = :id AND eliminado_en IS NULL");
    $stmt->execute([':id' => $id_correspondencia]);
    $corr = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$corr) {
        $_SESSION['mensaje'] = 'Correspondencia no encontrada';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }
    $fojas = $corr['fojas'];

    // Buscar funcionario activo con id_puesto = 2 (Gerencia General)
    $stmt = $pdo->prepare("SELECT id FROM funcionario WHERE id_puesto = 2 AND estado = 'Activo' LIMIT 1");
    $stmt->execute();
    $func = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$func) {
        $_SESSION['mensaje'] = 'No existe un funcionario activo asignado a Gerencia General (puesto id=1)';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: index.php');
        exit;
    }

    $id_funcionario = $func['id'];

    // Insertar derivación inicial
    $sql = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter)
            VALUES (:id_correspondencia, :id_funcionario, NOW(), :instruccion, :fojas, :caracter)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_correspondencia' => $id_correspondencia,
        ':id_funcionario' => $id_funcionario,
        ':instruccion' => 'Para su atención',
        ':fojas' => $fojas,
        ':caracter' => 'Para conocimiento'
    ]);

    // Actualizar estado de correspondencia a Iniciado
    $stmt = $pdo->prepare("UPDATE correspondencia SET estado = 'Iniciado', actualizado_en = NOW() WHERE id = :id");
    $stmt->execute([':id' => $id_correspondencia]);

    $_SESSION['mensaje'] = 'Correspondencia iniciada y derivada a Gerencia General';
    $_SESSION['mensaje_tipo'] = 'success';
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['mensaje'] = 'Error iniciando correspondencia: Por favor intente nuevamente';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: index.php');
    exit;
}
?>