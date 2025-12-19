@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h3 class="mb-0">Compras</h3>
                    @can('compras_create')
                    <a href="{{ route('compras.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Nueva Compra
                    </a>
                    @endcan
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(isset($compras) && $compras->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Proveedor</th>
                                        <th>Tipo Comprobante</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($compras as $compra)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                                            <td>{{ optional($compra->proveedor)->razon_social ?? '-' }}</td>
                                            <td>{{ optional($compra->comprobanteTipo)->descripcion ?? '-' }}</td>
                                            <td>S/. {{ number_format($compra->total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($compra->estado) }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-sm btn-info">Ver</a>
                                                @can('compras_edit')
                                                <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                                @endcan
                                                @can('compras_delete')
                                                <form action="{{ route('compras.destroy', $compra->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?');">Eliminar</button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $compras->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">No hay compras registradas</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection