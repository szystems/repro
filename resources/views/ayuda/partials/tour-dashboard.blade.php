@if(request()->routeIs('dashboard'))
@php
    $tourId = Auth::user()->role_as >= 2 ? 'repro-dashboard-v1' : 'empresa-dashboard-v1';
    $tourSteps = Auth::user()->role_as >= 2
        ? [
            ['element' => '#sidebar', 'popover' => ['title' => 'Menú principal', 'description' => 'Acceda a órdenes, cuestionarios, calendario y más desde aquí.', 'side' => 'right']],
            ['element' => 'a[href*="ayuda"]', 'popover' => ['title' => 'Centro de Ayuda', 'description' => 'Guías paso a paso, FAQ y glosario para resolver dudas sin consultoría.', 'side' => 'right']],
        ]
        : [
            ['element' => '#sidebar', 'popover' => ['title' => 'Su portal', 'description' => 'Mis Órdenes, Nueva Orden (si tiene permiso) y Estado de Procesos.', 'side' => 'right']],
            ['element' => 'a[href*="ayuda"]', 'popover' => ['title' => 'Centro de Ayuda', 'description' => 'Aprenda a crear órdenes, dar seguimiento y gestionar permisos de su equipo.', 'side' => 'right']],
        ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tourKey = 'repro_ayuda_tour_{{ $tourId }}';
    if (localStorage.getItem(tourKey)) return;

    var sidebar = document.getElementById('sidebar');
    var ayudaLink = document.querySelector('a[href*="ayuda"]');
    if (!sidebar || !ayudaLink || typeof window.driver === 'undefined') return;

    var driver = window.driver.js.driver;
    var tour = driver({
        showProgress: true,
        animate: true,
        overlayOpacity: 0.6,
        nextBtnText: 'Siguiente',
        prevBtnText: 'Anterior',
        doneBtnText: 'Entendido',
        steps: @json($tourSteps),
        onDestroyed: function() {
            localStorage.setItem(tourKey, '1');
        }
    });

    setTimeout(function() { tour.drive(); }, 800);
});
</script>
@endif
