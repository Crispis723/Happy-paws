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
                            <input type="datetime-local" name="fecha_hora" class="form-control" value="{{ old('fecha_hora') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required autofocus aria-describedby="help-fecha-hora">
                            <small id="help-fecha-hora" class="text-muted">Selecciona la fecha y hora de la cita. No puede ser en el pasado.</small>
                            @error('fecha_hora') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        @guest
                        <div class="mb-3">
                            <label class="form-label">Nombre del cliente</label>
                            <input type="text" name="cliente_nombre" class="form-control" value="{{ old('cliente_nombre') }}" placeholder="Ej. Juan Pérez" required>
                            @error('cliente_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="cliente_telefono" class="form-control" value="{{ old('cliente_telefono') }}" placeholder="Ej. 999999999" pattern="^[0-9\s\-\+]{6,}$" aria-describedby="help-telefono">
                            <small id="help-telefono" class="text-muted">Solo números, espacios, guiones o +. Mínimo 6 caracteres.</small>
                            @error('cliente_telefono') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <div class="form-control-plaintext">{{ auth()->user()->name }} @if(auth()->user()->telefono) - {{ auth()->user()->telefono }} @endif</div>
                            <input type="hidden" name="cliente_nombre" value="{{ auth()->user()->name }}">
                            <input type="hidden" name="cliente_telefono" value="{{ auth()->user()->telefono ?? '' }}">
                        </div>
                        @endguest

                        @php $hasMascotas = isset($mascotas) && $mascotas->isNotEmpty(); @endphp

                        @php $veterinarios = $veterinarios ?? collect(); @endphp

                        <div class="mb-3">
                            <label class="form-label">Veterinario (médico que atiende)</label>
                            <select name="veterinario_id" class="form-select" required aria-describedby="help-vet">
                                <option value="">-- Seleccionar veterinario --</option>
                                @foreach($veterinarios as $vet)
                                    <option value="{{ $vet->id }}" {{ old('veterinario_id') == $vet->id ? 'selected' : '' }}>{{ $vet->name }}</option>
                                @endforeach
                            </select>
                            <small id="help-vet" class="text-muted">Elige el profesional que atenderá la cita.</small>
                            @error('veterinario_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        @if($hasMascotas)
                        <div class="mb-3">
                            <label class="form-label">Seleccionar mascota (opcional)</label>
                            <select name="mascota_id" id="mascotaSelect" class="form-select" aria-describedby="help-mascota">
                                <option value="">-- Otra mascota --</option>
                                @foreach($mascotas as $m)
                                    <option value="{{ $m->id }}" {{ (old('mascota_id', $selected_mascota_id ?? '') == $m->id) ? 'selected' : '' }}>{{ $m->nombre }} ({{ $m->especie }})</option>
                                @endforeach
                            </select>
                            <small id="help-mascota" class="text-muted">Si no aparece aquí, completa los datos manualmente abajo.</small>
                        </div>
                        @endif

                        <div id="manualFields">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nombre de la mascota</label>
                                                        <input type="text" name="mascota_nombre" class="form-control" value="{{ old('mascota_nombre') }}" placeholder="Ej. Firulais" aria-describedby="help-mascota-nombre">
                                                        <small id="help-mascota-nombre" class="text-muted">Ingresa el nombre si no está registrada previamente.</small>
                                                        @error('mascota_nombre') <div class="text-danger">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Especie</label>
                                                        <input type="text" name="mascota_especie" class="form-control" value="{{ old('mascota_especie') }}" placeholder="Ej. Perro, Gato" aria-describedby="help-mascota-especie">
                                                        <small id="help-mascota-especie" class="text-muted">Especifica la especie: Perro, Gato, etc.</small>
                                                        @error('mascota_especie') <div class="text-danger">{{ $message }}</div> @enderror
                                                    </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea name="motivo" class="form-control" rows="3" placeholder="Describe brevemente el motivo de la consulta" required>{{ old('motivo') }}</textarea>
                            @error('motivo') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <div class="form-control-plaintext">{{ number_format(\App\Models\Setting::get('cita_precio', '0.00'), 2) }} PEN</div>
                            <small class="text-muted">Precio referencial de cita. El importe final se define en la factura.</small>
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
        const manualInputs = manual.querySelectorAll('input');
        const toggle = () => {
            const useExisting = !!select.value;
            manual.style.display = useExisting ? 'none' : '';
            manualInputs.forEach(i => { i.disabled = useExisting; });
        };
        toggle();
        select.addEventListener('change', toggle);
    }
});
</script>
@endpush