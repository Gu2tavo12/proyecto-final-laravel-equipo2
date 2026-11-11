{{-- [ACCESIBILIDAD] Modal comun de confirmacion para eliminaciones (DELETE) accesible por teclado y con opcion de cancelar --}}
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarTitulo" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fs-5" id="modalConfirmarEliminarTitulo">
                    <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>Confirmar eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4">
                <p id="modalConfirmarEliminarMensaje" class="mb-0 text-secondary">
                    ¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btnCancelarModalEliminar">
                    <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarAccionEliminar">
                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>
