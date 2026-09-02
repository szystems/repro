@props(['tipo' => 'info', 'titulo' => null, 'contenido' => ''])
@php
    $classes = [
        'info' => 'alert-info',
        'warning' => 'alert-warning',
        'success' => 'alert-success',
        'repro' => 'alert-primary',
        'cliente' => 'alert-secondary',
    ];
    $icons = [
        'info' => 'bi-info-circle',
        'warning' => 'bi-exclamation-triangle',
        'success' => 'bi-check-circle',
        'repro' => 'bi-shield-check',
        'cliente' => 'bi-building',
    ];
@endphp
<div class="alert {{ $classes[$tipo] ?? 'alert-info' }} ayuda-callout">
    <i class="bi {{ $icons[$tipo] ?? 'bi-info-circle' }} me-2"></i>
    @if($titulo)<strong>{{ $titulo }}</strong> @endif
    {!! $contenido !!}
</div>
