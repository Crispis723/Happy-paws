@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cart-plus"></i> Editar Compra
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('compras.update', $compra->id) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- FECHA --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" 
                                   value="{{ old('fecha', optional($compra->fecha)->format('Y-m-d')) }}" required>
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- PROVEEDOR --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Proveedor <span class="text-danger">*</span></label>
                            <select name="proveedor_id" class="form-select @error('proveedor_id') is-invalid @enderror" required>
                                <option value="">-- Seleccionar proveedor --</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ old('proveedor_id', $compra->proveedor_id) == $proveedor->id ? 'selected' : '' }}>
                                        {{ $proveedor->razon_social }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proveedor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- TIPO COMPROBANTE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo Comprobante <span class="text-danger">*</span></label>
                            <select name="comprobante_tipo_codigo" class="form-select @error('comprobante_tipo_codigo') is-invalid @enderror" required>
                                <option value="">-- Seleccionar tipo --</option>
                                @foreach($comprobanteTipos as $tipo)
                                    <option value="{{ $tipo->codigo }}" {{ old('comprobante_tipo_codigo', $compra->comprobante_tipo_codigo) == $tipo->codigo ? 'selected' : '' }}>
                                        {{ $tipo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                            @error('comprobante_tipo_codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- FORMA DE PAGO --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Forma de Pago <span class="text-danger">*</span></label>
                            <select name="forma_pago" class="form-select @error('forma_pago') is-invalid @enderror" required>
                                <option value="">-- Seleccionar forma --</option>
                                <option value="efectivo" {{ old('forma_pago', $compra->forma_pago) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="cheque" {{ old('forma_pago', $compra->forma_pago) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="transferencia" {{ old('forma_pago', $compra->forma_pago) == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="tarjeta" {{ old('forma_pago', $compra->forma_pago) == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                            </select>
                            @error('forma_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- TOTAL --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">S/.</span>
                                <input type="number" name="total" class="form-control @error('total') is-invalid @enderror" 
                                       value="{{ old('total', $compra->total) }}" step="0.01" min="0" required>
                            </div>
                            @error('total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- BOTONES --}}
                        <div class="d-flex justify-content-between gap-2 mt-4">
                            <a href="{{ route('compras.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Compra</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection