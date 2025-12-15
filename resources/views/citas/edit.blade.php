@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Editar Cita</div>
                <div class="card-body">
                    <form action="{{ route('citas.update', $cita) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Fecha y Hora</label>
                            <input type="datetime-local" name="fecha_hora" class="form-control" value="{{ old('fecha_hora', optional($cita->fecha_hora)->format('Y-m-d\TH:i')) }}">
                            @error('fecha_hora') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre del cliente</label>
                            <input type="text" name="cliente_nombre" class="form-control" value="{{ old('cliente_nombre', $cita->cliente_nombre) }}">
                            @error('cliente_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="cliente_telefono" class="form-control" value="{{ old('cliente_telefono', $cita->cliente_telefono) }}">
                            @error('cliente_telefono') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de la mascota</label>
                            <input type="text" name="mascota_nombre" class="form-control" value="{{ old('mascota_nombre', $cita->mascota_nombre) }}">
                            @error('mascota_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especie</label>
                            <input type="text" name="mascota_especie" class="form-control" value="{{ old('mascota_especie', $cita->mascota_especie) }}">
                            @error('mascota_especie') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control">{{ old('motivo', $cita->motivo) }}</textarea>
                            @error('motivo') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-control">
                                @php $estados = ['pendiente','confirmada','completada','cancelada']; @endphp
                                @foreach($estados as $estado)
                                    <option value="{{ $estado }}" {{ (old('estado', $cita->estado) == $estado) ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                                @endforeach
                            </select>
                            @error('estado') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio (opcional)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio', $cita->precio) }}">
                            @error('precio') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control">{{ old('notas', $cita->notas) }}</textarea>
                            @error('notas') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('citas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
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
