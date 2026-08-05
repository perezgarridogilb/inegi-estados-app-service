<!--begin::Content wrapper-->
<x-base-layout>
    {{-- Styles Section --}}
    @push('styles')
        <link href="{{ mix('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
    @endpush
    {{-- Content --}}
    <input type="hidden" value="{{ @$table->permissions->update }}" readonly="true" id="kt_datatable_update">
    <input type="hidden" value="{{ @$table->permissions->delete }}" readonly="true" id="kt_datatable_delete">
    <input type="hidden" value="{{ @$table->permissions->authorize }}" readonly="true" id="kt_datatable_authorize">
    <input type="hidden" value="{{ @$table->permissions->process }}" readonly="true" id="kt_datatable_process">
    <input type="hidden" value="{{ @$table->model }}" readonly="true" id="kt_datatable_model">

    <div class="card card-dashed">
        <div class="card-header">
            <h3 class="card-title">{{ (@$toolbar) ? $toolbar->title : @$page_title }}</h3>
            <div class="card-toolbar">
                @if(@$table->export)
                    <button type="button" class="btn btn-icon btn-light-success me-2" id="btn-datatable_export"
                            data-url="{{ $table->export }}" data-bs-toggle="tooltip"
                            data-bs-custom-class="tooltip-inverse" data-bs-placement="top" title="Exportar">
                        <i class="fa-solid fa-file-excel"></i>
                    </button>
                @endif
                @if(@$table->button)
                    <a href="{{ $table->button->route }}" class="btn btn-icon btn-light-info me-2"
                       data-bs-toggle="tooltip" data-bs-custom-class="tooltip-inverse" data-bs-placement="top"
                       title="{{ @$table->button->title }}">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                @endif
                @if(@$modal)
                    <div data-bs-toggle="tooltip" data-bs-custom-class="tooltip-inverse" data-bs-placement="top"
                         title="{{ @$modal->title }}">
                        <button type="button" class="btn btn-icon btn-light-primary" data-bs-toggle="modal"
                                data-bs-target="{{ $modal->target }}">
                            <i class="fa-solid fa-circle-dot"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            <!--begin::Wrapper-->
            <div class="row">
                 @if (isset($table->filters->product_codes))
                    <div class="col-md-1">
                        <input type="text" id="kt_datatable_to_search_code" class="form-control" placeholder="Código..."/>
                    </div>
                 @endif
                <!--begin::Search-->
                <div class="col-md-3">
                    <input type="text" id="kt_datatable_search" class="form-control" placeholder="Buscar..."/>
                </div>
                <!--end::Search-->
                <!--begin::Filters-->
                @if(isset($table->filters))
                    @if(isset($table->filters->accounts))
                        <div class="col-md-3">
                            <select class="form-select" data-control="select2" data-placeholder="Cuenta"
                                    id="kt_datatable_search_account">
                                <option></option>
                                <option value="0">Todas</option>
                                @foreach($table->filters->accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->statuses))
                        <div class="col-md-2">
                            <select class="form-select" data-control="select2" data-placeholder="Estatus"
                                    id="kt_datatable_search_status" data-allow-clear="true">
                                <option></option>
                                <option value="0">Todos</option>
                                @foreach($table->filters->statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->priorities))
                        <div class="col-md-1">
                            <select class="form-select" data-control="select2" data-placeholder="Prioridad"
                                    id="kt_datatable_search_priority">
                                <option></option>
                                <option value="0">Todos</option>
                                @foreach($table->filters->priorities as $priority)
                                    <option value="{{ $priority->id }}"
                                            data-content="<i class='{{ $priority->icon }}'></i>">{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->typeDepartures))
                        <div class="col-md-2">
                            <select class="form-select" data-control="select2" data-placeholder="Tipo de salida"
                                    id="kt_datatable_search_type_departure" data-allow-clear="true">
                                <option></option>
                                <option value="0">Todos</option>
                                @foreach($table->filters->typeDepartures as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->suppliers))
                        <div class="col-md-3">
                            <select class="form-select" data-control="select2" data-placeholder="Proveedor"
                                    id="kt_datatable_search_supplier">
                                <option></option>
                                <option value="0">Todos</option>
                                @foreach($table->filters->suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->clients))
                        <div class="col-md-3">
                            <select class="form-select" data-control="select2" data-placeholder="Cliente"
                                    id="kt_datatable_search_client">
                                <option></option>
                                <option value="0">Todos</option>
                                @foreach ($table->filters->clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->projects))
                        <div class="col-md-3">
                            <select class="form-select" data-control="select2" data-placeholder="Proyecto"
                                    id="kt_datatable_search_project">
                                <option></option>
                                <option value="0">Todos</option>
                                @foreach($table->filters->projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table->filters->warehouses) && $table->filters->warehouses->count() > 1)
                        <div class="col-md-3">
                            <select class="form-select" data-control="select2" multiple data-actions-box="true"
                                    data-placeholder="CC" id="kt_datatable_search_warehouses">
                                @foreach($table->filters->warehouses as $warehouse)
                                    <option
                                        value="{{ $warehouse->id }}" {{ ($warehouse->id == session()->get('activeWarehouse')->id) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if (isset($table->filters->production_statuses))
                        <div class="col-md-2">
                            <select class="form-select" data-control="select2" data-placeholder="Estatus"
                                    id="kt_datatable_search_production_statuses" data-allow-clear="true">
                                <option></option>
                                @foreach ($table->filters->production_statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <!--end: Filters-->
                @endif
            </div>
            <!--end::Wrapper-->
            <!--begin::Datatable-->
            <table id="{{ isset($table->name) ? $table->name : $table->js }}" data-url="{{ $table->list }}"
                   class="table align-middle table-row-dashed fs-6 gy-5 datatable">
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
            <!--end::Datatable-->
        </div>
        {{-- <div class="card-footer">
        </div> --}}
    </div>
    {{-- End::Content --}}
    {{-- Modal Section --}}
    @push('modals')
        @if(@$model)
            @component('pages.'.$model)@endcomponent
        @endif
        @if(@$modal)
            @include($modal->component)
        @endif
    @endpush
    {{-- Scripts Section --}}
    @push('scripts')
        <script src="{{ mix('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
        <script src="{{ mix('js/scripts/datatables/'.$table->js.'.js') }}" type="text/javascript"></script>
    @endpush
</x-base-layout>
