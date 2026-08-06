<div class="modal fade" id="modal-confirm-insert" tabindex="-1" aria-labelledby="modalConfirmInsertLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmInsertLabel">Confirmar inserción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $table->question }}[1]
                <br>
                <div style="overflow-y: auto; max-height: 300px;" class="mt-3">
                    <table id="preview_response_data" class="table table-bordered" style="text-align: center; margin: 0 auto;"></table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-insert">Confirmar</button>
            </div>
        </div>
    </div>
</div>
