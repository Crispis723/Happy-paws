@extends('plantilla.app')
@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Detalle de Compra</h5>
                </div>

                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4 fw-bold">Fecha</dt>
                        <dd class="col-sm-8">{{ $compra->fecha->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4 fw-bold">Proveedor</dt>
                        <dd class="col-sm-8">{{ $compra->proveedor->razon_social ?? '-' }}</dd>

                        <dt class="col-sm-4 fw-bold">Total</dt>
                        <dd class="col-sm-8"><strong class="text-success">S/. {{ number_format($compra->total, 2) }}</strong></dd>

                        <dt class="col-sm-4 fw-bold">Forma de Pago</dt>
                        <dd class="col-sm-8">{{ ucfirst($compra->forma_pago) }}</dd>

                        <dt class="col-sm-4 fw-bold">Estado</dt>
                        <dd class="col-sm-8">{{ ucfirst($compra->estado) }}</dd>
                    </dl>

                    <div class="d-flex justify-content-between gap-2 mt-4">
                        <a href="{{ route('compras.index') }}" class="btn btn-secondary">Volver</a>
                        <a href="{{ route('compras.edit', $compra->id) }}" class="btn btn-primary">Editar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection