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
                    <h3 class="card-title flex-grow-1">Roles</h3>
                    @can('roles_permisos_create')
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
                                    <th>Nombre</th>
                                    <th>Permisos</th>
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
@canany(['roles_permisos_create', 'roles_permisos_edit'])
    @include('roles.action')
@endcanany
@endsection
@push('scripts')
<script>
class RoleManager extends CrudManager {
    constructor() {
        super("{{ url('roles') }}");
        this.initializeDataTable();
        this.loadPermissions();
    }

    loadPermissions(marcados = []) {
        fetch('{{ route("permisos.select") }}')
            .then(response => response.json())
            .then(permisos => {
                const container = document.getElementById('checkbox-permisos');
                container.innerHTML = '';
                permisos.forEach(p => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-1';

                    const div = document.createElement('div');
                    div.className = 'form-check';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input';
                    checkbox.name = 'permissions[]';
                    checkbox.value = p.name;
                    checkbox.id = `perm_${p.id}`;
                    if (marcados.includes(p.name)) checkbox.checked = true;

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.htmlFor = `perm_${p.id}`;
                    label.textContent = p.name;

                    div.appendChild(checkbox);
                    div.appendChild(label);
                    col.appendChild(div);
                    container.appendChild(col);
                });
            })
            .catch(error => {
                console.error('Error al cargar permisos:', error);
                document.getElementById('permissions-error').textContent = 'No se pudieron cargar los permisos.';
            });
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
                { data: 'name', name: 'name'},
                { data: 'permissions', name: 'permissions' }
            ],
            columnDefs: [
                { targets: 0, width: '15%', className: 'text-center' },
                { targets: 1, width: '15%' },
                { targets: 2, width: '70%' }
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
            document.getElementById('name').value = response.name || '';
            // Llamar a loadPermissions con permisos marcados
            const permisosMarcados = (response.permissions || []).map(p => p.name);
            this.loadPermissions(permisosMarcados);

            this.form.action = `${this.baseUrl}/${id}`;
            
            this.modal.show();
            
        } catch (error) {
            this.showNotification('error', 'Error al cargar los datos');
            console.error('Error al cargar datos:', error);
        }
    }
    focusFirstField() {
        document.getElementById('name').focus();
    }
}
document.addEventListener('DOMContentLoaded', () => {
    new RoleManager();
});
document.getElementById('mnuSeguridad').classList.add('menu-open');
document.getElementById('itemRoles').classList.add('active');
</script>
@endpush