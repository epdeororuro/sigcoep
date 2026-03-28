$(document).ready(function() {
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

    // Inicializar Select2 en los modales para que tengan buscador
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('select').each(function() {
            $(this).select2({
                theme: 'bootstrap-5',
                dropdownParent: $(this).parent(),
                width: '100%'
            });
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
                $('#edit_foto_preview').html('<img src="' + urlFoto + '" alt="Foto actual" class="img-fluid rounded border">');
            } else {
                $('#edit_foto_preview').html('<span class="text-muted">Sin foto registrada</span>');
            }

            // Configurar tipo de remitente y selector/entrada correspondiente
            if (data.tipo_remitente === 'interno') {
                $('#edit_remitente_interno').prop('checked', true);
                $('#edit_div_remitente_interno').show();
                $('#edit_div_remitente_externo').hide();
                $('#edit_select_remitente_interno').val(data.remitente_id);
                $('#edit_input_remitente_externo').val('');
            } else {
                $('#edit_remitente_externo_radio').prop('checked', true);
                $('#edit_div_remitente_interno').hide();
                $('#edit_div_remitente_externo').show();
                $('#edit_select_remitente_interno').val('');
                $('#edit_input_remitente_externo').val(data.remitente_externo || data.remitente);
            }
            
            // Actualizar vista del select si ya tiene select2
            if ($('#edit_select_remitente_interno').hasClass("select2-hidden-accessible")) {
                $('#edit_select_remitente_interno').trigger('change.select2');
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

// Manejo de radiobuttons para tipo de remitente
$(document).on('change', 'input[name="tipo_remitente"]', function() {
    var tipoRemitente = $(this).val();
    if (tipoRemitente === 'interno') {
        $('#div_remitente_interno').show();
        $('#div_remitente_externo').hide();
        $('#select_remitente_interno').attr('required', 'required');
        $('#input_remitente_externo').removeAttr('required');
    } else if (tipoRemitente === 'externo') {
        $('#div_remitente_interno').hide();
        $('#div_remitente_externo').show();
        $('#input_remitente_externo').attr('required', 'required');
        $('#select_remitente_interno').removeAttr('required');
    }
});

// Manejo de radiobuttons para tipo de remitente en el modal de edición
$(document).on('change', 'input[name="edit_tipo_remitente"]', function() {
    var tipoRemitente = $(this).val();
    if (tipoRemitente === 'interno') {
        $('#edit_div_remitente_interno').show();
        $('#edit_div_remitente_externo').hide();
    } else if (tipoRemitente === 'externo') {
        $('#edit_div_remitente_interno').hide();
        $('#edit_div_remitente_externo').show();
    }
});

// Limpiar el formulario cuando se abre el modal
$(document).on('show.bs.modal', '#createCorrespondenciaModal', function() {
    $('#createCorrespondenciaForm')[0].reset();
    $('#div_remitente_interno').hide();
    $('#div_remitente_externo').hide();
    $('#select_remitente_interno').removeAttr('required');
    $('#input_remitente_externo').removeAttr('required');
    
    if ($('#select_remitente_interno').hasClass("select2-hidden-accessible")) {
        $('#select_remitente_interno').val('').trigger('change.select2');
    }
});