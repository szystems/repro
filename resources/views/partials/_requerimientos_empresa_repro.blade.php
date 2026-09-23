{{-- Requerimientos fijos de la empresa. Solo REPRO. No va al informe ni al portal del cliente. --}}
@php
    $textoRequerimientosRepro = trim((string) ($requerimientosRepro ?? ''));
@endphp
@if(Auth::check() && Auth::user()->role_as >= 2 && $textoRequerimientosRepro !== '')
    <div class="alert alert-warning mb-3" id="{{ $requerimientosReproId ?? 'aviso-requerimientos-empresa' }}">
        <strong><i class="bi bi-building"></i> Requerimientos de la empresa (solo REPRO)</strong>
        <div class="mt-1" style="white-space: pre-wrap;">{{ $textoRequerimientosRepro }}</div>
    </div>
@endif
