@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil"></i> Editar Producto
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- CÓDIGO DEL PRODUCTO --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Código <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror" 
                                   value="{{ old('codigo', $producto->codigo) }}" required>
                            @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- NOMBRE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre', $producto->nombre) }}" required>
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- DESCRIPCIÓN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $producto->descripcion) }}</textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- UNIDAD --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Unidad <span class="text-danger">*</span></label>
                            <select name="unidad_codigo" class="form-select @error('unidad_codigo') is-invalid @enderror" required>
                                <option value="">-- Seleccionar unidad --</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->codigo }}" 
                                            {{ old('unidad_codigo', $producto->unidad_codigo) == $unidad->codigo ? 'selected' : '' }}>
                                        {{ $unidad->codigo }} - {{ $unidad->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('unidad_codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- AFECTACIÓN TIPO --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo Afectación <span class="text-danger">*</span></label>
                            <select name="afectacion_tipo_codigo" class="form-select @error('afectacion_tipo_codigo') is-invalid @enderror" required>
                                <option value="">-- Seleccionar tipo --</option>
                                @foreach($afectacionTipos as $tipo)
                                    <option value="{{ $tipo->codigo }}" 
                                            {{ old('afectacion_tipo_codigo', $producto->afectacion_tipo_codigo) == $tipo->codigo ? 'selected' : '' }}>
                                        {{ $tipo->codigo }} - {{ $tipo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('afectacion_tipo_codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- PRECIO --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Precio Unitario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" name="precio_unitario" class="form-control @error('precio_unitario') is-invalid @enderror" 
                                       value="{{ old('precio_unitario', $producto->precio_unitario) }}" step="0.01" min="0" required>
                            </div>
                            @error('precio_unitario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- STOCK --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Stock <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                                   value="{{ old('stock', $producto->stock) }}" step="0.01" min="0" required>
                            @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- IMAGEN --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Imagen del Producto</label>
                            
                            @if($producto->imagen)
                                <div class="mb-2">
                                    <img src="{{ asset('uploads/productos/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" 
                                         class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                            @endif

                            <input type="file" name="imagen" class="form-control @error('imagen') is-invalid @enderror" 
                                   accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">JPG, JPEG o PNG. Máximo 2MB (Deja en blanco para no cambiar)</small>
                            @error('imagen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- BOTONES --}}
                        <div class="d-flex justify-content-between gap-2 mt-4">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
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
    const mnuAlmacen = document.getElementById('mnuAlmacen');
    if(mnuAlmacen) mnuAlmacen.classList.add('menu-open');
    const itemProductos = document.getElementById('itemProductos');
    if(itemProductos) itemProductos.classList.add('active');
});
</script>
@endpush
