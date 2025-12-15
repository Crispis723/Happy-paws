@extends('plantilla.app')

@section('titulo', 'Reportes Financieros')

@section('contenido')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i>
                Reportes Financieros
            </h2>
            <p class="text-muted">Análisis de ventas, compras y balance general</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card: Reporte de Ventas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-cash-coin text-success me-2"></i>Ventas</h5>
                    <p class="card-text">Resumen de ventas por período</p>
                    <a href="{{ route('reportes.ventas') }}" class="btn btn-primary">Ver Reporte</a>
                </div>
            </div>
        </div>

        <!-- Card: Balance -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-bar-chart text-info me-2"></i>Balance General</h5>
                    <p class="card-text">Ingresos vs Egresos</p>
                    <a href="#" class="btn btn-primary">Próximamente</a>
                </div>
            </div>
        </div>

        <!-- Card: Inventario -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box-seam text-warning me-2"></i>Inventario</h5>
                    <p class="card-text">Estado de productos y stock</p>
                    <a href="{{ route('productos.index') }}" class="btn btn-primary">Ver Productos</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
