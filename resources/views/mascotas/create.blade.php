@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Crear Mascota</div>
                <div class="card-body">
                    <form action="{{ route('mascotas.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}">
                            @error('nombre') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especie</label>
                            <input type="text" name="especie" class="form-control" value="{{ old('especie') }}">
                            @error('especie') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Raza (opcional)</label>
                            <input type="text" name="raza" class="form-control" value="{{ old('raza') }}">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('mascotas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
