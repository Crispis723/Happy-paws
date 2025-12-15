@props([
    'title' => 'Módulo',
    'icon' => 'bi-app',
    'color' => 'primary',
    'route' => '#',
    'description' => ''
])

<a href="{{ $route }}" class="text-decoration-none">
    <div class="module-card card h-100 border-0 shadow-sm hover-lift">
        <div class="card-body text-center p-4">
            <div class="module-icon mb-3">
                <i class="bi {{ $icon }} text-{{ $color }}" style="font-size: 3rem;"></i>
            </div>
            <h5 class="card-title fw-bold mb-2">{{ $title }}</h5>
            @if($description)
                <p class="card-text text-muted small">{{ $description }}</p>
            @endif
        </div>
    </div>
</a>

<style>
.module-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.module-icon {
    transition: transform 0.3s ease;
}

.module-card:hover .module-icon {
    transform: scale(1.1);
}
</style>
