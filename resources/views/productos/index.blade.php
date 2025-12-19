@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">
                        <i class="bi bi-box"></i> Productos
                    </h3>
                    @can('productos_create')
                    <a href="{{ route('productos.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Nuevo Producto
                    </a>
                    @endcan
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card-body">
                    @if($productos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Unidad</th>
                                        <th>Stock</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productos as $producto)
                                        <tr>
                                            {{-- CÓDIGO --}}
                                            <td>
                                                <code>{{ $producto->codigo }}</code>
                                            </td>

                                            {{-- NOMBRE --}}
                                            <td>
                                                <strong>{{ $producto->nombre }}</strong>
                                                @if($producto->descripcion)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($producto->descripcion, 50) }}</small>
                                                @endif
                                            </td>

                                            {{-- UNIDAD --}}
                                            <td>
                                                @if($producto->unidad)
                                                    <span class="badge bg-info">{{ $producto->unidad->descripcion }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            {{-- STOCK --}}
                                            <td>
                                                @if($producto->stock <= 5)
                                                    <span class="badge bg-danger">{{ $producto->stock }}</span>
                                                @elseif($producto->stock <= 20)
                                                    <span class="badge bg-warning">{{ $producto->stock }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ $producto->stock }}</span>
                                                @endif
                                            </td>

                                            {{-- PRECIO --}}
                                            <td>
                                                <strong class="text-primary">S/. {{ number_format($producto->precio_unitario, 2) }}</strong>
                                            </td>

                                            {{-- ACCIONES --}}
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-info" title="Ver">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @can('productos_edit')
                                                        <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-warning" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('productos_delete')
                                                        <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este producto?');" title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINACIÓN --}}
                        <div class="d-flex justify-content-center mt-4">
                            {{ $productos->links() }}
                        </div>
                    @else
                        {{-- MENSAJE VACÍO --}}
                        <div class="alert alert-info text-center py-5" role="alert">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No hay productos registrados</h5>
                            <p class="text-muted">Comienza creando tu primer producto</p>
                            @can('productos_create')
                                <a href="{{ route('productos.create') }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle"></i> Crear Producto
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Activar elemento de menú
    const mnuProductos = document.getElementById('mnuProductos');
    if(mnuProductos) mnuProductos.classList.add('menu-open');
    const itemProductos = document.getElementById('itemProductos');
    if(itemProductos) itemProductos.classList.add('active');
});
</script>
@endpush