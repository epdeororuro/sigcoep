
<?php
session_start();
require '../db.php';

try {
    $sql = "SELECT id, ci, nombre, paterno, materno, cargo, area, estado FROM funcionario WHERE cargo!='Administrador'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array();
    $n = 1; // Inicializa la variable $n en 1
    foreach ($funcionarios as $funcionario) {
        $acciones = '';
        if ($funcionario['estado'] == 'Activo') {
            $acciones = '
                 <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$funcionario['id'].'">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editFuncionarioModal" onclick="editarFuncionario('.$funcionario['id'].')"><i class="bi bi-pencil"></i></button>
                </form>
                <form action="destroy.php" method="post" style="display: inline;">
                    <input type="hidden" name="id" value="'.$funcionario['id'].'">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>';
        } elseif ($funcionario['estado'] == 'Inactivo') {
            $acciones = '
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
            'cargo' => $funcionario['cargo'],
            'area' => $funcionario['area'],
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