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
                    <h3 class="card-title flex-grow-1">Productos</h3>
                    @can('productos_create')
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
                                    <th>Unidad</th>
                                    <th>Afectación Tipo</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Stock</th>
                                    <th>Precio Unitario</th>
                                    <th>Imagen</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    @can('productos_create')
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        @endif
                        <form action="{{ route('productos.importar') }}" method="POST" enctype="multipart/form-data" 
                            class="d-flex align-items-center gap-2">
                            @csrf
                            <input type="file" name="archivo" class="form-control form-control-sm" style="max-width: 250px;" required>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-upload"></i> Importar
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!--end::Row-->
</div>
@canany(['productos_create', 'productos_edit'])
    @include('productos.action')
@endcan
@endsection
@push('scripts')
<script>
class ProductoManager extends CrudManager {
    constructor() {
        super("{{ url('productos') }}");
        this.initializeDataTable();        

        this.populateSelect('unidad_codigo', '{{ route("unidades.select") }}', item =>
            `<option value="${item.codigo}">${item.codigo} - ${item.descripcion}</option>`
        );

        this.populateSelect('afectacion_tipo_codigo', '{{ route("afectacion-tipos.select") }}', item =>
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
                { data: 'unidad_codigo', name: 'unidad_codigo'},
                { data: 'afectacion_tipo_codigo', name: 'afectacion_tipo_codigo'},
                { data: 'codigo', name: 'codigo'},
                { data: 'nombre', name: 'nombre'},
                { data: 'stock', name: 'stock'},
                { data: 'precio_unitario', name: 'precio_unitario' },
                { data: 'imagen', name: 'imagen'}
            ],
            columnDefs: [
                { targets: 0, width: '15%', className: 'text-center' },
                { targets: 1, width: '10%' },
                { targets: 2, width: '10%' },
                { targets: 3, width: '10%' },
                { targets: 4, width: '20%' },
                { targets: 5, width: '15%' },
                { targets: 6, width: '15%' },
                { targets: 7, width: '20%' }
            ],
            responsive: true
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
            document.getElementById('unidad_codigo').value = response.unidad_codigo;
            document.getElementById('afectacion_tipo_codigo').value = response.afectacion_tipo_codigo;
            document.getElementById('codigo').value = response.codigo || '';
            document.getElementById('nombre').value = response.nombre || '';
            document.getElementById('descripcion').value = response.descripcion || '';
            document.getElementById('precio_unitario').value = response.precio_unitario;
            document.getElementById('stock').value = response.stock;

            if (response.imagen && response.imagen !== "") {
                document.getElementById('imagen_producto').src = "{{ asset('uploads/productos') }}/" + response.imagen;
                document.getElementById('imagen_producto').style.display = 'block';
            } else {
                document.getElementById('imagen_producto').style.display = 'none';
            }

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
    showCreateModal(){
        super.showCreateModal();
        document.getElementById('unidad_codigo').value = 'NIU';
        document.getElementById('afectacion_tipo_codigo').value = '10';
        document.getElementById('imagen_producto').style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    new ProductoManager();
});
document.getElementById('mnuAlmacen').classList.add('menu-open');
document.getElementById('itemProductos').classList.add('active');
</script>
@endpush