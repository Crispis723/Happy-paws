@extends('plantilla.app')
@push('estilos')

@endpush
@section('contenido')
<div class="container-fluid">
    <!--begin::Row-->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title flex-grow-1">Tipos de Afectación</h3>
                    @can('afectacion_tipos_create')
                    <button type="button" class="btn btn-primary" id="btnCreate">
                        <i class="bi bi-plus-circle"></i> Nuevo
                    </button>
                    @endcan
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="listadoTable" class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Opciones</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>letra</th>
                                    <th>porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!--end::Row-->
</div>
@canany(['afectacion_tipos_create', 'afectacion_tipos_edit'])
    @include('afectacion-tipos.action')
@endcanany
@endsection
@push('scripts')
<script>
class AfectacionManager extends CrudManager {
    constructor() {
        super("{{ url('afectacion-tipos') }}");
        this.initializeDataTable();
    }

    initializeDataTable() {
        this.tabla = $(this.elements.table).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: this.baseUrl,
                type: 'GET'
            },
           columns: [
                { data: 'action', name: 'action', orderable: false, searchable: false},
                { data: 'codigo', name: 'codigo'},
                { data: 'nombre', name: 'nombre'},
                { data: 'descripcion', name: 'descripcion' },
                { data: 'letra', name: 'letra'},
                { data: 'porcentaje', name: 'porcentaje'},
            ],
            columnDefs: [
                { targets: 0, width: '15%', className: 'text-center' },
                { targets: 1, width: '15%' },
                { targets: 2, width: '15%' },
                { targets: 3, width: '40%' },
                { targets: 4, width: '15%' },
            ],
            responsive: true,
            order: [[1, 'asc']]
        });
    }

    async showEditModal(id) {
        try {
            const response = await this.fetchData(`${this.baseUrl}/${id}`);
            
            this.isEditing = true;
            this.resetForm();
            
            this.elements.modalTitle.textContent = 'Editar registro';
            this.elements.methodField.value = 'PUT';
            
            // Llenar campos específicos
            document.getElementById('codigo').value = response.codigo || '';
            document.getElementById('nombre').value = response.nombre || '';
            document.getElementById('descripcion').value = response.descripcion || '';
            document.getElementById('letra').value = response.letra || '';
            document.getElementById('porcentaje').value = response.porcentaje || '';

            this.form.action = `${this.baseUrl}/${id}`;
            
            this.modal.show();
            
        } catch (error) {
            this.showNotification('error', 'Error al cargar los datos');
            console.error('Error al cargar datos:', error);
        }
    }
    focusFirstField() {
        document.getElementById('codigo').focus();
    }
}
document.addEventListener('DOMContentLoaded', () => {
    new AfectacionManager();
});
document.getElementById('mnuConfiguracion').classList.add('menu-open');
document.getElementById('itemAfectacionTipo').classList.add('active');
</script>
@endpush