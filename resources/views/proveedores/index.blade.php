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
                    <h3 class="card-title flex-grow-1">Proveedores</h3>
                    @can('proveedores_create')
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
                                    <th>Tipo Documento</th>
                                    <th>Número Documento</th>
                                    <th>Razón Social</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
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
@canany(['proveedores_create', 'proveedores_edit'])
    @include('proveedores.action')
@endcanany
@endsection
@push('scripts')
<script>
class ProveedorManager extends CrudManager {
    constructor() {
        super("{{ url('proveedores') }}");
        this.initializeDataTable();
        this.populateSelect('documento_tipo_codigo', '{{ route("documento-tipos.select") }}', item =>
            `<option value="${item.codigo}">${item.codigo} - ${item.descripcion}</option>`
        );
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
                { data: 'documento_tipo_codigo', name: 'documento_tipo_codigo'},
                { data: 'numero_documento', name: 'numero_documento' },
                { data: 'razon_social', name: 'razon_social' },
                { data: 'direccion', name: 'direccion' },
                { data: 'telefono', name: 'telefono' },
                { data: 'email', name: 'email' }
            ],
            columnDefs: [
                { targets: 0, width: '15%', className: 'text-center' },
                { targets: 1, width: '15%' },
                { targets: 2, width: '15%' },
                { targets: 3, width: '15%' },
                { targets: 4, width: '20%' },
                { targets: 5, width: '10%' },
                { targets: 6, width: '10%' }
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
            document.getElementById('documento_tipo_codigo').value = response.documento_tipo_codigo;
            document.getElementById('numero_documento').value = response.numero_documento || '';
            document.getElementById('razon_social').value = response.razon_social || '';
            document.getElementById('direccion').value = response.direccion || '';
            document.getElementById('telefono').value = response.telefono || '';
            document.getElementById('email').value = response.email || '';

            this.form.action = `${this.baseUrl}/${id}`;
            
            this.modal.show();
            
        } catch (error) {
            this.showNotification('error', 'Error al cargar los datos');
            console.error('Error al cargar datos:', error);
        }
    }
    focusFirstField() {
        document.getElementById('razon_social').focus();
    }
    showCreateModal(){
        super.showCreateModal();
        document.getElementById('documento_tipo_codigo').value = '01';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    new ProveedorManager();
});
document.getElementById('mnuAlmacen').classList.add('menu-open');
document.getElementById('itemProveedores').classList.add('active');
</script>
@endpush