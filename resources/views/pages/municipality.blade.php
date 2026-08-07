<x-base-layout title="Estados">
    <input type="hidden" name="sync_save" id="sync_save" value="{{ @$routes->sync_save }}">
    <input type="hidden" name="sync" id="sync" value="{{ @$routes->sync }}">
    <input type="hidden" id="delete" value="{{ @$routes->delete }}">
    <input type="hidden" value="{{ @$state->id }}" name="kt_datatable_search_state" id="kt_datatable_search_state">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $table->title }}</h3>
            <div class="card-toolbar d-flex gap-3">
                <button type="button" class="btn btn-primary my-3" id="btn-sync-municipalities">
                    <span class="spinner-border spinner-border-sm d-none" id="spinner-sync" role="status"
                        aria-hidden="true"></span>
                    {{ $table->button }}
                </button>
                <button type="button" class="btn btn-danger my-3" id="btn-clear-states">
                    Limpiar base
                </button>
            </div>
        </div>
        <div class="card-body"> {{-- contenido, fondo blanco heredado --}}
            <table id="{{ isset($table->name) ? $table->name : $table->js }}" data-url="{{ $table->list }}"
                class="table align-middle table-row-dashed fs-6 gy-5 datatable">
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
        </div>
    </div>

    {{-- <div class="modal fade" id="modal-confirm-insert" tabindex="-1" aria-labelledby="modalConfirmInsertLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmInsertLabel">Confirmar inserción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Desea insertar los estados en el sistema?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-confirm-insert">Confirmar</button>
                </div>
            </div>
        </div>
    </div> --}}

    @push('modals')
        @include('modals.confirm-insert')
    @endpush

    @push('scripts')
        <script>
            window.municipalities = [];
        </script>
        <script src="{{ asset('js/factory.js') }}"></script>
        <script src="{{ asset("js/{$table->js}.js") }}"></script>
    @endpush
</x-base-layout>
