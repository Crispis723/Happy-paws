@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Detalle de Cita</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Fecha y Hora</dt>
                        <dd class="col-sm-8">{{ optional($cita->fecha_hora)->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Cliente</dt>
                        <dd class="col-sm-8">{{ $cita->cliente_nombre }}</dd>

                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">{{ $cita->cliente_telefono }}</dd>

                        <dt class="col-sm-4">Mascota</dt>
                        <dd class="col-sm-8">{{ $cita->mascota_nombre }}</dd>

                        <dt class="col-sm-4">Especie</dt>
                        <dd class="col-sm-8">{{ $cita->mascota_especie }}</dd>

                        <dt class="col-sm-4">Veterinario</dt>
                        <dd class="col-sm-8">{{ optional($cita->veterinario)->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Motivo</dt>
                        <dd class="col-sm-8">{{ $cita->motivo }}</dd>

                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8">{{ ucfirst($cita->estado ?? 'pendiente') }}</dd>

                        <dt class="col-sm-4">Precio</dt>
                        <dd class="col-sm-8">{{ $cita->precio ? number_format($cita->precio,2) : '-' }}</dd>

                        <dt class="col-sm-4">Notas</dt>
                        <dd class="col-sm-8">{{ $cita->notas ?? '-' }}</dd>
                    </dl>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('citas.index') }}" class="btn btn-secondary">Volver</a>
                        <a href="{{ route('citas.edit', $cita) }}" class="btn btn-primary">Editar</a>
                    </div>
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
