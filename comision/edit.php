<?php
session_start();
require '../db.php';

// Validar acceso (Solo Administrador o Secretaria)
if (!isset($_SESSION['usuario_cargo']) || (strtolower(trim($_SESSION['usuario_cargo'])) != 'administrador' && strtolower(trim($_SESSION['usuario_cargo'])) != 'secretaria')) {
    echo json_encode(['error' => 'Acceso denegado.']);
    exit;
}

if (isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    try {
        $stmt = $pdo->prepare("SELECT id, nombre, descripcion, responsable_id FROM comision WHERE id = ?");
        $stmt->execute([$id]);
        $comision = $stmt->fetch(PDO::FETCH_ASSOC);

         // Obtener los miembros de la comisión
         $stmtMiembros = $pdo->prepare("SELECT funcionario_id FROM comision_miembros WHERE comision_id = ?");
         $stmtMiembros->execute([$id]);
         $miembros = $stmtMiembros->fetchAll(PDO::FETCH_COLUMN);

         $comision['miembros'] = $miembros;

        echo json_encode($comision);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener la comisión: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID no proporcionado.']);
}
exit;
