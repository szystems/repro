{{-- Requerimientos fijos de la empresa. Solo REPRO. No va al informe ni al portal del cliente. --}}
@php
    $textoRequerimientosRepro = trim((string) ($requerimientosRepro ?? ''));
@endphp
@if(Auth::check() && Auth::user()->role_as >= 2 && $textoRequerimientosRepro !== '')
    <div class="alert alert-warning mb-3 requerimientos-empresa-repro" id="{{ $requerimientosReproId ?? 'aviso-requerimientos-empresa' }}">
        <strong><i class="bi bi-building"></i> Requerimientos de la empresa (solo REPRO)</strong>
        <div class="mt-1" style="white-space: pre-wrap;">{{ $textoRequerimientosRepro }}</div>
    </div>
    <style>
        /* El template pinta .alert-warning en blanco; sobre mostaza no se lee. Azul marino REPRO. */
        .alert.alert-warning.requerimientos-empresa-repro,
        .alert.alert-warning.requerimientos-empresa-repro strong,
        .alert.alert-warning.requerimientos-empresa-repro i,
        .alert.alert-warning.requerimientos-empresa-repro div {
            color: #000555 !important;
        }
    </style>
@endif
