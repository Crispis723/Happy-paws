@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Agendar Nueva Cita</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('citas.store') }}" method="POST" novalidate>
                        @csrf

                        {{-- Fecha y Hora --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_hora" class="form-control @error('fecha_hora') is-invalid @enderror" value="{{ old('fecha_hora') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required autofocus aria-describedby="help-fecha-hora">
                            <small id="help-fecha-hora" class="text-muted">Selecciona la fecha y hora de la cita. No puede ser en el pasado.</small>
                            @error('fecha_hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Cliente (autenticado o invitado) --}}
                        @guest
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Cliente <span class="text-danger">*</span></label>
                            <input type="text" name="cliente_nombre" class="form-control @error('cliente_nombre') is-invalid @enderror" value="{{ old('cliente_nombre') }}" placeholder="Ej. Juan Pérez" required>
                            @error('cliente_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" name="cliente_telefono" class="form-control @error('cliente_telefono') is-invalid @enderror" value="{{ old('cliente_telefono') }}" placeholder="Ej. 999999999" pattern="^[0-9\s\-\+]{6,}$" required aria-describedby="help-telefono">
                            <small id="help-telefono" class="text-muted">Solo números, espacios, guiones o +. Mínimo 6 caracteres.</small>
                            @error('cliente_telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @else
                        <div class="alert alert-info mb-3" role="alert">
                            <i class="bi bi-info-circle"></i> Cita registrada a nombre de: <strong>{{ auth()->user()->name }}</strong>
                            @if(auth()->user()->telefono) ({{ auth()->user()->telefono }}) @endif
                        </div>
                        <input type="hidden" name="cliente_nombre" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="cliente_telefono" value="{{ auth()->user()->telefono ?? '' }}">
                        @endguest

                        {{-- Veterinario --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Veterinario <span class="text-danger">*</span></label>
                            <select name="veterinario_id" class="form-select @error('veterinario_id') is-invalid @enderror" required aria-describedby="help-vet">
                                <option value="">-- Seleccionar veterinario --</option>
                                @forelse($veterinarios as $vet)
                                    <option value="{{ $vet->id }}" {{ old('veterinario_id') == $vet->id ? 'selected' : '' }}>
                                        {{ $vet->name }}
                                    </option>
                                @empty
                                    <option value="" disabled>No hay veterinarios disponibles</option>
                                @endforelse
                            </select>
                            <small id="help-vet" class="text-muted">Elige el profesional que atenderá la cita.</small>
                            @error('veterinario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Mascota --}}
                        @php $hasMascotas = isset($mascotas) && $mascotas->isNotEmpty(); @endphp

                        @if($hasMascotas)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mascota Registrada</label>
                            <select name="mascota_id" id="mascotaSelect" class="form-select" aria-describedby="help-mascota">
                                <option value="">-- Agregar mascota manualmente --</option>
                                @foreach($mascotas as $m)
                                    <option value="{{ $m->id }}" {{ (old('mascota_id', $selected_mascota_id ?? '') == $m->id) ? 'selected' : '' }}>
                                        {{ $m->nombre }} ({{ $m->especie }})
                                    </option>
                                @endforeach
                            </select>
                            <small id="help-mascota" class="text-muted">Selecciona una mascota registrada o completa los datos manualmente.</small>
                        </div>
                        @endif

                        {{-- Datos Manual de Mascota --}}
                        <div id="manualFields" class="border-top pt-3 mt-3">
                            <h6 class="mb-3">Información de la Mascota</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="mascota_nombre" class="form-control @error('mascota_nombre') is-invalid @enderror" value="{{ old('mascota_nombre') }}" placeholder="Ej. Firulais" aria-describedby="help-mascota-nombre" required>
                                <small id="help-mascota-nombre" class="text-muted">Nombre de la mascota.</small>
                                @error('mascota_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Especie <span class="text-danger">*</span></label>
                                <input type="text" name="mascota_especie" class="form-control @error('mascota_especie') is-invalid @enderror" value="{{ old('mascota_especie') }}" placeholder="Ej. Perro, Gato" aria-describedby="help-mascota-especie" required>
                                <small id="help-mascota-especie" class="text-muted">Especie: Perro, Gato, etc.</small>
                                @error('mascota_especie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Motivo --}}
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">Motivo de la Consulta <span class="text-danger">*</span></label>
                            <textarea name="motivo" class="form-control @error('motivo') is-invalid @enderror" rows="3" placeholder="Describe brevemente el motivo de la consulta" required>{{ old('motivo') }}</textarea>
                            @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Precio Referencial --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Precio Referencial</label>
                            <div class="alert alert-light border">
                                <strong>{{ number_format(\App\Models\Setting::get('cita_precio', '0.00'), 2) }} PEN</strong>
                            </div>
                            <small class="text-muted">Precio base de la cita. El importe final se define en la factura.</small>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('citas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Agendar Cita
                            </button>
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
    // Highlight menu
    const mnuVenta = document.getElementById('mnuVenta');
    if(mnuVenta) mnuVenta.classList.add('menu-open');
    const item = document.getElementById('itemPedirCita');
    if(item) item.classList.add('active');

    // Toggle manual fields when mascota is selected
    const select = document.getElementById('mascotaSelect');
    const manual = document.getElementById('manualFields');
    if(select && manual){
        const manualInputs = manual.querySelectorAll('input');
        const toggle = () => {
            const useExisting = !!select.value;
            // Show/hide section
            manual.style.display = useExisting ? 'none' : '';
            // Disable/enable validation for manual inputs
            manualInputs.forEach(i => { 
                i.disabled = useExisting; 
                i.required = !useExisting;
            });
        };
        toggle();
        select.addEventListener('change', toggle);
    } else if(!select && manual) {
        // No mascota selector, manual fields always required
        const manualInputs = manual.querySelectorAll('input[name="mascota_nombre"], input[name="mascota_especie"]');
        manualInputs.forEach(i => { i.required = true; });
    }
});
</script>
@endpush