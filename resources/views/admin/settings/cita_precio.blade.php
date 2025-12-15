@extends('plantilla.app')
@section('contenido')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Precio de Cita</div>
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    <form action="{{ route('admin.settings.cita_precio.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Precio (PEN)</label>
                            <input type="number" step="0.01" name="cita_precio" class="form-control" value="{{ old('cita_precio', $precio) }}">
                            @error('cita_precio') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
