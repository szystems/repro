{{-- Marco wireframe reutilizable — imita layout REPRO sin interactividad --}}
<div class="ayuda-mock mb-4" aria-hidden="true">
    <div class="ayuda-mock-bar">
        <span class="ayuda-mock-dot"></span>
        <span class="ayuda-mock-dot"></span>
        <span class="ayuda-mock-dot"></span>
        <span class="ayuda-mock-label"><i class="bi bi-display me-1"></i>Vista simulada</span>
    </div>
    <div class="ayuda-mock-body">
        <div class="ayuda-mock-sidebar">
            <div class="ayuda-mock-sidebar-item active"></div>
            <div class="ayuda-mock-sidebar-item"></div>
            <div class="ayuda-mock-sidebar-item"></div>
            <div class="ayuda-mock-sidebar-item short"></div>
        </div>
        <div class="ayuda-mock-content">
            <div class="ayuda-mock-header">
                <strong>{{ $titulo }}</strong>
                @if($subtitulo)
                    <small class="text-muted d-block">{{ $subtitulo }}</small>
                @endif
            </div>
            <div class="ayuda-mock-slot">
                @if($innerView)
                    @include($innerView)
                @endif
            </div>
        </div>
    </div>
</div>
