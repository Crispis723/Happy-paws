<aside class="app-sidebar shadow" style="background: linear-gradient(180deg, #f5f5f5 0%, #ffffff 100%); border-right: 3px solid var(--primary-color);" data-bs-theme="light">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); border-bottom: none;">
        <!--begin::Brand Link-->
        <a href="{{route('dashboard')}}" class="brand-link">
            <!--begin::Brand Image-->
            <img src="{{asset('assets/img/logo.jpg')}}" alt="Happy Paws Logo"
                class="brand-image opacity-100 shadow" style="height: 40px; width: auto;" />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-bold text-white">Happy Paws</span>
            <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{route('dashboard')}}" class="nav-link" id="itemDashboard" style="color: var(--text-primary);">
                        <i class="nav-icon bi bi-calendar-check" style="color: var(--primary-color);"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @canany(['citas_list', 'pacientes_list'])
                <li class="nav-item" id="mnuAlmacen">
                    <a href="#" class="nav-link">                        
                        <i class="bi bi-archive-fill"></i>
                        <p>
                            Almacén
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('unidades_list')
                        <li class="nav-item">
                            <a href="{{route('unidades.index')}}" class="nav-link" id="itemUnidades">
                                <i class="bi bi-inbox-fill"></i>
                                <p>Unidades</p>
                            </a>
                        </li>
                        @endcan
                        @can('productos_list')
                        <li class="nav-item">
                            <a href="{{route('productos.index')}}" class="nav-link" id="itemProductos">
                                <i class="bi bi-basket2-fill"></i>
                                <p>Productos</p>
                            </a>
                        </li>
                        @endcan
                        @can('proveedores_list')
                        <li class="nav-item">
                            <a href="{{route('proveedores.index')}}" class="nav-link" id="itemProveedores">
                                <i class="bi bi-person-vcard-fill"></i>
                                <p>Proveedores</p>
                            </a>
                        </li>
                        @endcan
                        @can('compras_list')
                        <li class="nav-item">
                            <a href="{{route('compras.index')}}" class="nav-link" id="itemCompras">
                                <i class="bi bi-cart"></i>
                                <p>Compras</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['clientes_list', 'ventas_list'])
                <li class="nav-item" id="mnuVenta">
                    <a href="#" class="nav-link">                        
                        <i class="bi bi-archive-fill"></i>
                        <p>
                            Ventas
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('clientes_list')
                        <li class="nav-item">
                            <a href="{{route('clientes.index')}}" class="nav-link" id="itemClientes">
                                <i class="bi bi-person-vcard"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        @endcan
                        @can('ventas_list')
                        <li class="nav-item">
                            <a href="{{route('ventas.index')}}" class="nav-link" id="itemVentas">
                                <i class="bi bi-wallet"></i>
                                <p>Ventas</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['compras_list', 'ventas_list'])
                <li class="nav-item" id="mnuReporte">
                    <a href="#" class="nav-link">                        
                        <i class="bi bi-bar-chart"></i>
                        <p>
                            Reportes
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('ventas_list')
                        <li class="nav-item">
                            <a href="{{route('reportes.ventas')}}" class="nav-link" id="itemReporteVentas">
                                <i class="bi bi-file-spreadsheet"></i>
                                <p>Reporte Ventas</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['comprobante_tipos_list', 'documento_tipos_list', 'afectacion_tipos_list'])
                <li class="nav-item" id="mnuConfiguracion">
                    <a href="#" class="nav-link">                        
                        <i class="bi bi-gear"></i>
                        <p>
                            Configuración
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('comprobante_tipos_list')
                        <li class="nav-item">
                            <a href="{{route('comprobante-tipos.index')}}" class="nav-link" id="itemComprobanteTipo">
                                <i class="bi bi-receipt"></i>
                                <p>Tipos de Comprobante</p>
                            </a>
                        </li>
                        @endcan
                        @can('documento_tipos_list')
                        <li class="nav-item">
                            <a href="{{route('documento-tipos.index')}}" class="nav-link" id="itemDocumentoTipo">
                                <i class="bi bi-files"></i>
                                <p>Tipos de Documento</p>
                            </a>
                        </li>
                        @endcan
                        @can('afectacion_tipos_list')
                        <li class="nav-item">
                            <a href="{{route('afectacion-tipos.index')}}" class="nav-link" id="itemAfectacionTipo">
                               <i class="bi bi-file-break"></i>
                                <p>Tipos de Afectación</p>
                            </a>
                        </li>
                        @endcan
                        @can('comprobante_tipos_list')
                        <li class="nav-item">
                            <a href="{{route('comprobante-series.index')}}" class="nav-link" id="itemComprobanteSerie">
                                <i class="bi bi-receipt"></i>
                                <p>Correlativos</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['users_list', 'roles_permisos_list'])
                <li class="nav-item" id="mnuSeguridad">
                    <a href="#" class="nav-link">                        
                        <i class="bi bi-shield-lock-fill"></i>
                        <p>
                            Seguridad
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('roles_permisos_list')
                        <li class="nav-item">
                            <a href="{{route('roles.index')}}" class="nav-link" id="itemRoles">
                                <i class="bi bi-ui-checks-grid"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                        @endcan
                        @can('users_list')
                        <li class="nav-item">
                            <a href="{{route('usuarios.index')}}" class="nav-link" id="itemUsuarios">
                                <i class="bi bi-people"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>