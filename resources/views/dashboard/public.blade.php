@extends('plantilla.app')

@section('titulo', 'Mi Panel')

@section('contenido')
<div class="container py-5">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-person-circle text-primary"></i>
                Bienvenido, {{ Auth::user()->name }}
            </h1>
            <p class="lead text-muted">Gestiona tus mascotas y citas desde aquí</p>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row g-4">
        
        <!-- Mis Mascotas -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-heart-fill text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Mis Mascotas</h3>
                    <p class="text-muted mb-4">Administra la información de tus mascotas</p>
                    <a href="{{ route('mascotas.index') }}" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-list-ul me-2"></i>Ver Mis Mascotas
                    </a>
                </div>
            </div>
        </div>

        <!-- Pedir Cita -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-calendar-plus-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Agendar Cita</h3>
                    <p class="text-muted mb-4">Solicita una cita para tu mascota</p>
                    <a href="{{ route('citas.create') }}" class="btn btn-success btn-lg px-5">
                        <i class="bi bi-plus-circle me-2"></i>Pedir Cita
                    </a>
                </div>
            </div>
        </div>

        <!-- Mis Citas -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-calendar-check text-info" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Mis Citas</h3>
                    <p class="text-muted mb-4">Revisa tus citas programadas</p>
                    <a href="{{ route('citas.index') }}" class="btn btn-info btn-lg px-5">
                        <i class="bi bi-eye me-2"></i>Ver Citas
                    </a>
                </div>
            </div>
        </div>

        <!-- Mi Perfil -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-person-circle text-secondary" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="card-title fw-bold mb-3">Mi Perfil</h3>
                    <p class="text-muted mb-4">Actualiza tu información personal</p>
                    <a href="{{ route('perfil.edit') }}" class="btn btn-secondary btn-lg px-5">
                        <i class="bi bi-pencil-square me-2"></i>Editar Perfil
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Info adicional -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-primary border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">¿Necesitas ayuda?</h5>
                        <p class="mb-0">Contacta con nuestra recepción para cualquier consulta sobre citas o servicios.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
}

.btn-lg {
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: scale(1.05);
}
</style>
@endsection
