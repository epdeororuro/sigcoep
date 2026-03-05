<?php
session_start();
require '../db.php';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = 'Método no permitido';
    header('Location: ../correspondencia/index.php');
    exit;
}

// Recoger datos
$id_correspondencia = $_POST['id_correspondencia'] ?? null;
$id_funcionario = $_POST['id_funcionario'] ?? null;
$instruccion = trim($_POST['instruccion_adicional'] ?? '');
$fojas = isset($_POST['fojas']) ? intval($_POST['fojas']) : 0;
$caracter = trim($_POST['caracter'] ?? '');

if (empty($id_correspondencia) || empty($id_funcionario)) {
    $_SESSION['mensaje'] = 'Faltan datos obligatorios para derivar.';
    header('Location: ../correspondencia/index.php');
    exit;
}

// Validar que los campos obligatorios del formulario estén presentes
if (empty($instruccion) || empty($caracter)) {
    $_SESSION['mensaje'] = 'Complete todos los campos obligatorios antes de derivar.';
    header('Location: ../correspondencia/index.php');
    exit;
}

try {
    // Insertar derivación en la tabla `derivacion`
    $sql = "INSERT INTO derivacion (id_correspondencia, id_funcionario, fecha_derivacion, instruccion_adicional, fojas, caracter) VALUES (:id_corr, :id_func, NOW(), :instr, :fojas, :caracter)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_corr' => $id_correspondencia,
        ':id_func' => $id_funcionario,
        ':instr' => $instruccion,
        ':fojas' => $fojas,
        ':caracter' => $caracter
    ]);

    // Actualizar estado de la correspondencia
    $sqlUpd = "UPDATE correspondencia SET estado = 'Derivado', idfuncionario_enturno = :id_func, actualizado_en = NOW() WHERE id = :id";
    $stmtUpd = $pdo->prepare($sqlUpd);
    $stmtUpd->execute([
        ':id' => $id_correspondencia,
        ':id_func' => $id_funcionario
    ]);

    $_SESSION['mensaje'] = 'Correspondencia derivada con éxito';
    header('Location: ../correspondencia/index.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['mensaje'] = 'Error al registrar derivación: ' . $e->getMessage();
    header('Location: ../correspondencia/index.php');
    exit;
}
