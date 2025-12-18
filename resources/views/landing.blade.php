<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Happy Paws - Clínica Veterinaria</title>
    <link rel="shortcut icon" href="{{asset('assets/favicon.ico')}}" type="image/x-icon">
    <link rel="stylesheet" href="{{asset('css/colors.css')}}" />
    <link rel="stylesheet" href="{{asset('css/landing.css')}}" />
    <link rel="stylesheet" href="{{asset('css/auth-modal.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/bootstrap-icons-1.13.1/bootstrap-icons.min.css')}}" />
  </head>
  <body>
    <!-- Header -->
    <header class="navbar">
      <nav class="container flex-wrapper">
        <a class="navbar-brand" href="/">
          <img src="{{ asset('assets/img/logo.jpg') }}" alt="Happy Paws" class="logo-img">
          Happy Paws
        </a>
        <div class="navbar-nav" id="navMenu">
          <a href="#inicio">Inicio</a>
          <a href="#servicios">Servicios</a>
          <a href="#contacto">Contacto</a>
          @guest
            <a href="#" onclick="openAuthModal()" class="btn-secondary">Ingresar</a>
          @else
            <a href="{{ route('dashboard') }}" class="btn-secondary">Dashboard</a>
          @endguest
        </div>
        <button class="btn-menu hidden-md" id="menuBtn" type="button">
          <i class="bi bi-list"></i>
        </button>
      </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section" id="inicio">
      <div class="container flex-wrapper">
        <div class="hero-content">
          <h1>Sistema de Gestión Veterinario</h1>
          <p>Administra citas, pacientes, tratamientos e historia médica de forma eficiente en tu clínica veterinaria.</p>
          @if(session('success'))
            <div class="alert alert-success" role="alert" style="margin-top:1rem">{{ session('success') }}</div>
          @endif
          <div class="btn-group">
            <a href="{{ route('citas.create') }}" class="btn-primary">Pedir cita</a>
            @guest
              <a href="#" onclick="openAuthModal()" class="btn-secondary">Comenzar</a>
            @else
              <a href="{{ route('dashboard') }}" class="btn-secondary">Ir al Dashboard</a>
            @endguest
          </div>
        </div>
        <div>
          <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=500&q=80" alt="Clínica Veterinaria" class="hero-image">
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="servicios">
      <div class="container">
        <h2>Características Principales</h2>
        <div class="services-grid">
          <div class="service-card">
            <div class="service-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <h3>Gestión de Citas</h3>
            <p>Agenda y administra citas con recordatorios automáticos para clientes y mascotas.</p>
          </div>
          <div class="service-card">
            <div class="service-icon">
              <i class="bi bi-heart-pulse"></i>
            </div>
            <h3>Historia Médica</h3>
            <p>Registro completo de pacientes con diagnósticos, tratamientos y vacunas.</p>
          </div>
          <div class="service-card">
            <div class="service-icon">
              <i class="bi bi-people"></i>
            </div>
            <h3>Gestión de Clientes</h3>
            <p>Base de datos de dueños con contacto, mascotas asociadas e historial.</p>
          </div>
          <div class="service-card">
            <div class="service-icon">
              <i class="bi bi-box-seam"></i>
            </div>
            <h3>Control de Inventario</h3>
            <p>Medicinas, vacunas y suministros médicos con alertas de stock bajo.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contacto">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h4>Happy Paws</h4>
            <p>Sistema integral de gestión veterinaria para clínicas y consultorios de mascotas.</p>
          </div>
          <div class="footer-section">
            <h4>Enlaces Rápidos</h4>
            <p><a href="#inicio" style="color: white; text-decoration: none;">Inicio</a></p>
            <p><a href="#servicios" style="color: white; text-decoration: none;">Servicios</a></p>
            @guest
              <p><a href="{{ route('login') }}" style="color: white; text-decoration: none;">Ingresar</a></p>
            @endguest
          </div>
          <div class="footer-section">
            <h4>Contacto</h4>
            <p>Email: info@proyectoadso.com</p>
            <p>Teléfono: +57 (1) 2345-6789</p>
            <p>Horario: Lun-Vie 8am-6pm</p>
          </div>
        </div>
        
        <div class="footer-links">
          <a href="#" title="Twitter"><i class="bi bi-twitter"></i></a>
          <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
        </div>

        <div class="footer-divider">
          <p>© 2024 Happy Paws. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <script>
      // Mobile Menu Toggle
      const menuBtn = document.getElementById('menuBtn');
      const navMenu = document.getElementById('navMenu');

      if (menuBtn) {
        menuBtn.addEventListener('click', function() {
          navMenu.classList.toggle('active');
        });
      }

      // Smooth Scroll
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            navMenu.classList.remove('active');
            target.scrollIntoView({ behavior: 'smooth' });
          }
        });
      });
    </script>

    <!-- Auth Modal Component -->
    @include('autenticacion.auth-modal')

</body></html>