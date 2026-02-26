<?php
require 'db.php';

$nombre = 'Superadmin';
$ci = '123456789';
$paterno = ' ';
$materno = ' ';
$usuario = 'admin';
$password = password_hash('admin', PASSWORD_DEFAULT);
$rol = 'Administrador';
$id_puesto = 1;
$estado = 'Activo';

$stmt = $pdo->prepare("INSERT INTO funcionario (ci, nombre, paterno, materno, usuario, password, rol, id_puesto, estado)
VALUES (:ci, :nombre, :paterno, :materno, :usuario, :password, :rol, :id_puesto, :estado)");
$stmt->execute([
    ':ci' => $ci,
    ':nombre' => $nombre,
    ':paterno' => $paterno,
    ':materno' => $materno,
    ':usuario' => $usuario,
    ':password' => $password,
    ':rol' => $rol,
    ':id_puesto' => $id_puesto,
    ':estado' => $estado
]);



