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
                    <h3 class="card-title flex-grow-1">Ventas</h3>
                    @can('ventas_create')
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
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                    <th>Forma de Pago</th>
                                    <th>Cliente</th>
                                    <th>Tipo de Comprobante</th>
                                    <th>Serie</th>
                                    <th>Correlativo</th>                           
                                    <th>Total</th>
                                    <th>Estado</th>
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
@canany(['ventas_create', 'ventas_edit'])
    @include('ventas.action')
@endcanany
@endsection
@push('scripts')
<script>
class VentaManager extends CrudManager {
    constructor() {
        super("{{ url('ventas') }}");
        this.afterSuccess = this.handleVentaSuccess.bind(this);
        this.initializeDataTable();

        this.populateSelect('documento_tipo_codigo', '{{ route("documento-tipos.select") }}', item =>
            `<option value="${item.codigo}">${item.codigo} - ${item.descripcion}</option>`
        );

        this.populateSelect('comprobante_tipo_codigo', '{{ route("comprobante-tipos.select") }}', item =>
            `<option value="${item.codigo}">${item.codigo} - ${item.descripcion}</option>`
        );

        this.setupLiveSearchSelect({
            inputId: 'producto_nombre',
            hiddenId: 'producto_id',
            url: "{{ route('productos.buscar') }}",
            template: (item) => {
                return `${item.codigo} - ${item.nombre} (S/ ${item.precio_unitario})`;
            },
            getId: item => item.id,
            onSelect: (item) => this.addProductoToTable(item)
        });

        this.setupLiveSearchSelect({
            inputId: 'cliente_razon_social',
            hiddenId: 'cliente_id',
            url: "{{ route('clientes.buscar') }}",
            template: (item) => {
                return `${item.numero_documento} - ${item.razon_social}`;
            }
        });

        document.getElementById('btnRegistrarCliente').addEventListener('click', () => this.registerClient());

        // Evento para el select de comprobante_tipo_codigo
        const selectComprobante = document.getElementById('comprobante_tipo_codigo');
        if (selectComprobante) {
            selectComprobante.addEventListener('change', (e) => {
                this.getSerie(e.target.value); // Ahora sí llama al método de la clase
            });
        }
    }

    async handleVentaSuccess(response, isEditing) {
        // Solo mostrar opción de ticket para ventas NUEVAS (no ediciones)
        if (!isEditing && response.venta_id) {
            setTimeout(() => {
                this.showTicketOption(response);
            }, 1000); // Esperar 1 segundo para que se vea la notificación de registro primero
        }
    }

    async showTicketOption(response){
        const result = await Swal.fire({
            title: '¡Venta registrada!',
            text: '¿Deseas imprimir el ticket?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Imprimir Ticket',
            cancelButtonText: 'Continuar',
            reverseButtons: true,
            timer: 8000,
            timerProgressBar: true
        });
        
        if (result.isConfirmed) {
            const imprimirRuta = "{{ route('ventas.imprimir', ['id' => ':id']) }}";
            window.open(imprimirRuta.replace(':id', response.venta_id), '_blank');
        }
    }

