// =================================================================
// FUNCIONES GLOBALES PARA MANEJAR MODALES DESDE EL IFRAME
// Este archivo se carga en dashboard.php para que los modales
// puedan aparecer por encima del iframe.
// =================================================================

// Instancias de Modales (para reutilizar)
const modals = {};
function getModal(id) {
    if (!modals[id]) {
        modals[id] = new bootstrap.Modal(document.getElementById(id));
    }
    return modals[id];
}

function verFoto(url) {
    document.getElementById('fotoModalImg').src = url;
    getModal('fotoModal').show();
}

function derivarCorrespondencia(id) {
    document.getElementById('derivar_id_correspondencia').value = id;
    const form = document.getElementById('derivarCorrespondenciaForm');
    if (form) form.reset();

    // Resetear Select2 y campos relacionados, ya que form.reset() puede no ser suficiente
    if (typeof $.fn.select2 !== 'undefined') {
        $('#derivar_select_funcionario').val(null).trigger('change');
        $('#derivar_caracter').val('Urgente').trigger('change');
    }
    
    getModal('derivarCorrespondenciaModal').show();
}

function confirmarEliminacion(id) {
    document.getElementById('delete_correspondencia_id').value = id;
    getModal('deleteCorrespondenciaModal').show();
}

function abrirAceptarCorrespondencia(id) {
    document.getElementById('aceptar_correspondencia_id').value = id;
    getModal('aceptarCorrespondenciaModal').show();
}

function abrirRechazarCorrespondencia(id, tipo) {
    const form = document.getElementById('rechazarCorrespondenciaForm');
    form.reset();
    document.getElementById('rechazar_correspondencia_id').value = id;
    document.getElementById('rechazar_action_type').value = 'final';
    document.getElementById('motivoRechazoContainer').style.display = 'none';

    if (tipo === 'No cursada') {
        document.getElementById('rechazarModalTitle').innerHTML = '<i class="bi bi-slash-circle"></i> Marcar como No Cursada';
        document.getElementById('rechazarModalText').innerHTML = '¿Confirma que desea marcar esta correspondencia como <strong>No Cursada</strong>? Esta acción es definitiva.';
        document.getElementById('rechazar_estado_destino').value = 'No cursada';
        document.getElementById('rechazarModalSubmitBtn').textContent = 'Confirmar No Cursada';
    } else {
        document.getElementById('rechazarModalTitle').innerHTML = '<i class="bi bi-x-circle"></i> Rechazar Correspondencia';
        document.getElementById('rechazarModalText').innerHTML = '¿Confirma que desea <strong>Rechazar</strong> esta correspondencia?';
        document.getElementById('rechazar_estado_destino').value = 'Rechazado';
        document.getElementById('rechazarModalSubmitBtn').textContent = 'Confirmar Rechazo';
    }
    getModal('rechazarCorrespondenciaModal').show();
}

function abrirModalDevolucion(id) {
    const form = document.getElementById('rechazarCorrespondenciaForm');
    form.reset();
    document.getElementById('rechazar_correspondencia_id').value = id;
    document.getElementById('rechazar_action_type').value = 'devolver';
    document.getElementById('rechazarModalTitle').innerHTML = '<i class="bi bi-arrow-return-left"></i> Devolver Correspondencia';
    document.getElementById('rechazarModalText').innerHTML = 'Esta acción devolverá la correspondencia al remitente anterior. Debe especificar un motivo.';
    document.getElementById('motivoRechazoContainer').style.display = 'block';
    document.getElementById('motivo_rechazo').required = true;
    document.getElementById('rechazarModalSubmitBtn').textContent = 'Confirmar Devolución';
    getModal('rechazarCorrespondenciaModal').show();
}

function solicitarPagina(id) {
    document.getElementById('print_correspondencia_id').value = id;
    getModal('printPageModal').show();
}

function abrirConcluirCorrespondencia(id) {
    document.getElementById('concluir_correspondencia_id').value = id;
    document.getElementById('concluirCorrespondenciaForm').reset();
    getModal('concluirCorrespondenciaModal').show();
}

function solicitarAmpliacion(id) {
    document.getElementById('ampliacion_correspondencia_id').value = id;
    document.getElementById('solicitarAmpliacionForm').reset();
    getModal('solicitarAmpliacionModal').show();
}

function abrirDesarchivarCorrespondencia(id) {
    document.getElementById('desarchivar_correspondencia_id').value = id;
    document.getElementById('desarchivarCorrespondenciaForm').reset();
    getModal('desarchivarCorrespondenciaModal').show();
}

function abrirSolicitarArchivo(id) {
    document.getElementById('solicitar_archivo_id').value = id;
    document.getElementById('solicitarArchivoForm').reset();
    getModal('solicitarArchivoModal').show();
}

function abrirAprobarArchivo(id) {
    document.getElementById('aprobar_archivo_id').value = id;
    document.getElementById('aprobarArchivoForm').reset();
    getModal('aprobarArchivoModal').show();
}

function abrirArchivarDefinitivo(id) {
    document.getElementById('archivar_definitivo_id').value = id;
    document.getElementById('archivarDefinitivoForm').reset();
    getModal('archivarDefinitivoModal').show();
}

// Funciones que requieren AJAX
async function editarCorrespondencia(id) {
    try {
        const response = await fetch(`correspondencia/edit.php`, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `id=${id}` });
        if (!response.ok) throw new Error('Error de red.');
        const data = await response.json();
        $('#edit_id').val(data.id); $('#edit_hojaruta').val(data.hojaruta); $('#edit_referencia').val(data.referencia); $('#edit_fojas').val(data.fojas); $('#edit_anexo').val(data.anexo); $('#edit_foto_actual').val(data.foto);
        const isExterno = data.tipo_remitente === 'externo';
        $('#edit_checkbox_remitente_externo').prop('checked', isExterno); $('#edit_hidden_tipo_remitente').val(data.tipo_remitente); $('#edit_div_remitente_interno').toggle(!isExterno); $('#edit_div_remitente_externo').toggle(isExterno);
        isExterno ? $('#edit_input_remitente_externo').val(data.remitente_externo) : $('#edit_select_remitente_interno').val(data.remitente_id).trigger('change');
        const preview = $('#edit_foto_preview');
        if (data.foto) { const url = `assets/fotos_correspondencia/${data.foto}`; data.foto.toLowerCase().endsWith('.pdf') ? preview.html(`<a href="${url}" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> Ver PDF</a>`) : preview.html(`<img src="${url}" class="img-thumbnail" style="max-height: 100px;">`); } else { preview.html('<span class="text-muted">Sin foto.</span>'); }
        getModal('editCorrespondenciaModal').show();
    } catch (error) { console.error('Error en editarCorrespondencia:', error); alert('No se pudieron cargar los datos para editar.'); }
}

async function abrirModalAgrupar(id) {
    $('#agrupar_id_hija').val(id);
    const selectMadre = $('#agrupar_id_madre');
    selectMadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
    getModal('agruparCorrespondenciaModal').show();
    try {
        const response = await fetch(`correspondencia/get_groupable_mothers.php?child_id=${id}`);
        if (!response.ok) throw new Error('Error de red.');
        const madres = await response.json();
        selectMadre.empty().append('<option value="">-- Seleccione una correspondencia --</option>');
        if (madres.length > 0) { madres.forEach(madre => selectMadre.append(new Option(`${madre.hojaruta} - ${madre.referencia}`, madre.id))); selectMadre.prop('disabled', false); } else { selectMadre.append('<option value="">No tiene otras correspondencias para agrupar</option>'); }
    } catch (error) { console.error('Error en abrirModalAgrupar:', error); selectMadre.empty().append('<option value="">Error al cargar</option>'); }
}

function abrirModalComision(id) {
    $('#comision_id_correspondencia').val(id);
    $('#comision_responsable').empty().append('<option value="">-- Primero seleccione integrantes --</option>').prop('disabled', true);
    // Asegurarse de que Select2 esté inicializado y disponible
    if (typeof $.fn.select2 !== 'undefined') {
        $('#comision_integrantes').val(null).trigger('change');
    }
    $('#fecha_limite_grupo').val('');
    getModal('conformarComisionModal').show();
}

function abrirModalSubirInforme(id) {
    $('#informe_id_correspondencia').val(id);
    $('#subirInformeForm')[0].reset();
    getModal('subirInformeModal').show();
}

// =================================================================
// Select2 Initialization for Modals (moved from correspondencia.js)
// =================================================================

// Eliminar tabindex de los modales para evitar que Bootstrap bloquee el cuadro de búsqueda (filtro) de Select2
// Esto debe hacerse en el padre, ya que los modales están en el padre.
$(document).on('show.bs.modal', '.modal', function () {
    $(this).attr('tabindex', -1); // Establecer tabindex a -1 para evitar problemas de foco con Select2
});

