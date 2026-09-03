@extends('layouts.cuestionario')

@section('title', 'Sección ' . $numeroSeccion . ' - Cuestionario REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-{{ $iconoSeccion }}"></i> {{ $tituloSeccion }}</h1>
                <p>Complete la información solicitada en esta sección</p>
            </div>
            
            <div class="progress-container">
                <div class="progress">
                    <div class="progress-bar" 
                         style="width: {{ ($numeroSeccion / $totalSecciones) * 100 }}%">
                    </div>
                </div>
                <div class="progress-text">
                    Progreso: {{ $numeroSeccion }} de {{ $totalSecciones }} secciones completadas
                </div>
                
                {{-- Navegación por secciones --}}
                <div class="section-nav">
                    <div class="d-flex flex-wrap justify-content-center">
                        @for($i = 1; $i <= $totalSecciones; $i++)
                            @php
                                $esSeccionActual = $i == $numeroSeccion;
                                $puedeAcceder = $i <= $numeroSeccion;
                            @endphp
                            
                            @if($puedeAcceder)
                                <a href="{{ route('cuestionario.seccion', ['token' => $token, 'numero' => $i]) }}" 
                                   class="nav-link {{ $esSeccionActual ? 'active' : '' }}">
                                    <i class="fas fa-{{ $i == 1 ? 'user' : ($i == 2 ? 'users' : ($i == 3 ? 'briefcase' : ($i == 4 ? 'dollar-sign' : 'shield-alt'))) }}"></i>
                                    {{ $nombresSecciones[$i] ?? 'Sección ' . $i }}
                                </a>
                            @else
                                <span class="nav-link text-muted">
                                    <i class="fas fa-lock"></i>
                                    {{ $nombresSecciones[$i] ?? 'Sección ' . $i }}
                                </span>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
            
            <div class="form-content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> <strong>Errores en el formulario</strong></h6>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>{{ session('success') }}</strong>
                    </div>
                @endif
                
                <form action="{{ route('cuestionario.guardar-seccion', ['token' => $token, 'numero' => $numeroSeccion]) }}" 
                      method="POST" 
                      id="cuestionarioForm"
                      novalidate
                      data-autosave-url="{{ route('cuestionario.autosave-seccion', ['token' => $token, 'numero' => $numeroSeccion]) }}"
                      @if($numeroSeccion === 1) enctype="multipart/form-data" @endif>
                    @csrf
                    <input type="hidden" name="action" id="formAction"
                           value="{{ $numeroSeccion < $totalSecciones ? 'siguiente' : 'finalizar' }}">
                    
                    <div class="section-title">
                        <i class="fas fa-{{ $iconoSeccion }}"></i>
                        <span>{{ $tituloSeccion }}</span>
                    </div>
                    <div id="autosaveStatus" class="autosave-status small text-muted mb-3" aria-live="polite"></div>
                    
                    {{-- Aquí se incluirá el contenido específico de cada sección --}}
                    @php
                        $vistaSeccion = $vistaSeccion ?? match ($numeroSeccion) {
                            1 => 'cuestionario.secciones.datos-personales',
                            2 => 'cuestionario.secciones.informacion-familiar',
                            3 => 'cuestionario.secciones.historial-laboral',
                            4 => 'cuestionario.secciones.situacion-economica',
                            5 => 'cuestionario.secciones.antecedentes',
                            default => 'cuestionario.secciones.generica',
                        };
                    @endphp
                    @include($vistaSeccion)
                    
                    <div class="navigation-buttons">
                        <div class="d-flex gap-2">
                            @if($numeroSeccion > 1)
                                <a href="{{ route('cuestionario.seccion', ['token' => $token, 'numero' => $numeroSeccion - 1]) }}" 
                                   class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Anterior
                                </a>
                            @endif
                            
                            <button type="submit" class="btn btn-secondary" data-set-action="borrador">
                                <i class="fas fa-save"></i> Guardar Borrador
                            </button>
                        </div>
                        
                        <div class="d-flex gap-2">
                            @if($numeroSeccion < $totalSecciones)
                                <button type="submit" class="btn btn-primary" data-set-action="siguiente">
                                    Guardar y Continuar <i class="fas fa-arrow-right"></i>
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary" data-set-action="finalizar">
                                    <i class="fas fa-check"></i> Finalizar Cuestionario
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cuestionarioForm');
    const formAction = document.getElementById('formAction');

    if (form && formAction) {
        form.querySelectorAll('[data-set-action]').forEach(function(button) {
            button.addEventListener('click', function() {
                formAction.value = this.dataset.setAction;
            });
        });
    }
    
    // Prevenir salida accidental
    let formChanged = false;
    const inputs = form ? form.querySelectorAll('input, select, textarea') : [];
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de salir? Los cambios no guardados se perderán.';
        }
    });
    
    if (form) {
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    }
});
</script>
<script src="{{ \App\Support\PublicAsset::url('js/cuestionario-autosave.js') }}"></script>
@include('cuestionario.partials.resaltar-errores-validacion')
@endpush