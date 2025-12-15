@extends('plantilla.app')
@push('estilos')

@endpush

@php use Illuminate\Support\Str; @endphp
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title flex-grow-1">Citas</h3>
                    <a href="{{ route('citas.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha / Hora</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Mascota</th>
                                    <th>Especie</th>
                                    <th>Veterinario</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th>Precio</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($citas as $cita)
                                <tr>
                                    <td>{{ optional($cita->fecha_hora)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $cita->cliente_nombre }}</td>
                                    <td>{{ $cita->cliente_telefono }}</td>
                                    <td>{{ $cita->mascota_nombre }}</td>
                                    <td>{{ $cita->mascota_especie }}</td>
                                    <td>{{ optional($cita->veterinario)->name ?? '-' }}</td>
                                    <td>{{ Str::limit($cita->motivo, 50) }}</td>
                                    <td>{{ ucfirst($cita->estado ?? 'pendiente') }}</td>
                                    <td>{{ $cita->precio ? number_format($cita->precio,2) : '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('citas.show', $cita) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                        <a href="{{ route('citas.edit', $cita) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="{{ route('citas.destroy', $cita) }}" method="POST" style="display:inline-block" onsubmit="return confirm('¿Eliminar cita?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No hay citas registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    {{ $citas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const mnuVenta = document.getElementById('mnuVenta');
    if(mnuVenta) mnuVenta.classList.add('menu-open');
    const item = document.getElementById('itemCitasIndex');
    if(item) item.classList.add('active');
});
</script>
@endpush