// Función para renderizar la sigla en negrita dentro de Select2
function formatFuncionario(state) {
    if (!state.id) { return state.text; }
    // Buscar el patrón Nombre Completo - SIGLA
    var match = state.text.match(/^(.*)\s*-\s*(.*?)$/);
    if (match) {
        return $('<span><strong>' + match[1] + '</strong> - ' + match[2] + '</span>');
    }
    // Para opciones de grupo que no tienen sigla
    if (state.text.startsWith('Grupo:')) {
        return $('<span><strong>' + state.text + '</strong></span>');
    }
    return state.text;
}

// Opciones comunes para Select2 en modales
function select2ModalOptions(modalInstance) {
    return {
        theme: 'bootstrap-5',
        dropdownParent: modalInstance,
        width: '100%',
        templateResult: formatFuncionario,
        templateSelection: formatFuncionario
    };
}

// Inicializar Select2 en los modales cuando se muestran (para todos los selects que no sean comision_responsable)
$(document).on('shown.bs.modal', '.modal', function () {
    var modalInstance = $(this);
    // Solo inicializar Select2 si no ha sido inicializado antes
    modalInstance.find('select').each(function() {
        if (!$(this).hasClass("select2-hidden-accessible") && $(this).attr('id') !== 'comision_responsable') { // Excluir comision_responsable que se maneja dinámicamente
            $(this).select2(select2ModalOptions(modalInstance));
        } 
    });
});

// Cuando el usuario seleccione un funcionario o grupo, guardamos el id en el input oculto
$(document).on('change', '#derivar_select_funcionario', function() {
    var fid = $(this).val();
    $('#derivar_id_funcionario').val(fid);
});

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

// Limpiar el formulario cuando se abre el modal de crear
$(document).on('show.bs.modal', '#createCorrespondenciaModal', function() {
    $('#createCorrespondenciaForm')[0].reset();
    // Resetear a estado Interno por defecto
    $('#checkbox_remitente_externo').prop('checked', false).trigger('change');
    if ($('#select_remitente_interno').hasClass("select2-hidden-accessible")) {
        $('#select_remitente_interno').val('').trigger('change');
    }
});

// Actualizar dinámicamente el selector de Responsable según los integrantes seleccionados
$(document).on('change', '#comision_integrantes', function() {
    var selectedOptions = $(this).find('option:selected');
    var responsableSelect = $('#comision_responsable');
    var currentResponsable = responsableSelect.val();
    responsableSelect.empty();
    if (selectedOptions.length === 0) {
        responsableSelect.append('<option value="">-- Primero seleccione integrantes --</option>').prop('disabled', true);
    } else {
        responsableSelect.append('<option value="">-- Seleccione al Responsable --</option>').prop('disabled', false);
        selectedOptions.each(function() {
            responsableSelect.append('<option value="' + $(this).val() + '">' + $(this).attr('data-nombre') + '</option>');
        });
        if (currentResponsable && responsableSelect.find('option[value="' + currentResponsable + '"]').length > 0) {
            responsableSelect.val(currentResponsable);
        }
    }
    responsableSelect.trigger('change');
});

// =================================================================
// PREVENCIÓN DE DOBLE ENVÍO (DOUBLE-CLICK) EN FORMULARIOS
// =================================================================
$(document).on('submit', 'form', function(e) {
    var form = $(this);
    var action = form.attr('action') || '';
    
    // Excluir formularios de actualización (edits) según lo solicitado
    if (action.indexOf('update.php') === -1) {
        
        // CANDADO LÓGICO INFALIBLE: Si ya se está enviando, abortar inmediatamente el segundo clic
        if (form.data('enviando')) {
            e.preventDefault();
            return false;
        }
        // Cerrar el candado
        form.data('enviando', true);
        
        var formId = form.attr('id');
        var submitBtn = form.find('button[type="submit"], input[type="submit"]');
        
        if (submitBtn.length === 0 && formId) {
            submitBtn = $('button[form="' + formId + '"]');
        }
        
        if (submitBtn.length > 0) {
            var originalClass = submitBtn.attr('class');
            var originalHtml = submitBtn.html();
            
            // Cambiar a color plomo y poner texto
            submitBtn.removeClass().addClass('btn btn-secondary').html('Espere...');
            
            // Deshabilitar 10ms después para asegurar que el navegador procese el envío nativo
            setTimeout(function() { submitBtn.prop('disabled', true); }, 10);

            // Restaurar todo a la normalidad después de 1.5 segundos (por si falla el internet o algo)
            setTimeout(function() {
                submitBtn.removeClass().addClass(originalClass).prop('disabled', false).html(originalHtml);
                form.data('enviando', false); // Abrir candado
            }, 1500);
        }
    }
});