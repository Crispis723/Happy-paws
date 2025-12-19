@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box"></i> Detalle del Producto
                    </h5>
                    <span class="badge bg-light text-dark">ID: {{ $producto->id }}</span>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card-body">
                    <div class="row">
                        {{-- IMAGEN --}}
                        <div class="col-md-4 text-center mb-4">
                            @if($producto->imagen)
                                <img src="{{ asset('uploads/productos/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" 
                                     class="img-fluid rounded" style="max-width: 100%; max-height: 300px;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <div class="text-center">
                                        <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Sin imagen</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- INFORMACIÓN --}}
                        <div class="col-md-8">
                            {{-- CÓDIGO Y NOMBRE --}}
                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase">Código</h6>
                                <h5 class="mb-0">
                                    <code>{{ $producto->codigo }}</code>
                                </h5>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase">Nombre</h6>
                                <h4 class="mb-0">{{ $producto->nombre }}</h4>
                            </div>

                            {{-- DESCRIPCIÓN --}}
                            @if($producto->descripcion)
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase">Descripción</h6>
                                    <p class="mb-0">{{ $producto->descripcion }}</p>
                                </div>
                            @endif

                            {{-- DATOS TÉCNICOS --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-muted text-uppercase">Unidad</h6>
                                        @if($producto->unidad)
                                            <span class="badge bg-info fs-6">
                                                {{ $producto->unidad->codigo }} - {{ $producto->unidad->descripcion }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6 class="text-muted text-uppercase">Afectación</h6>
                                        @if($producto->afectacionTipo)
                                            <span class="badge bg-warning text-dark fs-6">
                                                {{ $producto->afectacionTipo->codigo }} - {{ $producto->afectacionTipo->descripcion }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- PRECIO Y STOCK --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 p-3 bg-light rounded">
                                        <h6 class="text-muted text-uppercase">Precio Unitario</h6>
                                        <h4 class="mb-0 text-success">
                                            S/. {{ number_format($producto->precio_unitario, 2) }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3 p-3 bg-light rounded">
                                        <h6 class="text-muted text-uppercase">Stock Disponible</h6>
                                        <h4 class="mb-0">
                                            @if($producto->stock <= 5)
                                                <span class="text-danger">{{ $producto->stock }} unidades</span>
                                            @elseif($producto->stock <= 20)
                                                <span class="text-warning">{{ $producto->stock }} unidades</span>
                                            @else
                                                <span class="text-success">{{ $producto->stock }} unidades</span>
                                            @endif
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BOTONES DE ACCIÓN --}}
                    <div class="d-flex justify-content-between gap-2 mt-5 pt-4 border-top">
                        <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>

                        <div>
                            @can('productos_edit')
                                <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @endcan

                            @can('productos_delete')
                                <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.');">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            @endcan
                        </div>
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
    const mnuAlmacen = document.getElementById('mnuAlmacen');
    if(mnuAlmacen) mnuAlmacen.classList.add('menu-open');
    const itemProductos = document.getElementById('itemProductos');
    if(itemProductos) itemProductos.classList.add('active');
});
</script>
@endpush
