@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalle de Cita</h5>
                    <span class="badge bg-light text-dark">{{ ucfirst($cita->estado ?? 'pendiente') }}</span>
                </div>
                <div class="card-body">
                    <dl class="row">
                        {{-- Fecha y Hora --}}
                        <dt class="col-sm-4 fw-bold">Fecha y Hora</dt>
                        <dd class="col-sm-8">
                            @if($cita->fecha_hora)
                                <i class="bi bi-calendar-event"></i>
                                {{ optional($cita->fecha_hora)->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>

                        {{-- Cliente --}}
                        <dt class="col-sm-4 fw-bold">Cliente</dt>
                        <dd class="col-sm-8">{{ $cita->cliente_nombre }}</dd>

                        {{-- Teléfono --}}
                        <dt class="col-sm-4 fw-bold">Teléfono</dt>
                        <dd class="col-sm-8">
                            @if($cita->cliente_telefono)
                                <a href="tel:{{ $cita->cliente_telefono }}" class="text-decoration-none">
                                    {{ $cita->cliente_telefono }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>

                        {{-- Mascota --}}
                        <dt class="col-sm-4 fw-bold">Mascota</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $cita->mascota_nombre }}</strong>
                            @if($cita->mascota->exists ?? false)
                                <a href="{{ route('mascotas.show', $cita->mascota) }}" class="ms-2" title="Ver perfil completo">
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            @endif
                        </dd>

                        {{-- Especie --}}
                        <dt class="col-sm-4 fw-bold">Especie</dt>
                        <dd class="col-sm-8">{{ $cita->mascota_especie }}</dd>

                        {{-- Veterinario --}}
                        <dt class="col-sm-4 fw-bold">Veterinario</dt>
                        <dd class="col-sm-8">
                            @if($cita->veterinario)
                                <strong>{{ $cita->veterinario->name }}</strong>
                            @else
                                <span class="text-muted">Sin asignar</span>
                            @endif
                        </dd>

                        {{-- Motivo --}}
                        <dt class="col-sm-4 fw-bold">Motivo</dt>
                        <dd class="col-sm-8">
                            <p class="mb-0">{{ $cita->motivo }}</p>
                        </dd>

                        {{-- Estado --}}
                        <dt class="col-sm-4 fw-bold">Estado</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-info">{{ ucfirst($cita->estado ?? 'pendiente') }}</span>
                        </dd>

                        {{-- Precio --}}
                        <dt class="col-sm-4 fw-bold">Precio</dt>
                        <dd class="col-sm-8">
                            @if($cita->precio)
                                <strong class="text-success">S/. {{ number_format($cita->precio, 2) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>

                        {{-- Notas --}}
                        @if($cita->notas)
                        <dt class="col-sm-4 fw-bold">Notas</dt>
                        <dd class="col-sm-8">
                            <div class="alert alert-light border" role="alert">
                                {{ $cita->notas }}
                            </div>
                        </dd>
                        @endif
                    </dl>

                    {{-- Action Buttons --}}
                    <div class="d-flex justify-content-between gap-2 mt-4">
                        <a href="{{ route('citas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <div>
                            <a href="{{ route('citas.edit', $cita) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <form action="{{ route('citas.destroy', $cita) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta cita?');">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

    if(item) item.classList.add('active');
});
</script>
@endpush