    initEvents() {
        const inputs = document.querySelectorAll('.inputCantidad');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.calculateTotals());
        });
    }

    addProductoToTable(item, cantidad = 1, precio_unitario = null, subtotal = null)
    {
        const tbody = document.querySelector('#tablaDetalles tbody');
        document.getElementById('producto_id').value = '';
        document.getElementById('producto_nombre').value = '';
        
        const existingRow = [...tbody.querySelectorAll('tr')].find(row => row.dataset.productoId == item.id);
        if (existingRow) {
            const inputCantidad = existingRow.querySelector('.inputCantidad');
            const nuevaCantidad = parseInt(inputCantidad.value) + cantidad;
            inputCantidad.value = nuevaCantidad;

            const precio = parseFloat(existingRow.querySelector('.precio-unitario').textContent);
            existingRow.querySelector('.subtotal').textContent = (precio * nuevaCantidad).toFixed(2);
        } else {
            const rowCount = tbody.rows.length + 1;
            const precioConImpuesto = parseFloat(precio_unitario ?? item.precio_unitario);
            const porcentaje = parseFloat(item.afectacion_tipo?.porcentaje || 0);
            const sub = subtotal ?? (precioConImpuesto * cantidad);
            const valorUnitario = precioConImpuesto / (1 + porcentaje);
            const impuesto = precioConImpuesto - valorUnitario;

            const tr = document.createElement('tr');
            tr.dataset.productoId = item.id;
            tr.dataset.afectacionPorcentaje = porcentaje;
            tr.dataset.afectacionCodigo = item.afectacion_tipo_codigo || '10';

            tr.innerHTML = `
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btnEliminarFila">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
                <td class="text-center">${rowCount}</td>
                <td>${item.codigo} - ${item.nombre}</td>
                <td>
                    <input type="number" name="detalles[${rowCount}][cantidad]" value="${cantidad}" min="1" class="form-control form-control-sm inputCantidad">
                </td>
                <td class="text-end precio-unitario">${precioConImpuesto.toFixed(2)}</td>
                <td class="text-end subtotal">${sub.toFixed(2)}</td>

                <!-- Solo estos inputs viajan al backend -->
                <input type="hidden" name="detalles[${rowCount}][producto_id]" value="${item.id}">
            `;

            tr.querySelector('.inputCantidad').addEventListener('change', () => this.calculateTotals());
            tr.querySelector('.btnEliminarFila').addEventListener('click', () => { tr.remove(); this.calculateTotals(); });
            tbody.appendChild(tr);
        }
        this.calculateTotals();
    }

    calculateTotals() {
        const tbody = document.querySelector('#tablaDetalles tbody');
        if (!tbody) return;

        let op_gravada = 0, op_exonerada = 0, op_inafecta = 0, totalImpuesto = 0;

        [...tbody.querySelectorAll('tr')].forEach(row => {
            const cantidad = parseFloat(row.querySelector('.inputCantidad').value) || 0;
            const precioUnitario = parseFloat(row.querySelector('.precio-unitario').textContent) || 0;
            const porcentaje = parseFloat(row.dataset.afectacionPorcentaje || 0);
            const afectacionCodigo = row.dataset.afectacionCodigo;

            const itemSubtotal = cantidad * precioUnitario;
            let baseSinImpuesto = itemSubtotal, impuesto = 0;

            if (afectacionCodigo === '10') { 
                baseSinImpuesto = itemSubtotal / (1 + porcentaje);
                impuesto = itemSubtotal - baseSinImpuesto;
                op_gravada += baseSinImpuesto;
                totalImpuesto += impuesto;
            } else if (afectacionCodigo === '20') op_exonerada += itemSubtotal;
            else if (afectacionCodigo === '30') op_inafecta += itemSubtotal;

            row.querySelector('.subtotal').textContent = itemSubtotal.toFixed(2);
        });

        const total = op_gravada + op_exonerada + op_inafecta + totalImpuesto;

        // Solo mostramos en el formulario (no viajan al backend)
        document.getElementById('op_gravada').value = op_gravada.toFixed(2);
        document.getElementById('op_exonerada').value = op_exonerada.toFixed(2);
        document.getElementById('op_inafecta').value = op_inafecta.toFixed(2);
        document.getElementById('impuesto').value = totalImpuesto.toFixed(2);
        document.getElementById('total').value = total.toFixed(2);
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
                { data: 'usuario', name: 'usuario' },
                { data: 'fecha', name: 'fecha' },
                { data: 'forma_pago', name: 'forma_pago' },
                { data: 'cliente', name: 'cliente' },
                { data: 'tipo_comprobante', name: 'tipo_comprobante' },
                { data: 'serie', name: 'serie' },
                { data: 'correlativo', name: 'correlativo' },
                { data: 'total', name: 'total' },
                { data: 'estado', name: 'estado' }
            ],
            columnDefs: [
                { targets: 0, width: '15%', className: 'text-center' }
            ],
            responsive: true
        });
    }

    showCreateModal(){
        super.showCreateModal();
        document.querySelector('#tablaDetalles tbody').innerHTML = '';
        document.getElementById('documento_tipo_codigo').value = '01';
        document.getElementById('comprobante_tipo_codigo').value = '01';
        this.getSerie('01');
    }

    async showEditModal(id) {
        try {
            const response = await this.fetchData(`${this.baseUrl}/${id}`);
            
            this.isEditing = true;
            this.resetForm();
            
            this.elements.modalTitle.textContent = 'Editar registro';
            this.elements.methodField.value = 'PUT';
            
            // Llenar campos específicos
            // Llenar campos principales del modal
            document.getElementById('forma_pago').value = response.forma_pago || '';
            document.getElementById('comprobante_tipo_codigo').value = response.comprobante_tipo_codigo || '';
            document.getElementById('serie').value = response.serie || '';
            document.getElementById('correlativo').value = response.correlativo || '';
            document.getElementById('cliente_id').value = response.cliente_id || '';
            document.getElementById('cliente_razon_social').value = response.cliente?.razon_social || '';

            // Llenar tabla de detalles (productos)
            this.updateDetailsTable(response.detalles);

            // Llenar totales
            document.getElementById('op_gravada').value = parseFloat(response.op_gravada).toFixed(2);
            document.getElementById('op_exonerada').value = parseFloat(response.op_exonerada).toFixed(2);
            document.getElementById('op_inafecta').value = parseFloat(response.op_inafecta).toFixed(2);
            document.getElementById('impuesto').value = parseFloat(response.impuesto).toFixed(2);
            document.getElementById('total').value = parseFloat(response.total).toFixed(2);

            this.form.action = `${this.baseUrl}/${id}`;
            
            this.modal.show();
            
        } catch (error) {
            this.showNotification('error', 'Error al cargar los datos');
            console.error('Error al cargar datos:', error);
        }
    }
    
    updateDetailsTable(detalles=[]){
        const tbody = document.querySelector('#tablaDetalles tbody');
        tbody.innerHTML = ''; 
        detalles.forEach(detalle => {
            this.addProductoToTable(detalle.producto, +detalle.cantidad, +detalle.precio_unitario, +detalle.subtotal);
        });
    }

    focusFirstField() {
        document.getElementById('producto_nombre').focus();
    }
    async registerClient() {
        // Recoge los datos del formulario
        const documento_tipo_codigo = document.getElementById('documento_tipo_codigo').value;
        const numero_documento = document.getElementById('numero_documento').value;
        const razon_social = document.getElementById('razon_social').value;

        if (!documento_tipo_codigo || !numero_documento || !razon_social) {
            this.showNotification('warning', 'Completa todos los campos obligatorios de cliente');
            return;
        }
        try {
            const url = "{{ route('clientes.store') }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    documento_tipo_codigo,
                    numero_documento,
                    razon_social
                })
            });

            const data = await response.json();

            if (response.ok && data.success && data.cliente) {
                // Asigna los datos al formulario principal
                document.getElementById('cliente_id').value = data.cliente.id;
                document.getElementById('cliente_razon_social').value = data.cliente.razon_social;

                // Cambia a la tab de "Buscar Cliente"
                new bootstrap.Tab(document.getElementById('nav-buscar-tab')).show();
                this.showNotification('success', 'Cliente registrado correctamente');
                document.getElementById('numero_documento').value = '';
                document.getElementById('razon_social').value = '';
            } 
            else if (response.status === 422) {
                this.handleFormErrors({ status: 422, data }); 
            } else {
                this.showNotification('error', data.message || 'Error al registrar cliente');
            }
        } catch (error) {
            this.showNotification('error', 'Error de red al registrar cliente');
            console.error(error);
        }
    }

    async getSerie(codigo) {
        if (!codigo) return;
        const url= "{{ route('ventas.get-serie') }}" + "?comprobante_tipo_codigo=" + codigo;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Si no hay datos, limpiar
                if (!data.serie || !data.numero) {
                    document.getElementById('serie').value = '';
                    document.getElementById('correlativo').value = '';
                    return;
                }
                // Actualizar inputs
                document.getElementById('serie').value = data.serie;
                document.getElementById('correlativo').value = data.numero;
            })
            .catch(error => console.error('Error al obtener la serie y correlativo:', error));
    }

}
document.addEventListener('DOMContentLoaded', () => {
    new VentaManager();
});
document.getElementById('mnuVenta').classList.add('menu-open');
document.getElementById('itemVentas').classList.add('active');
</script>
@endpush