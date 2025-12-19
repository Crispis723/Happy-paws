@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Editar Cita</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('citas.update', $cita) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- Fecha y Hora --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_hora" class="form-control @error('fecha_hora') is-invalid @enderror" value="{{ old('fecha_hora', optional($cita->fecha_hora)->format('Y-m-d\TH:i')) }}" required>
                            @error('fecha_hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Cliente --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Cliente <span class="text-danger">*</span></label>
                            <input type="text" name="cliente_nombre" class="form-control @error('cliente_nombre') is-invalid @enderror" value="{{ old('cliente_nombre', $cita->cliente_nombre) }}" required>
                            @error('cliente_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" name="cliente_telefono" class="form-control @error('cliente_telefono') is-invalid @enderror" value="{{ old('cliente_telefono', $cita->cliente_telefono) }}" required>
                            @error('cliente_telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Datos de Mascota --}}
                        <div class="border-top pt-3 mt-3">
                            <h6 class="mb-3">Información de la Mascota</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="mascota_nombre" class="form-control @error('mascota_nombre') is-invalid @enderror" value="{{ old('mascota_nombre', $cita->mascota_nombre) }}" required>
                                @error('mascota_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Especie <span class="text-danger">*</span></label>
                                <input type="text" name="mascota_especie" class="form-control @error('mascota_especie') is-invalid @enderror" value="{{ old('mascota_especie', $cita->mascota_especie) }}" required>
                                @error('mascota_especie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Motivo --}}
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">Motivo de la Consulta <span class="text-danger">*</span></label>
                            <textarea name="motivo" class="form-control @error('motivo') is-invalid @enderror" rows="3" required>{{ old('motivo', $cita->motivo) }}</textarea>
                            @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Estado <span class="text-danger">*</span></label>
                            <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                @php $estados = ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'completada' => 'Completada', 'cancelada' => 'Cancelada']; @endphp
                                @foreach($estados as $value => $label)
                                    <option value="{{ $value }}" {{ (old('estado', $cita->estado) == $value) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Precio --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Precio</label>
                            <input type="number" step="0.01" name="precio" class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $cita->precio) }}">
                            <small class="text-muted">Precio de la cita (opcional).</small>
                            @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Notas --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notas Internas</label>
                            <textarea name="notas" class="form-control @error('notas') is-invalid @enderror" rows="2">{{ old('notas', $cita->notas) }}</textarea>
                            <small class="text-muted">Observaciones o notas adicionales sobre la cita.</small>
                            @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-between gap-2 mt-4">
                            <a href="{{ route('citas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
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
