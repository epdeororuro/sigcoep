<?php
require 'db.php';

$nombre = 'Reynaldo Jesus Flores Jaillita';
$usuario = 'admin';
$password = password_hash('123456', PASSWORD_DEFAULT);
$cargo = 'Administrador';
$area = 'Sistemas';

$stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, usuario, password, cargo, area) VALUES (:nombre, :usuario, :password, :cargo, :area)");
$stmt->execute([
    ':nombre' => $nombre,
    ':usuario' => $usuario,
    ':password' => $password,
    ':cargo' => $cargo,
    ':area' => $area
]);

echo "Usuario creado con éxito.";
