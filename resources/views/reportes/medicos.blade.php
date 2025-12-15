@extends('plantilla.app')

@section('titulo', 'Reportes Médicos')

@section('contenido')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">
                <i class="bi bi-clipboard-pulse text-danger me-2"></i>
                Reportes Médicos
            </h2>
            <p class="text-muted">Estadísticas e historiales de consultas veterinarias</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card: Historial de Citas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-calendar-check text-success me-2"></i>Historial de Citas</h5>
                    <p class="card-text">Consultas realizadas y programadas</p>
                    <a href="{{ route('citas.index') }}" class="btn btn-primary">Ver Citas</a>
                </div>
            </div>
        </div>

        <!-- Card: Mascotas Atendidas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-heart-pulse text-danger me-2"></i>Mascotas Atendidas</h5>
                    <p class="card-text">Registro de pacientes</p>
                    <a href="{{ route('mascotas.index') }}" class="btn btn-primary">Ver Mascotas</a>
                </div>
            </div>
        </div>

        <!-- Card: Estadísticas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-pie-chart text-info me-2"></i>Estadísticas</h5>
                    <p class="card-text">Métricas de atención veterinaria</p>
                    <a href="#" class="btn btn-primary">Próximamente</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
