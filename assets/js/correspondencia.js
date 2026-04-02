$(document).ready(function() {
    // Eliminar tabindex de los modales para evitar que Bootstrap bloquee el cuadro de búsqueda (filtro) de Select2
    $('.modal').on('show.bs.modal', function () {
        $(this).removeAttr('tabindex');
    });

    var table = $('#correspondencia').DataTable({
        ajax: {
            url: 'show.php',
            type: 'POST',
            data: function(d) {
                d.filtro = $('.filtro-tab.active').data('filtro') || '';
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
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 6, 7] // Omitimos las columnas 5 (Foto) y 8 (Acciones)
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm ms-1',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 6, 7] // Omitimos las columnas de Foto y Acciones
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        initComplete: function(settings, json) {
            setTimeout(function() {
                table.columns.adjust().responsive.recalc();
            }, 100);
        },
        columns: [
            { data: 'hojaruta' },
            { data: 'remitente' },
            { data: 'referencia' },
            { data: 'fojas' },
            { data: 'anexo' },
            { data: 'foto', orderable: false, searchable: false },
            { data: 'fecha' },
            { data: 'estado' },
            { data: 'acciones' }
        ]
    });

    // Actualizar contadores en base a los datos que retorna el servidor
    table.on('xhr.dt', function ( e, settings, json, xhr ) {
        if (json && json.counts) {
            for (const [key, value] of Object.entries(json.counts)) {
                $('.count-badge[data-count="' + key + '"]').text(value);
            }
        }
    });

    // Evento para cambiar de pestaña y recargar la tabla
    $('.filtro-tab').on('click', function(e) {
        e.preventDefault();
        // Cambiar la clase active
        $('.filtro-tab').removeClass('active');
        $(this).addClass('active');
        // Recargar DataTables (enviará el nuevo data-filtro al backend)
        table.ajax.reload();
    });

    // Función para renderizar la sigla en negrita dentro de Select2
    function formatFuncionario(state) {
        if (!state.id) { return state.text; }
        // Buscar el patrón Nombre Completo - SIGLA
        var match = state.text.match(/^(.*)\s*-\s*(.*?)$/);
        if (match) {
            return $('<span><strong>' + match[1] + '</strong> - ' + match[2] + '</span>');
        }
        return state.text;
    }

    // Inicializar Select2 en los modales para que tengan buscador
    $('.modal').on('shown.bs.modal', function () {
        var modalInstance = $(this);
        $(this).find('select').each(function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    dropdownParent: modalInstance,
                    width: '100%',
                    templateResult: formatFuncionario,
                    templateSelection: formatFuncionario
                });
            }
        });
    });
});

function editarCorrespondencia(id) {
    $.ajax({
        type: 'POST',
        url: 'edit.php',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            $('#edit_id').val(data.id);
            $('#edit_hojaruta').val(data.hojaruta);
            $('#edit_referencia').val(data.referencia);
            $('#edit_fojas').val(data.fojas);
            $('#edit_anexo').val(data.anexo);
            $('#edit_foto_actual').val(data.foto || '');

            if (data.foto) {
                var urlFoto = '../assets/fotos_correspondencia/' + data.foto;
                var ext = data.foto.split('.').pop().toLowerCase();
                if (ext === 'pdf') {
                    $('#edit_foto_preview').html('<a href="' + urlFoto + '" target="_blank" class="text-danger text-decoration-none" title="Ver PDF actual"><i class="bi bi-file-earmark-pdf-fill" style="font-size: 2rem; vertical-align: middle;"></i> Ver documento PDF actual</a>');
                } else {
                    $('#edit_foto_preview').html('<img src="' + urlFoto + '" alt="Foto actual" class="img-fluid rounded border">');
                }
            } else {
                $('#edit_foto_preview').html('<span class="text-muted">Sin foto registrada</span>');
            }

            // Configurar tipo de remitente y selector/entrada correspondiente
            if (data.tipo_remitente === 'interno') {
                $('#edit_checkbox_remitente_externo').prop('checked', false).trigger('change');
                $('#edit_select_remitente_interno').val(data.remitente_id).trigger('change');
                $('#edit_input_remitente_externo').val('');
            } else {
                $('#edit_checkbox_remitente_externo').prop('checked', true).trigger('change');
                $('#edit_select_remitente_interno').val('').trigger('change');
                $('#edit_input_remitente_externo').val(data.remitente_externo || data.remitente);
            }
            
            $('#editCorrespondenciaModal').modal('show');
        }
    });
}

