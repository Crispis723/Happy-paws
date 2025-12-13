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
                    <h3 class="card-title flex-grow-1">Reporte de Ventas</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form method="GET" action="{{ route('reportes.ventas') }}" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">Usuario</label>
                            <select id="user_id" name="user_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100"> <i class="bi bi-funnel-fill"></i> Filtrar </button>
                            <a href="{{ route('reportes.ventas.exportar', request()->query()) }}" class="btn btn-success w-100">
                                <i class="bi bi-file-earmark-excel-fill"></i> Exportar
                            </a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Opciones</th>
                                    <th>Fecha</th>
                                    <th>Comprobante</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>                                    
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ventas as $v)
                                    <tr>
                                        <td>
                                            <a href="{{route('ventas.imprimir', $v->id)}}" 
                                                target="_blank" 
                                                class="btn btn-sm btn-secondary" 
                                                title="Ver Comprobante">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <button class="btn btn-sm btn-info ms-2" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#detalles-{{ $v->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                        <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $v->comprobanteTipo->descripcion}} - {{$v->serie}} - {{str_pad($v->correlativo,8,'0',STR_PAD_LEFT)}}</td>
                                        <td>{{ $v->cliente->razon_social ?? 'N/A' }}</td>
                                        <td>{{ $v->user->name ?? 'N/A' }}</td>
                                        <td>S/ {{ number_format($v->total, 2) }}</td>
                                    </tr>
                                    <tr class="collapse" id="detalles-{{ $v->id }}">
                                        <td colspan="5">
                                            <div class="p-2">
                                                <h6>Detalles de productos</h6>
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Producto</th>
                                                            <th>Cantidad</th>
                                                            <th>Precio Unitario</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($v->detalles as $detalle)
                                                            <tr>
                                                                <td>{{ $detalle->producto->nombre ?? 'N/A' }}</td>
                                                                <td>{{ $detalle->cantidad }}</td>
                                                                <td>S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                                                <td>S/ {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay ventas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $ventas->appends(request()->all())->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!--end::Row-->
</div>
@endsection
@push('scripts')
<script>
document.getElementById('mnuReporte').classList.add('menu-open');
document.getElementById('itemReporteVentas').classList.add('active');
</script>
@endpush