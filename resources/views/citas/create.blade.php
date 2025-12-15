@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Crear Cita</div>
                <div class="card-body">
                    <form action="{{ route('citas.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Fecha y Hora</label>
                            <input type="datetime-local" name="fecha_hora" class="form-control" value="{{ old('fecha_hora') }}">
                            @error('fecha_hora') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre del cliente</label>
                            <input type="text" name="cliente_nombre" class="form-control" value="{{ old('cliente_nombre') }}">
                            @error('cliente_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="cliente_telefono" class="form-control" value="{{ old('cliente_telefono') }}">
                            @error('cliente_telefono') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        @php $hasMascotas = isset($mascotas) && $mascotas->isNotEmpty(); @endphp

                        @if($hasMascotas)
                        <div class="mb-3">
                            <label class="form-label">Seleccionar mascota (opcional)</label>
                            <select name="mascota_id" id="mascotaSelect" class="form-select">
                                <option value="">-- Otra mascota --</option>
                                @foreach($mascotas as $m)
                                    <option value="{{ $m->id }}" {{ (old('mascota_id', $selected_mascota_id ?? '') == $m->id) ? 'selected' : '' }}>{{ $m->nombre }} ({{ $m->especie }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div id="manualFields">
                          <div class="mb-3">
                            <label class="form-label">Nombre de la mascota</label>
                            <input type="text" name="mascota_nombre" class="form-control" value="{{ old('mascota_nombre') }}">
                            @error('mascota_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Especie</label>
                            <input type="text" name="mascota_especie" class="form-control" value="{{ old('mascota_especie') }}">
                            @error('mascota_especie') <div class="text-danger">{{ $message }}</div> @enderror
                          </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control">{{ old('motivo') }}</textarea>
                            @error('motivo') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio (opcional)</label>
                            <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio') }}">
                            @error('precio') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('citas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button class="btn btn-primary">Guardar</button>
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
    const item = document.getElementById('itemPedirCita');
    if(item) item.classList.add('active');

    const select = document.getElementById('mascotaSelect');
    const manual = document.getElementById('manualFields');
    if(select && manual){
        const toggle = () => { manual.style.display = (select.value ? 'none' : '') };
        toggle();
        select.addEventListener('change', toggle);
    }
});
</script>
@endpush