<?php 
session_start(); 
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

if (isset($_SESSION['mensaje'])) { 
    $tipo = $_SESSION['mensaje_tipo'] ?? 'success';
    $clase_alert = $tipo === 'danger' ? 'alert-danger' : 'alert-success';
    echo ' <div class="alert ' . $clase_alert . ' alert-dismissible fade show" role="alert"> ' . $_SESSION['mensaje'] . ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>'; 
    unset($_SESSION['mensaje']); 
    unset($_SESSION['mensaje_tipo']);
}
require '../db.php';

$titulo_vista = 'Mis Grupos de Trabajo';
?> 
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo_vista ?> - SIGCOEP</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/correspondencia.css?v=<?= time() ?>" >
    <!-- Theme Script -->
    <script src="../assets/js/theme.js"></script>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0"><?= $titulo_vista ?></h3>
                        </div>
                        
                        <ul class="nav nav-tabs mb-3" id="gruposTabs">
                            <li class="nav-item"><button class="nav-link active filtro-tab" data-filtro="entrantes" type="button"><i class="bi bi-inbox"></i> Entrantes <span class="badge bg-secondary ms-1 count-badge" data-count="entrantes">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="aceptados" type="button"><i class="bi bi-clock-history"></i> Aceptados <span class="badge bg-secondary ms-1 count-badge" data-count="aceptados">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="enviados" type="button"><i class="bi bi-send"></i> Enviados al Responsable <span class="badge bg-secondary ms-1 count-badge" data-count="enviados">0</span></button></li>
                            <li class="nav-item"><button class="nav-link filtro-tab" data-filtro="supervision" type="button"><i class="bi bi-eye"></i> Supervisión (Responsable) <span class="badge bg-secondary ms-1 count-badge" data-count="supervision">0</span></button></li>
                        </ul>

                        <div class="table-responsive">
                            <table id="tablaGrupos" class="table table-striped table-bordered align-middle text-center w-100">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th>Hoja de <br>Ruta</th>
                                        <th>Referencia</th>
                                        <th>Solicitado por</th>
                                        <th>Integrantes</th>
                                        <th>Fecha Límite</th>
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
        </div>
    </div>

    <!-- ================= MODAL SUBIR INFORME ================= -->
    <div class="modal fade" id="subirInformeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cloud-upload"></i> Subir Informe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="subirInformeForm" action="upload_report.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="informe_id_correspondencia" name="id_correspondencia">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contenido / Conclusión:</label>
                            <textarea class="form-control border-4" name="contenido" rows="4" required placeholder="Redacte la conclusión de su informe..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Archivo Adjunto (PDF/Imagen): <small id="nota_archivo_opcional" class="text-muted fw-normal d-none">(Opcional, dejar vacío para conservar el actual)</small></label>
                            <input type="file" class="form-control border-4" name="archivo_informe" accept="image/*,.pdf" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold" form="subirInformeForm"><i class="bi bi-send"></i> Enviar Informe</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL ACEPTAR TAREA ================= -->
    <div class="modal fade" id="aceptarTareaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Aceptar Tarea Grupal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="aceptarTareaForm" action="action.php" method="post">
                        <input type="hidden" id="aceptar_detalle_id" name="detalle_id">
                        <input type="hidden" name="accion" value="aceptar">
                        <p class="mb-0 fs-6">¿Confirma que desea <strong>Aceptar</strong> la participación en este grupo y comenzar a trabajar en su informe?</p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" form="aceptarTareaForm">Aceptar Tarea</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL RECHAZAR TAREA ================= -->
    <div class="modal fade" id="rechazarTareaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle"></i> Rechazar Tarea Grupal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rechazarTareaForm" action="action.php" method="post">
                        <input type="hidden" id="rechazar_detalle_id" name="detalle_id">
                        <input type="hidden" name="accion" value="rechazar">
                        <p class="mb-0 fs-6">¿Está seguro que desea <strong>Rechazar</strong> la tarea? El responsable será notificado.</p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="rechazarTareaForm">Rechazar Tarea</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL REVISAR INFORME INDIVIDUAL ================= -->
    <div class="modal fade" id="revisarInformeUnicoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-info">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title"><i class="bi bi-search"></i> Revisar Informe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="revisarInformeUnicoForm" action="review_report.php" method="post">
                        <input type="hidden" id="revisar_detalle_id" name="detalle_id">
                        
                        <p class="mb-2"><strong>Funcionario:</strong> <span id="revisar_nombre_funcionario" class="text-primary fw-bold"></span></p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Conclusión / Contenido:</label>
                            <div class="p-2 border rounded bg-light text-wrap text-break" id="revisar_contenido" style="min-height: 60px; font-size: 0.9rem;"></div>
                        </div>
                        
                        <div class="mb-3">
                            <a href="#" id="revisar_enlace_pdf" target="_blank" class="btn btn-outline-danger btn-sm fw-bold"><i class="bi bi-file-earmark-pdf"></i> Ver PDF Adjunto</a>
                        </div>
                        
                        <div class="mb-3 mt-4 border-top pt-3">
                            <label class="form-label fw-bold text-danger">Observaciones <small class="text-muted">(Requerido solo si rechaza)</small>:</label>
                            <textarea class="form-control border-danger" id="revisar_observaciones" name="observaciones" rows="2" placeholder="Describa el motivo de la observación si va a rechazar el informe..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between bg-light">
                    <button type="submit" name="accion" value="rechazar" class="btn btn-danger" form="revisarInformeUnicoForm" onclick="return validarObservacion()"><i class="bi bi-x-circle"></i> Observar (Rechazar)</button>
                    <button type="submit" name="accion" value="aprobar" class="btn btn-success" form="revisarInformeUnicoForm"><i class="bi bi-check-circle"></i> Aprobar Informe</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons para Excel -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#tablaGrupos').DataTable({
                ajax: {
                    url: 'show.php',
                    type: 'POST',
                    data: function(d) {
                        d.filtro = $('.filtro-tab.active').data('filtro') || 'entrantes';
                    }
                },
                autoWidth: false,
                responsive: true,
                dom: '<"row align-items-center mb-3"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm ms-1',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    }
                ],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
                initComplete: function(settings, json) {
                    setTimeout(function() { table.columns.adjust().responsive.recalc(); }, 100);
                },
                columns: [
                    { data: 'hojaruta' },
                    { data: 'referencia', width: '250px', className: 'wrap-text' },
                    { data: 'creador', width: '150px', className: 'wrap-text' },
                    { data: 'integrantes', width: '200px', className: 'wrap-text' },
                    { data: 'fecha_limite' },
                    { data: 'estado' },
                    { data: 'acciones', orderable: false, searchable: false }
                ]
            });

            // Actualizar contadores
            table.on('xhr.dt', function ( e, settings, json, xhr ) {
                if (json && json.counts) {
                    for (const [key, value] of Object.entries(json.counts)) {
                        $('.count-badge[data-count="' + key + '"]').text(value);
                    }
                }
            });

            // Cambiar de pestaña
            $('.filtro-tab').on('click', function(e) {
                e.preventDefault();
                $('.filtro-tab').removeClass('active');
                $(this).addClass('active');
                table.ajax.reload();
            });
        });

        function abrirAceptarTarea(detalle_id) {
            $('#aceptar_detalle_id').val(detalle_id);
            $('#aceptarTareaModal').modal('show');
        }

        function abrirRechazarTarea(detalle_id) {
            $('#rechazar_detalle_id').val(detalle_id);
            $('#rechazarTareaModal').modal('show');
        }

        function abrirModalSubirInforme(id_correspondencia) {
            $('#informe_id_correspondencia').val(id_correspondencia);
            $('#subirInformeForm')[0].reset();
            $('#subirInformeModal .modal-title').html('<i class="bi bi-cloud-upload"></i> Subir Informe');
            $('#subirInformeForm button[type="submit"]').html('<i class="bi bi-send"></i> Enviar Informe');
            $('#subirInformeForm input[name="archivo_informe"]').attr('required', 'required');
            $('#nota_archivo_opcional').addClass('d-none');
            $('#subirInformeModal').modal('show');
        }

        function abrirModalEditarInforme(id_correspondencia, contenido) {
            $('#informe_id_correspondencia').val(id_correspondencia);
            $('#subirInformeForm')[0].reset();
            $('#subirInformeForm textarea[name="contenido"]').val(contenido);
            $('#subirInformeModal .modal-title').html('<i class="bi bi-pencil-square"></i> Editar Informe');
            $('#subirInformeForm button[type="submit"]').html('<i class="bi bi-save"></i> Guardar Cambios');
            $('#subirInformeForm input[name="archivo_informe"]').removeAttr('required');
            $('#nota_archivo_opcional').removeClass('d-none');
            $('#subirInformeModal').modal('show');
        }

        function abrirRevisarInformeUnico(detalle_id, nombre, contenido, archivo) {
            $('#revisar_detalle_id').val(detalle_id);
            $('#revisar_nombre_funcionario').text(nombre);
            $('#revisar_contenido').text(contenido);
            $('#revisar_enlace_pdf').attr('href', '../assets/informes_grupo/' + archivo);
            $('#revisar_observaciones').val(''); // Limpiar caja
            $('#revisarInformeUnicoModal').modal('show');
        }

        function validarObservacion() {
            if ($('#revisar_observaciones').val().trim() === '') {
                alert('Debe ingresar una observación detallada para poder rechazar el informe. El funcionario necesita saber qué corregir.');
                $('#revisar_observaciones').focus();
                return false;
            }
            return true;
        }

        function abrirRevisarInformes(grupo_id) {
            // Aquí enlazaremos la lógica para el Supervisor (Consolidación)
            // Por ahora redirigimos o abrimos modal. Lo programaremos en el siguiente paso.
            alert("Abriendo panel de revisión para el grupo: " + grupo_id);
        }

        function verInforme(ruta) {
            window.open(ruta, '_blank');
        }
    </script>
</body>
</html>