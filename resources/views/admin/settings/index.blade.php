@extends('plantilla.app')

@section('titulo', 'Configuración del Sistema')

@section('contenido')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">
                <i class="bi bi-gear-fill text-secondary me-2"></i>
                Configuración del Sistema
            </h2>
            <p class="text-muted">Ajustes generales (solo administradores)</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card: Precio de Citas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-currency-dollar text-success me-2"></i>Precio de Citas</h5>
                    <p class="card-text">Configurar precio estándar de consulta</p>
                    <a href="{{ route('admin.settings.cita_precio.edit') }}" class="btn btn-primary">Configurar</a>
                </div>
            </div>
        </div>

        <!-- Card: Usuarios -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-people-fill text-primary me-2"></i>Usuarios</h5>
                    <p class="card-text">Gestión de usuarios y permisos</p>
                    <a href="{{ route('users.index') }}" class="btn btn-primary">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Card: Roles -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-shield-lock text-warning me-2"></i>Roles y Permisos</h5>
                    <p class="card-text">Configurar accesos del sistema</p>
                    <a href="{{ route('roles.index') }}" class="btn btn-primary">Configurar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