function derivarCorrespondencia(id) {
    // Cargar el ID en el campo oculto del modal
    $('#derivar_id_correspondencia').val(id);
    // limpiar campos previos
    $('#derivar_select_funcionario').val('');
    $('#derivar_id_funcionario').val('');
    $('#derivar_instruccion').val('');
    $('#derivar_fojas').val('');
    $('#derivar_caracter').val('Urgente');
    
    if ($('#derivar_select_funcionario').hasClass("select2-hidden-accessible")) {
        $('#derivar_select_funcionario').trigger('change.select2');
        $('#derivar_caracter').trigger('change.select2');
    }
    
    // Mostrar el modal
    $('#derivarCorrespondenciaModal').modal('show');
}

// Cuando el usuario seleccione un funcionario, guardamos el id en el input oculto
$(document).on('change', '#derivar_select_funcionario', function() {
    var fid = $(this).val();
    $('#derivar_id_funcionario').val(fid);
});

// Ver foto en modal grande
function verFoto(url) {
    $('#fotoModalImg').attr('src', url);
    var modal = new bootstrap.Modal(document.getElementById('fotoModal'));
    modal.show();
}

// función para abrir el modal de impresión y fijar el id
function solicitarPagina(id) {
    $('#print_correspondencia_id').val(id);
    $('#print_page_number').val(1);
    $('#printPageModal').modal('show');
}

// función para confirmar la eliminación
function confirmarEliminacion(id) {
    $('#delete_correspondencia_id').val(id);
    $('#deleteCorrespondenciaModal').modal('show');
}

// Abrir modal para aceptar correspondencia
function abrirAceptarCorrespondencia(id) {
    $('#aceptar_correspondencia_id').val(id);
    $('#aceptarCorrespondenciaModal').modal('show');
}

// Abrir modal para concluir
function abrirConcluirCorrespondencia(id) {
    $('#concluir_correspondencia_id').val(id);
    $('#concluirCorrespondenciaForm')[0].reset();
    $('#concluirCorrespondenciaModal').modal('show');
}

// Abrir modal para solicitar archivo
function abrirSolicitarArchivo(id) {
    $('#solicitar_archivo_id').val(id);
    $('#solicitarArchivoForm')[0].reset();
    $('#solicitarArchivoModal').modal('show');
}

// NUEVA FUNCIÓN: Abrir modal para solicitar ampliación
function solicitarAmpliacion(id) {
    $('#ampliacion_correspondencia_id').val(id);
    $('#solicitarAmpliacionForm')[0].reset();
    $('#solicitarAmpliacionModal').modal('show');
}

// NUEVA FUNCIÓN: Abrir modal para desarchivar
function abrirDesarchivarCorrespondencia(id) {
    $('#desarchivar_correspondencia_id').val(id);
    $('#desarchivarCorrespondenciaForm')[0].reset();
    $('#desarchivarCorrespondenciaModal').modal('show');
}

// Función para "No Cursada" (estado final) y "Rechazar"
function abrirRechazarCorrespondencia(id, tipo = 'Rechazado') {
    $('#rechazar_correspondencia_id').val(id);
    $('#rechazar_estado_destino').val(tipo);
    $('#rechazar_action_type').val('final');

    $('#motivoRechazoContainer').hide();
    $('#motivo_rechazo').prop('required', false);
    
    if(tipo === 'No cursada') {
        $('#rechazarModalTitle').text('Correspondencia No Cursada');
        $('#rechazarModalText').html('¿Confirma que desea marcar esta correspondencia como <strong>No Cursada</strong>? (No procede para trámite).');
    } else {
        $('#rechazarModalTitle').text('Rechazar correspondencia');
        $('#rechazarModalText').html('¿Confirma que desea <strong>Rechazar</strong> esta correspondencia?');
    }
    $('#rechazarModalSubmitBtn').text('Confirmar').removeClass('btn-warning').addClass('btn-danger');
    $('#rechazarCorrespondenciaModal').modal('show');
}

