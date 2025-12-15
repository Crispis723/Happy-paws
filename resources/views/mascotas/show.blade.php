@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Mascota: {{ $mascota->nombre }}</div>
                <div class="card-body">
                    <p><strong>Especie:</strong> {{ $mascota->especie }}</p>
                    <p><strong>Raza:</strong> {{ $mascota->raza }}</p>
                    <a href="{{ route('citas.create') }}?mascota_id={{ $mascota->id }}" class="btn btn-primary">Pedir cita para esta mascota</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
