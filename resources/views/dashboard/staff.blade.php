@extends('plantilla.app')

@section('titulo', 'Dashboard')

@section('contenido')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>
                Dashboard Principal
            </h1>
            <p class="text-muted">Bienvenido, <strong>{{ Auth::user()->name }}</strong></p>
        </div>
    </div>

    <!-- Grid de Módulos -->
    <div class="row g-4">
        
        {{-- MÓDULO: CLIENTES --}}
        @can('module-clientes')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Clientes"
                icon="bi-people-fill"
                color="primary"
                route="{{ route('clientes.index') }}"
                description="Gestión de clientes y contactos"
            />
        </div>
        @endcan

        {{-- MÓDULO: MASCOTAS --}}
        @can('module-mascotas')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Mascotas"
                icon="bi-heart-fill"
                color="danger"
                route="{{ route('mascotas.index') }}"
                description="Registro de mascotas y propietarios"
            />
        </div>
        @endcan

        {{-- MÓDULO: CITAS --}}
        @can('module-citas')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Citas"
                icon="bi-calendar-check-fill"
                color="success"
                route="{{ route('citas.index') }}"
                description="Agenda de citas y consultas"
            />
        </div>
        @endcan

        {{-- MÓDULO: PRODUCTOS --}}
        @can('module-productos')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Productos"
                icon="bi-box-seam-fill"
                color="warning"
                route="{{ route('productos.index') }}"
                description="Inventario y catálogo"
            />
        </div>
        @endcan

        {{-- MÓDULO: COMPRAS --}}
        @can('module-compras')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Compras"
                icon="bi-cart-fill"
                color="info"
                route="{{ route('compras.index') }}"
                description="Gestión de proveedores y compras"
            />
        </div>
        @endcan

        {{-- MÓDULO: VENTAS --}}
        @can('module-ventas')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Ventas"
                icon="bi-cash-coin"
                color="success"
                route="{{ route('ventas.index') }}"
                description="Facturación y ventas"
            />
        </div>
        @endcan

        {{-- MÓDULO: REPORTES FINANCIEROS --}}
        @can('module-reportes-financieros')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Reportes Financieros"
                icon="bi-graph-up-arrow"
                color="primary"
                route="{{ route('reportes.financieros') }}"
                description="Análisis financiero y reportes"
            />
        </div>
        @endcan

        {{-- MÓDULO: REPORTES MÉDICOS --}}
        @can('module-reportes-medicos')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Reportes Médicos"
                icon="bi-clipboard-pulse"
                color="danger"
                route="{{ route('reportes.medicos') }}"
                description="Historiales y estadísticas médicas"
            />
        </div>
        @endcan

        {{-- MÓDULO: CONFIGURACIÓN (Solo Admin) --}}
        @can('module-configuracion')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Configuración"
                icon="bi-gear-fill"
                color="secondary"
                route="{{ route('admin.settings.index') }}"
                description="Configuración del sistema"
            />
        </div>
        @endcan

        {{-- MÓDULO: USUARIOS (Admin) --}}
        @can('manage-users')
        <div class="col-xl-3 col-lg-4 col-md-6">
            <x-module-card
                title="Usuarios"
                icon="bi-person-badge-fill"
                color="dark"
                route="{{ route('users.index') }}"
                description="Gestión de usuarios y accesos"
            />
        </div>
        @endcan

    </div>

    <!-- Info adicional -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Tip:</strong> Los módulos visibles dependen de tus permisos. 
                @if(Auth::user()->isAdmin())
                    Como administrador, tienes acceso completo al sistema.
                @elseif(Auth::user()->isStaff())
                    Tu categoría de empleado ({{ Auth::user()->staff_type }}) determina los módulos disponibles.
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
}
</style>
@endsection
