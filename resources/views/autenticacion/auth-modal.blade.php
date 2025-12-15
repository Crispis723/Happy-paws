<!-- Auth Modal - Login Form -->
<div class="auth-modal-overlay" id="authModalOverlay">
  <div class="auth-modal" id="authModal">
    <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>

    <div class="auth-modal-header">
      <img src="{{ asset('assets/img/logo.jpg') }}" alt="Happy Paws" class="auth-modal-logo">
      <h1 class="auth-modal-title">Happy Paws</h1>
      <p class="auth-modal-subtitle">Sistema de Gestión Veterinaria</p>
    </div>

    <div class="auth-modal-body">
      <!-- Login Form -->
      <div id="loginForm" class="auth-form">
        <h2>Iniciar Sesión</h2>

        @if(session('error'))
          <div class="auth-alert error">
            <strong>Error:</strong> {{ session('error') }}
          </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
          @csrf

          <div class="form-group">
            <label for="loginEmail">Correo Electrónico</label>
            <input 
              type="email" 
              id="loginEmail" 
              name="email" 
              placeholder="tu@correo.com"
              value="{{ old('email') }}"
              required
            >
            @error('email')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="loginPassword">Contraseña</label>
            <input 
              type="password" 
              id="loginPassword" 
              name="password" 
              placeholder="••••••••"
              required
            >
            @error('password')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <button type="submit" class="auth-btn auth-btn-primary">
            Ingresar
          </button>
        </form>

        <div class="auth-links">
          <p><a href="#">¿Olvidaste tu contraseña?</a></p>
          <p class="auth-divider">o</p>
          <p class="auth-toggle-text">
            ¿No tienes cuenta? 
            <a href="#" class="auth-toggle-link" onclick="toggleForms()">Regístrate</a>
          </p>
        </div>
      </div>

      <!-- Register Form (Hidden by default) -->
      <div id="registerForm" class="auth-form" style="display: none;">
        <h2>Crear Cuenta</h2>

        <form action="{{ route('register.post') }}" method="POST">
          @csrf

          <div class="form-group">
            <label for="regName">Nombre Completo</label>
            <input 
              type="text" 
              id="regName" 
              name="name" 
              placeholder="Tu nombre"
              value="{{ old('name') }}"
              required
            >
            @error('name')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="regEmail">Correo Electrónico</label>
            <input 
              type="email" 
              id="regEmail" 
              name="email" 
              placeholder="tu@correo.com"
              value="{{ old('email') }}"
              required
            >
            @error('email')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="regPassword">Contraseña</label>
            <input 
              type="password" 
              id="regPassword" 
              name="password" 
              placeholder="••••••••"
              required
            >
            @error('password')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="regPasswordConfirm">Confirmar Contraseña</label>
            <input 
              type="password" 
              id="regPasswordConfirm" 
              name="password_confirmation" 
              placeholder="••••••••"
              required
            >
          </div>

          <button type="submit" class="auth-btn auth-btn-primary">
            Registrarse
          </button>
        </form>

        <div class="auth-links">
          <p class="auth-toggle-text">
            ¿Ya tienes cuenta? 
            <a href="#" class="auth-toggle-link" onclick="toggleForms()">Inicia sesión</a>
          </p>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  function openAuthModal() {
    document.getElementById('authModalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeAuthModal() {
    document.getElementById('authModalOverlay').classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  function toggleForms() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm.style.display === 'none') {
      loginForm.style.display = 'block';
      registerForm.style.display = 'none';
    } else {
      loginForm.style.display = 'none';
      registerForm.style.display = 'block';
    }
  }

  // Cerrar modal al clickear fuera
  document.getElementById('authModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
      closeAuthModal();
    }
  });

  // ESC cierra el modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeAuthModal();
    }
  });
</script>
