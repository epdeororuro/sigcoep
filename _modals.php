    <!-- ================= MODAL NUEVA CORRESPONDENCIA ================= -->
    <div class="modal fade" id="createCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-envelope-plus"></i> Agregar Nueva Correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createCorrespondenciaForm" action="correspondencia/store.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Hoja de ruta</label>
                            <input type="text" class="form-control" name="hojaruta" value="<?= htmlspecialchars($siguienteHojaRuta) ?>" readonly>
                        </div>
                        
                        <!-- Remitente Interno (Select) -->
                        <div class="mb-3" id="div_remitente_interno">
                            <label class="form-label">Seleccione Funcionario</label>
                            <select class="form-select border-4" id="select_remitente_interno" name="remitente_id" style="width: 100%;">
                                <option value="">-- Seleccione un funcionario --</option>
                                <?php foreach($funcionarios as $f): ?>
                                    <option value="<?= htmlspecialchars($f['id']) ?>">
                                        <?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?> - <?= htmlspecialchars($f['sigla'] ?? 'S/S') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Remitente Externo (Input) -->
                        <div class="mb-3" id="div_remitente_externo" style="display: none;">
                            <label class="form-label">Nombre del Remitente Externo</label>
                            <input type="text" class="form-control border-4" id="input_remitente_externo" name="remitente_externo">
                        </div>
                        
                        <!-- Tipo de Remitente (Checkbox) -->
                        <div class="mb-3">
                            <input type="hidden" name="tipo_remitente" id="hidden_tipo_remitente" value="interno">
                            <div class="border p-2 rounded bg-light">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input class="form-check-input fs-5 mt-0" type="checkbox" id="checkbox_remitente_externo">
                                    <label class="form-check-label fw-bold ms-2" for="checkbox_remitente_externo">
                                        Remitente Externo
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <textarea class="form-control border-4" name="referencia" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <textarea class="form-control border-4" id="fojas" name="fojas" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Anexo</label>
                            <textarea class="form-control border-4" id="anexo" name="anexo"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto obligatorio</label>
                            <input type="file" class="form-control border-4" name="foto" accept="image/*,.pdf" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" form="createCorrespondenciaForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL EDITAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="editCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCorrespondenciaForm" action="correspondencia/update.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="edit_id" name="id">
                        <input type="hidden" id="edit_foto_actual" name="foto_actual">
                        <div class="mb-3">
                            <label class="form-label">Hoja de ruta</label>
                            <input type="text" class="form-control border-4" id="edit_hojaruta" name="hojaruta">
                        </div>
                        <!-- Remitente Interno (Select) -->
                        <div class="mb-3" id="edit_div_remitente_interno">
                            <label class="form-label">Seleccione Funcionario</label>
                            <select class="form-select border-4" id="edit_select_remitente_interno" name="edit_remitente_id" style="width: 100%;">
                                <option value="">-- Seleccione un funcionario --</option>
                                <?php foreach($funcionarios as $f): ?>
                                    <option value="<?= htmlspecialchars($f['id']) ?>">
                                        <?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?> - <?= htmlspecialchars($f['sigla'] ?? 'S/S') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Remitente Externo (Input) -->
                        <div class="mb-3" id="edit_div_remitente_externo" style="display: none;">
                            <label class="form-label">Nombre del Remitente Externo</label>
                            <input type="text" class="form-control border-4" id="edit_input_remitente_externo" name="edit_remitente_externo">
                        </div>
                        <!-- Tipo de Remitente (Checkbox) -->
                        <div class="mb-3">
                            <input type="hidden" name="edit_tipo_remitente" id="edit_hidden_tipo_remitente" value="interno">
                            <div class="border p-2 rounded bg-light">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input class="form-check-input fs-5 mt-0" type="checkbox" id="edit_checkbox_remitente_externo">
                                    <label class="form-check-label fw-bold ms-2" for="edit_checkbox_remitente_externo">
                                        Remitente Externo
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referencia</label>
                            <textarea class="form-control border-4" id="edit_referencia" name="referencia"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas</label>
                            <textarea class="form-control border-4" id="edit_fojas" name="fojas"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Anexo</label>
                            <textarea class="form-control border-4" id="edit_anexo" name="edit_anexo"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto actual</label>
                            <div id="edit_foto_preview" class="mb-2 border-4"></div>
                            <label class="form-label">Cambiar foto (opcional)</label>
                            <input type="file" class="form-control" name="foto_nueva" accept="image/*,.pdf">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark" form="editCorrespondenciaForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL VER FOTO ================= -->
    <div class="modal fade" id="fotoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto de la correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="fotoModalImg" src="" alt="Foto de la correspondencia" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL DERIVAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="derivarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-right-circle"></i> Derivar Correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="derivarCorrespondenciaForm" action="derivacion/store.php" method="post">
                        <input type="hidden" id="derivar_id_correspondencia" name="id_correspondencia">
                        <input type="hidden" id="derivar_id_funcionario" name="id_funcionario">
                        <div class="mb-3">
                            <label class="form-label">Derivar a (seleccione):</label>
                            <select id="derivar_select_funcionario" class="form-select border-4" required style="width: 100%;">
                                <option value="">-- Funcionario --</option>
                                <optgroup label="Grupos de Trabajo / Comisiones">
                                    <?php foreach($comisiones as $c): ?>
                                        <option value="c_<?= htmlspecialchars($c['id']) ?>">Grupo: <?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Funcionarios">
                                    <?php foreach($funcionarios as $f): ?>
                                    <option value="f_<?= htmlspecialchars($f['id']) ?>">
                                        <?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?> - <?= htmlspecialchars($f['sigla'] ?? 'S/S') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instrucción adicional</label>
                            <textarea class="form-control border-4" id="derivar_instruccion" name="instruccion_adicional"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fojas a añadir</label>
                            <input type="number" min="0" class="form-control border-4" id="derivar_fojas"
                                name="fojas">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Carácter</label>
                            <select class="form-select" id="derivar_caracter" name="caracter" required style="width: 100%;">
                                <option value="Urgente">Urgente</option>
                                <option value="Para conocimiento">Para conocimiento</option>
                                <option value="Preparar Respuesta">Preparar Respuesta</option>
                                <option value="Procesar">Procesar</option>
                                <option value="Preparar Informe">Preparar Informe</option>
                                <option value="Archivo">Archivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" form="derivarCorrespondenciaForm">Derivar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL IMPRESIÓN DE PÁGINA ================= -->
    <div class="modal fade" id="printPageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-info">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title"><i class="bi bi-printer"></i> Indica el número de página de hoja de ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="printPageForm" action="correspondencia/report.php" method="post">
                        <input type="hidden" id="print_correspondencia_id" name="id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Seleccione qué página imprimir</label>
                            <select class="form-select border-4" id="print_page_number" name="page" required style="width: 100%;">
                                <option value="1">Página 1 (Hoja Principal y Destinatarios 1 al 4)</option>
                                <option value="2">Página 2 (Destinatarios 5 al 7)</option>
                                <option value="3">Página 3 (Destinatarios 8 al 10)</option>
                                <option value="4">Página 4 (Destinatarios 11 al 13)</option>
                                <option value="5">Página 5 (Destinatarios 14 al 16)</option>
                                <option value="6">Página 6 (Destinatarios 17 al 19)</option>
                                <option value="7">Página 7 (Destinatarios 20 al 22)</option>
                                <option value="8">Página 8 (Destinatarios 23 al 25)</option>
                                <option value="9">Página 9 (Destinatarios 26 al 28)</option>
                                <option value="10">Página 10 (Destinatarios 29 al 31)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" form="printPageForm">Generar hoja</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL ELIMINAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="deleteCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash"></i> Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta correspondencia?</p>
                    <form id="deleteCorrespondenciaForm" action="correspondencia/destroy.php" method="post">
                        <input type="hidden" id="delete_correspondencia_id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="deleteCorrespondenciaForm">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL ACEPTAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="aceptarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Aceptar correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="aceptarCorrespondenciaForm" action="correspondencia/accept.php" method="post">
                        <input type="hidden" id="aceptar_correspondencia_id" name="id">
                        <p class="mb-0 fs-6">¿Confirma que desea marcar esta correspondencia como <strong>Aceptada</strong>?</p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" form="aceptarCorrespondenciaForm">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL RECHAZAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="rechazarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="rechazarModalTitle">Rechazar correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rechazarCorrespondenciaForm" action="correspondencia/reject.php" method="post">
                        <input type="hidden" id="rechazar_correspondencia_id" name="id">
                        <input type="hidden" id="rechazar_estado_destino" name="estado_destino" value="Rechazado">
                        <input type="hidden" id="rechazar_action_type" name="action_type" value="final">

                        <p id="rechazarModalText">¿Confirma que desea <strong>Rechazar</strong> esta correspondencia?</p>

                        <div class="mb-3" id="motivoRechazoContainer" style="display: none;">
                            <label for="motivo_rechazo" class="form-label">Motivo de la Devolución:</label>
                            <textarea class="form-control border border-danger border-2 shadow-sm" id="motivo_rechazo" name="motivo_rechazo" rows="3" placeholder="Describa el motivo por el cual devuelve esta correspondencia..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="rechazarCorrespondenciaForm" id="rechazarModalSubmitBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL SOLICITAR ARCHIVO ================= -->
    <div class="modal fade" id="solicitarArchivoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-dark">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-archive-fill"></i> Solicitar Envío a Archivo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="solicitarArchivoForm" action="correspondencia/request_archive.php" method="post">
                        <input type="hidden" id="solicitar_archivo_id" name="id">
                        <p>Esta acción enviará una solicitud formal a su Jefe de Área (o directo al Archivo) para autorizar el traslado físico del documento.</p>
                        <div class="mb-3">
                            <label class="form-label">Nota o Justificación <small class="text-muted">(Opcional)</small></label>
                            <textarea class="form-control border-dark" name="nota_solicitud" rows="2" placeholder="Ej: Trámite finalizado, se remite carpeta completa..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" form="solicitarArchivoForm">Enviar Solicitud</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL CONCLUIR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="concluirCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-check2-circle"></i> Concluir Trámite</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="concluirCorrespondenciaForm" action="correspondencia/conclude.php" method="post">
                        <input type="hidden" id="concluir_correspondencia_id" name="id">
                        <p>Esta acción marcará el trámite como <strong>Concluido</strong> en su bandeja. El documento quedará a la espera de ser enviado al Archivo Central posteriormente.</p>
                        <div class="mb-3">
                            <label class="form-label">Nota de Conclusión <small class="text-muted">(Opcional)</small></label>
                            <textarea class="form-control border-primary" name="nota_conclusion" rows="2" placeholder="Ej: Se atendió la solicitud, se adjuntó informe final..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="concluirCorrespondenciaForm">Confirmar Conclusión</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL SOLICITAR AMPLIACIÓN ================= -->
    <div class="modal fade" id="solicitarAmpliacionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus"></i> Solicitar Ampliación de Plazo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="solicitarAmpliacionForm" action="correspondencia/extend.php" method="post">
                        <input type="hidden" id="ampliacion_correspondencia_id" name="id">
                        <p>Está a punto de solicitar una ampliación de <strong>5 días hábiles</strong> para atender esta correspondencia.</p>
                        <p>Esta acción quedará registrada en el historial del documento. ¿Desea continuar?</p>
                        <div class="mb-3 mt-3">
                            <label class="form-label"><strong>Motivo de la ampliación (Justificación):</strong></label>
                            <textarea class="form-control border-primary" name="justificacion" rows="3" required placeholder="Explique brevemente por qué necesita más tiempo para atender la correspondencia..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="solicitarAmpliacionForm">Sí, solicitar ampliación</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL DESARCHIVAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="desarchivarCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-light text-success border-bottom border-success">
                    <h5 class="modal-title"><i class="bi bi-box-arrow-up"></i> Desarchivar Correspondencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="desarchivarCorrespondenciaForm" action="correspondencia/unarchive.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="desarchivar_correspondencia_id" name="id">
                        <p>¿Está seguro de que desea <strong>desarchivar</strong> esta correspondencia?</p>
                        <p class="text-muted small">El documento volverá a su bandeja de pendientes (Aceptados) para continuar con su trámite.</p>
                        
                        <div class="mb-3 mt-3">
                            <label class="form-label"><strong>Autorización / Justificación:</strong> <small class="text-muted">(Requerido para Archivo Central)</small></label>
                            <textarea class="form-control border-success" name="autorizacion" rows="2" placeholder="Especifique quién autorizó el desarchivo y por qué..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Foto de respaldo:</strong> <small class="text-danger fw-bold">(Obligatorio)</small></label>
                            <input type="file" class="form-control border-success" name="foto_desarchivo" accept="image/*,.pdf" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-outline-success fw-bold" form="desarchivarCorrespondenciaForm">Sí, Desarchivar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL AGRUPAR CORRESPONDENCIA ================= -->
    <div class="modal fade" id="agruparCorrespondenciaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-info">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-folder-symlink"></i> Agrupar Correspondencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="agruparCorrespondenciaForm" action="correspondencia/group.php" method="POST">
                        <input type="hidden" name="id_hija" id="agrupar_id_hija" value="">
                        
                        <p>Seleccione la correspondencia principal (Madre) a la cual desea adjuntar este trámite. <strong>Esta acción concluirá el trámite actual.</strong></p>
                        
                        <div class="mb-3 mt-3">
                            <label for="agrupar_id_madre" class="form-label fw-bold">Hoja de Ruta Destino (Madre) <span class="text-danger">*</span></label>
                            <select class="form-select border-info" name="id_madre" id="agrupar_id_madre" required style="width: 100%;">
                                <option value="">-- Seleccione una correspondencia --</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white" form="agruparCorrespondenciaForm"><i class="bi bi-check-circle"></i> Confirmar Agrupación</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL APROBAR ARCHIVO ================= -->
    <div class="modal fade" id="aprobarArchivoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-all"></i> Aprobar Envío a Archivo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="aprobarArchivoForm" action="correspondencia/archive.php" method="post">
                        <input type="hidden" id="aprobar_archivo_id" name="id">
                        <input type="hidden" name="tipo_archivo" value="central">
                        <p>¿Confirma que autoriza el traslado de este documento al Archivo Central?</p>
                        <div class="mb-3">
                            <label class="form-label">Instrucción / Proveído <small class="text-muted">(Opcional)</small></label>
                            <textarea class="form-control border-success" name="nota_archivo" rows="2" placeholder="Ej: Aprobado para su resguardo definitivo."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" form="aprobarArchivoForm">Aprobar y Enviar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= MODAL ARCHIVAR DEFINITIVO (ARCHIVISTA) ================= -->
    <div class="modal fade" id="archivarDefinitivoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-dark">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-archive-fill"></i> Archivar Definitivamente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="archivarDefinitivoForm" action="correspondencia/archive.php" method="post">
                        <input type="hidden" id="archivar_definitivo_id" name="id">
                        <input type="hidden" name="tipo_archivo" value="central">
                        <p>¿Confirma que este documento ya fue resguardado en el edificio del Archivo Central?</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ubicación Física <span class="text-danger">*</span></label>
                            <textarea class="form-control border-dark" name="nota_archivo" rows="2" placeholder="Ej: Estante A, Caja 2, Folder de Mantenimiento..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" form="archivarDefinitivoForm">Guardar en Archivo Central</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL CONFORMAR COMISIÓN (TRABAJO EN GRUPO) ================= -->
    <div class="modal fade" id="conformarComisionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-people-fill"></i> Conformar Grupo de Trabajo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="conformarComisionForm" action="grupo/store.php" method="post">
                        <input type="hidden" id="comision_id_correspondencia" name="id_correspondencia">
                        
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle"></i> Primero seleccione a los integrantes y luego asigne a uno de ellos como el Responsable (Líder).
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Integrantes del Grupo:</label>
                            <select id="comision_integrantes" name="participantes_ids[]" class="form-select border-4" multiple="multiple" required style="width: 100%;">
                                <?php foreach($funcionarios as $f): ?>
                                <option value="<?= htmlspecialchars($f['id']) ?>" data-nombre="<?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?> - <?= htmlspecialchars($f['sigla'] ?? '') ?>">
                                    <?= htmlspecialchars(trim($f['nombre'] . ' ' . ($f['paterno'] ?? '') . ' ' . ($f['materno'] ?? ''))) ?> - <?= htmlspecialchars($f['sigla'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Responsable del Grupo (Líder):</label>
                            <select id="comision_responsable" name="responsable_id" class="form-select border-4" required disabled style="width: 100%;">
                                <option value="">-- Primero seleccione integrantes --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha Límite para Informes:</label>
                            <input type="datetime-local" class="form-control border-4" id="fecha_limite_grupo" name="fecha_limite" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold" form="conformarComisionForm"><i class="bi bi-diagram-3"></i> Conformar Grupo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL SUBIR INFORME DE GRUPO ================= -->
    <div class="modal fade" id="subirInformeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cloud-upload"></i> Subir Informe al Grupo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="subirInformeForm" action="grupo/upload_report.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="informe_id_correspondencia" name="id_correspondencia">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contenido / Conclusión:</label>
                            <textarea class="form-control border-4" name="contenido" rows="3" required placeholder="Redacte un resumen, conclusión o contenido de su informe..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Archivo Adjunto (PDF o Imagen):</label>
                            <input type="file" class="form-control border-4" name="archivo_informe" accept="image/*,.pdf" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="subirInformeForm"><i class="bi bi-send"></i> Enviar Informe</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL VER CONTRASEÑA (FUNCIONARIO) ================= -->
    <div class="modal fade" id="verContraseniaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-info">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title"><i class="bi bi-eye"></i> Credenciales de Acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="mb-3 text-center">
                        <label class="form-label text-muted fw-bold">Nombre de Usuario:</label>
                        <input type="text" class="form-control border-info text-center fs-5 mb-3" id="usuario_actual" readonly>
                        
                        <label class="form-label text-muted fw-bold">Contraseña:</label>
                        <input type="text" class="form-control border-info text-center fs-4 fw-bold" id="contrasenia_actual" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL NUEVO FUNCIONARIO ================= -->
    <div class="modal fade" id="createFuncionarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nuevo Funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createFuncionarioForm" action="funcionario/store.php" method="post">

                        <div class="mb-3">
                            <label class="form-label">Carnet Identidad</label>
                            <input type="number" class="form-control" name="ci" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" name="paterno">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" name="materno">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol" style="width: 100%;">
                                <option>Administrador</option>
                                <option>Gerente</option>
                                <option>Administrativo</option>
                                <option>Secretaria</option>
                                <option>Archivista Central</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Puesto</label>
                            <select class="form-select" name="id_puesto" style="width: 100%;">
                                <?php foreach ($puestos as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['descripcion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" form="createFuncionarioForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL EDITAR FUNCIONARIO ================= -->
    <div class="modal fade" id="editFuncionarioModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Funcionario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editFuncionarioForm" action="funcionario/update.php" method="post">
                        <input type="hidden" id="edit_id" name="id">

                        <div class="mb-3">
                            <label class="form-label">Carnet Identidad</label>
                            <input type="number" class="form-control" id="edit_ci" name="ci" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="edit_paterno" name="paterno">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="edit_materno" name="materno">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="edit_rol" name="rol" style="width: 100%;">
                                <option>Administrador</option>
                                <option>Gerente</option>
                                <option>Administrativo</option>
                                <option>Secretaria</option>
                                <option>Archivista Central</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Puesto</label>
                            <select class="form-select" id="edit_id_puesto" name="id_puesto" style="width: 100%;">
                                <?php foreach ($puestos as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['descripcion']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark" form="editFuncionarioForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL ELIMINAR FUNCIONARIO ================= -->
    <div class="modal fade" id="deleteFuncionarioModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-person-x"></i> Dar de Baja Funcionario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea dar de baja a este funcionario?</p>
                    <form id="deleteFuncionarioForm" action="funcionario/destroy.php" method="post">
                        <input type="hidden" id="delete_funcionario_id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="deleteFuncionarioForm">Dar de Baja</button>
                </div>
            </div>
        </div>
    </div>