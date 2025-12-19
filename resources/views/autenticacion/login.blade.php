<!doctype html>
<html lang="es">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('titulo','Sistema - Ventas')</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Sistema - IncanatoApps" />
    <meta name="author" content="IncanatoApps" />
    <meta
      name="description"
      content="Sistema de Ventas en Laravell."
    />
    <meta
      name="keywords"
      content="Sistema en laravel, Sistema de Ventas"
    />
    <link rel="shortcut icon" href="{{asset('assets/favicon.ico')}}" type="image/x-icon">
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="{{asset('css/overlayscrollbars.min.css')}}"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="{{ secure_asset('assets/bootstrap-icons-1.13.1/bootstrap-icons.min.css') }}"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Styles (Bootstrap/AdminLTE + colores)-->
    <link rel="stylesheet" href="{{ secure_asset('css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ secure_asset('css/colors.css') }}">
    <!--end::Required Styles-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="login-page bg-warning-subtle">
    <div class="login-box">
      <div class="card card-outline card-dark">
        <div class="card-header bg-success">
          <a
            href="/"
            class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover"
          >
          <img src="{{ asset('assets/img/logo.jpg') }}" alt="Happy Paws" class="logo-img"> 
          <h1 class="mb-0"><b>Happy paws</b></h1>
          </a>
        </div>
        <div class="card-body login-card-body bg-dark-subtle">
          <p class="login-box-msg">Ingrese sus credenciales de acceso</p>
          @if(session('error'))
            <div class="alert alert-danger">
              {{session('error')}}
            </div>
          @endif
          <form action="{{route('login.post')}}" method="post">
            @csrf
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="loginEmail" type="email" name="email" class="form-control" value="{{old('email')}}" placeholder="" required />
                <label for="loginEmail">Email</label>
              </div>
              <div class="input-group-text"><span class="bi bi-envelope"></span></div>
            </div>
            @error('email')
                <div class="mb-2"><small class="text-danger">{{ $message }}</small></div>
            @enderror
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="loginPassword" type="password" name="password" class="form-control" placeholder="" required/>
                <label for="loginPassword">Password</label>
              </div>
              <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>              
            </div>
            @error('password')
                <div class="mb-2"><small class="text-danger">{{ $message }}</small></div>
            @enderror
            <!--begin::Row-->
            <div class="row">
              <!-- /.col -->
              <div class="col-10">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-secondary">Ingresar</button>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </form>
          
          <!-- /.social-auth-links -->
           
          <p class="mb-1"><a href="forgot-password.html">¿Olvidaste tu contraseña?</a></p>
          <p class="mb-0">
            <a href="register.html" class="text-center"> Regístrate </a>
          </p>
          
        </div>
        <!-- /.login-card-body -->
      </div>
    </div>
    <!-- /.login-box -->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="{{ secure_asset('js/overlayscrollbars.browser.es6.min.js') }}"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="{{ secure_asset('js/popper.min.js') }}"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="{{ secure_asset('js/bootstrap.min.js') }}"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ secure_asset('js/adminlte.js') }}"></script>
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
