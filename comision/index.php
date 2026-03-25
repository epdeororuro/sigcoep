<?php
session_start();
require '../db.php';

// Validar acceso (Solo Administrador)
if (!isset($_SESSION['usuario_cargo']) || (strtolower(trim($_SESSION['usuario_cargo'])) !== 'administrador' && strtolower(trim($_SESSION['usuario_cargo'])) !== 'secretaria')) {
    echo "<div style='padding:20px; font-family:sans-serif;'><h3 style='color:red;'>Acceso denegado.</h3><p>Solo los administradores y secretarias pueden gestionar comisiones.</p></div>";
    exit;
}

// Cargar lista de funcionarios para selects de manera segura
try {
    $stmtFunc = $pdo->prepare("SELECT id, nombre, paterno, materno FROM funcionario WHERE estado = 'Activo' ORDER BY nombre");
    $stmtFunc->execute();
    $funcionarios = $stmtFunc->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $funcionarios = [];
}

// Manejo de alertas y mensajes de sesión
if (isset($_SESSION['mensaje'])) {
    $tipo = isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : 'success';
    $clase_alert = $tipo === 'danger' ? 'alert-danger' : 'alert-success';
    $mensaje_html = '
    <div class="alert ' . $clase_alert . ' alert-dismissible fade show m-3" role="alert">
        ' . $_SESSION['mensaje'] . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
} else {
    $mensaje_html = '';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Comisiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/correspondencia.css">
    <!-- Script del Tema Oscuro -->
    <script src="../assets/js/theme.js"></script>
</head>
<body>

<?= $mensaje_html ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Gestión de Comisiones</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createComisionModal">
                    <i class="bi bi-plus-circle"></i> Nueva Comisión
                </button>
            </div>

            <div class="table-responsive">
                <table id="comisiones" class="table table-striped table-bordered align-middle text-center w-100">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>N°</th>
                            <th>Nombre de Comisión</th>
                            <th>Descripción</th>
                            <th>Responsable</th>
                            <th>Miembros</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NUEVA COMISIÓN -->
<div class="modal fade" id="createComisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nueva Comisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createComisionForm" action="store.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Comisión</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-primary fw-bold"><i class="bi bi-person-fill-check"></i> Responsable de Comisión</label>
                        <select class="form-select" name="responsable_id" required>
                            <option value="">Seleccione al responsable...</option>
                            <?php foreach ($funcionarios as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars(trim($f['nombre'].' '.($f['paterno']??'').' '.($f['materno']??''))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-success fw-bold"><i class="bi bi-people-fill"></i> Integrantes (Miembros)</label>
                        <select class="form-select select2-multiple" name="miembros[]" multiple="multiple" style="width: 100%;" required>
                            <?php foreach ($funcionarios as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars(trim($f['nombre'].' '.($f['paterno']??'').' '.($f['materno']??''))) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Puede seleccionar a múltiples funcionarios.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" form="createComisionForm">Guardar Comisión</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR COMISIÓN -->
<div class="modal fade" id="editComisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Comisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editComisionForm" action="update.php" method="post">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Comisión</label>
                        <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-primary fw-bold"><i class="bi bi-person-fill-check"></i> Responsable de Comisión</label>
                        <select class="form-select" id="edit_responsable_id" name="responsable_id" required>
                            <option value="">Seleccione al responsable...</option>
                            <?php foreach ($funcionarios as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars(trim($f['nombre'].' '.($f['paterno']??'').' '.($f['materno']??''))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-success fw-bold"><i class="bi bi-people-fill"></i> Integrantes (Miembros)</label>
                        <select class="form-select select2-multiple" id="edit_miembros" name="miembros[]" multiple="multiple" style="width: 100%;" required>
                            <?php foreach ($funcionarios as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars(trim($f['nombre'].' '.($f['paterno']??'').' '.($f['materno']??''))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" form="editComisionForm">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR COMISION -->
<div class="modal fade" id="deleteComisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta comisión?</p>
                <form id="deleteComisionForm" action="delete.php" method="post">
                    <input type="hidden" id="delete_comision_id" name="id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" form="deleteComisionForm">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RESTAURAR COMISION -->
<div class="modal fade" id="restoreComisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Restauración</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea restaurar esta comisión?</p>
                <form id="restoreComisionForm" action="restore.php" method="post">
                    <input type="hidden" id="restore_comision_id" name="id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" form="restoreComisionForm">Restaurar</button>
            </div>
        </div>
    </div>
</div>

















<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#comisiones').DataTable({
        ajax: 'show.php',
        autoWidth: false,
        responsive: true,
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        columns: [
            { data: 'numero' },
            { data: 'nombre' },
            { data: 'descripcion' },
            { data: 'responsable' },
            { data: 'miembros' },
            { data: 'estado' },
            { data: 'acciones' }
        ]
    });

    // Inicializar Select2 general en Modales
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('select:not(.select2-multiple)').each(function() {
            $(this).select2({ theme: 'bootstrap-5', dropdownParent: $(this).parent(), width: '100%' });
        });
        
        $(this).find('.select2-multiple').each(function() {
            $(this).select2({ 
                theme: 'bootstrap-5', 
                dropdownParent: $(this).parent(), 
                width: '100%',
                placeholder: "Seleccione a los integrantes..." 
            });
        });
    });
});

function editarComision(id) {
    $.ajax({
        type: 'POST',
        url: 'edit.php',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            $('#edit_id').val(data.id);
            $('#edit_nombre').val(data.nombre);
            $('#edit_descripcion').val(data.descripcion);
            $('#edit_responsable_id').val(data.responsable_id).trigger('change.select2');
            $('#edit_miembros').val(data.miembros).trigger('change.select2');
            $('#editComisionModal').modal('show');
        }
    });
}
function eliminarComision(id) {
    // Asigna el ID al formulario de eliminación
    $('#delete_comision_id').val(id);

    // Muestra el modal de confirmación
    $('#deleteComisionModal').modal('show');
}
function restoreComision(id) {
    // Asigna el ID al formulario de restauración
    $('#restore_comision_id').val(id);

    // Muestra el modal de confirmación
    $('#restoreComisionModal').modal('show');
}


</script>
</body>
</html>