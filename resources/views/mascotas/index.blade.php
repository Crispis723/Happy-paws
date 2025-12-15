@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title flex-grow-1">Mis Mascotas</h3>
                    <a href="{{ route('mascotas.create') }}" class="btn btn-primary">Nuevo</a>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($mascotas as $m)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $m->nombre }}</strong> — {{ $m->especie }}
                            </div>
                            <div>
                                <a href="{{ route('mascotas.show', $m) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <a href="{{ route('mascotas.edit', $m) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item">No tienes mascotas. <a href="{{ route('mascotas.create') }}">Agregar una</a></li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer">{{ $mascotas->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