// Nueva función para "Devolver" (retorno al remitente anterior)
function abrirModalDevolucion(id) {
    $('#rechazar_correspondencia_id').val(id);
    $('#rechazar_action_type').val('devolver');

    $('#motivoRechazoContainer').show();
    $('#motivo_rechazo').prop('required', true);
    $('#motivo_rechazo').val('');

    $('#rechazarModalTitle').text('Devolver Correspondencia');
    $('#rechazarModalText').html('La correspondencia será devuelta al remitente anterior. Por favor, especifique el motivo:');
    $('#rechazarModalSubmitBtn').text('Devolver').removeClass('btn-warning').addClass('btn-danger');
    $('#rechazarCorrespondenciaModal').modal('show');
}

// Abrir modal para agrupar correspondencia
function abrirModalAgrupar(id) {
    $('#agrupar_id_hija').val(id);
    
    // Limpiar el select y mostrar un estado de carga mientras se obtienen los datos
    $('#agrupar_id_madre').empty().append('<option value="">Cargando opciones...</option>').trigger('change');
    $('#agruparCorrespondenciaModal').modal('show');

    // Cargar las opciones vía AJAX excluyendo la correspondencia actual
    $.ajax({
        url: 'get_groupable_mothers.php',
        type: 'GET',
        data: { child_id: id },
        dataType: 'json',
        success: function(response) {
            $('#agrupar_id_madre').empty().append('<option value="">-- Seleccione una correspondencia --</option>');
            if (response && !response.error) {
                $.each(response, function(index, madre) {
                    $('#agrupar_id_madre').append('<option value="' + madre.id + '">HR: ' + madre.hojaruta + ' - ' + madre.referencia + '</option>');
                });
            }
            $('#agrupar_id_madre').trigger('change'); // Refrescar el Select2
        },
        error: function() {
            $('#agrupar_id_madre').empty().append('<option value="">Error al cargar opciones</option>').trigger('change');
        }
    });
}

// Abrir modal para aprobar archivo (Jefe)
function abrirAprobarArchivo(id) {
    $('#aprobar_archivo_id').val(id);
    $('#aprobarArchivoForm')[0].reset();
    $('#aprobarArchivoModal').modal('show');
}

// Abrir modal para archivar definitivo (Archivista Central)
function abrirArchivarDefinitivo(id) {
    $('#archivar_definitivo_id').val(id);
    $('#archivarDefinitivoForm')[0].reset();
    $('#archivarDefinitivoModal').modal('show');
}

// Manejo de Checkbox para remitente externo (Crear)
$(document).on('change', '#checkbox_remitente_externo', function() {
    if ($(this).is(':checked')) {
        $('#hidden_tipo_remitente').val('externo');
        $('#div_remitente_interno').hide();
        $('#div_remitente_externo').show();
        $('#select_remitente_interno').removeAttr('required');
        $('#input_remitente_externo').attr('required', 'required');
    } else {
        $('#hidden_tipo_remitente').val('interno');
        $('#div_remitente_interno').show();
        $('#div_remitente_externo').hide();
        $('#select_remitente_interno').attr('required', 'required');
        $('#input_remitente_externo').removeAttr('required');
    }
});

// Manejo de Checkbox para remitente externo (Editar)
$(document).on('change', '#edit_checkbox_remitente_externo', function() {
    if ($(this).is(':checked')) {
        $('#edit_hidden_tipo_remitente').val('externo');
        $('#edit_div_remitente_interno').hide();
        $('#edit_div_remitente_externo').show();
    } else {
        $('#edit_hidden_tipo_remitente').val('interno');
        $('#edit_div_remitente_interno').show();
        $('#edit_div_remitente_externo').hide();
    }
});

// Limpiar el formulario cuando se abre el modal
$(document).on('show.bs.modal', '#createCorrespondenciaModal', function() {
    $('#createCorrespondenciaForm')[0].reset();
    
    // Resetear a estado Interno por defecto
    $('#checkbox_remitente_externo').prop('checked', false).trigger('change');
    
    if ($('#select_remitente_interno').hasClass("select2-hidden-accessible")) {
        $('#select_remitente_interno').val('').trigger('change');
    }
});