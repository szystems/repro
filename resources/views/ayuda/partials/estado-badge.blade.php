@php
    $map = [
        'pendiente' => ['icon' => 'bi-hourglass', 'class' => 'bg-warning text-dark', 'label' => 'Pendiente'],
        'link_enviado' => ['icon' => 'bi-send', 'class' => 'bg-info text-dark', 'label' => 'Link Enviado'],
        'recibido' => ['icon' => 'bi-check-circle', 'class' => 'bg-success', 'label' => 'Recibido'],
        'vencido' => ['icon' => 'bi-x-circle', 'class' => 'bg-danger', 'label' => 'Vencido'],
        'en_proceso' => ['icon' => 'bi-arrow-repeat', 'class' => 'bg-primary', 'label' => 'En Proceso'],
        'programado' => ['icon' => 'bi-calendar-check', 'class' => 'bg-secondary', 'label' => 'Programado'],
    ];
    $cfg = $map[$tipo] ?? ['icon' => 'bi-circle', 'class' => 'bg-secondary', 'label' => $tipo];
@endphp
<span class="badge {{ $cfg['class'] }}"><i class="bi {{ $cfg['icon'] }} me-1"></i>{{ $cfg['label'] }}</span>
