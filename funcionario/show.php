<?php
session_start();
require '../db.php';

try {
    $esAdmin = isset($_SESSION['usuario_cargo']) && strtolower($_SESSION['usuario_cargo']) === 'administrador';

    $sql = "SELECT f.id, f.ci, f.nombre, f.paterno, f.materno, f.rol, p.descripcion AS puesto, f.estado
            FROM funcionario f
            JOIN puesto p ON f.id_puesto = p.id
            WHERE f.rol != 'Administrador'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array();
    $n = 1; // Inicializa la variable $n en 1
    foreach ($funcionarios as $funcionario) {
        $acciones = '';
        $btn_ver_pass = '';
        if ($esAdmin) {
            $btn_ver_pass = '
                <button type="button" class="btn btn-info btn-sm me-1" title="Ver contraseña actual" onclick="verContrasenia('.$funcionario['id'].')">
                    <i class="bi bi-eye"></i>
                </button>';
        }

        if ($funcionario['estado'] == 'Activo') {
            $acciones = $btn_ver_pass . '
                <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$funcionario['id'].'">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editFuncionarioModal" onclick="editarFuncionario('.$funcionario['id'].')"><i class="bi bi-pencil"></i></button>
                </form>
                <form action="destroy.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$funcionario['id'].'">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>';
        } elseif ($funcionario['estado'] == 'Inactivo') {
            $acciones = $btn_ver_pass . '
                <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$funcionario['id'].'">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editFuncionarioModal" onclick="editarFuncionario('.$funcionario['id'].')"><i class="bi bi-pencil"></i></button>
                </form>
                <form action="restore.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$funcionario['id'].'">
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-recycle"></i></button>
                </form>';
        }
        $data[] = array(
            'numero' => $n,
            'ci' => $funcionario['ci'],
            'nombre' => $funcionario['nombre'],
            'paterno' => $funcionario['paterno'],
            'materno' => $funcionario['materno'],
            'puesto' => $funcionario['puesto'],
            'estado' => $funcionario['estado'],
            'acciones' => $acciones
        );
        $n++;
    }
    echo json_encode(array("data" => $data));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>