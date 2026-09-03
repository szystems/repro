@extends('layouts.cuestionario')

@section('title', 'Acceso al Cuestionario - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="form-card">
            <div class="form-header">
                {{-- Logo REPRO si existe --}}
                @if(file_exists(public_path('img/logo-repro.png')))
                    <img src="{{ asset('img/logo-repro.png') }}" alt="REPRO" class="logo-repro">
                @endif
                
                @php
                    $tipoFormularioCandidato = \App\Support\CuestionarioPresentacionCandidato::resolverTipo($evaluado ?? null);
                    $tituloNavbarCuestionario = \App\Support\CuestionarioPresentacionCandidato::tituloNavbar($tipoFormularioCandidato);
                @endphp
                <h1>{{ $tituloNavbarCuestionario }}</h1>
                <p>Acceso Seguro con Documento de Identidad</p>
            </div>
            
            <div class="form-content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> Error de Verificación</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif
                
                <div class="mb-4">
                    <h3 class="section-title">
                        <i class="fas fa-id-card"></i> Verificación de Identidad
                    </h3>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instrucciones:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Ingrese su número de DPI (Documento Personal de Identificación)</li>
                            <li>Debe coincidir exactamente con el registrado en el sistema</li>
                            <li>Solo números, sin espacios ni guiones</li>
                        </ul>
                    </div>
                </div>
                
                <form action="{{ route('cuestionario.verificar', $token) }}" method="POST" id="verificacionForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="dpi" class="form-label">
                            <i class="fas fa-id-card"></i> Número de DPI <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('dpi') is-invalid @enderror" 
                               id="dpi" 
                               name="dpi" 
                               value="{{ old('dpi') }}"
                               placeholder="Ejemplo: 1234567890101"
                               maxlength="13"
                               required>
                        <div class="form-text">
                            <i class="fas fa-shield-alt"></i> Su información está protegida y es confidencial
                        </div>
                        @error('dpi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_nacimiento" class="form-label">
                            <i class="fas fa-calendar"></i> Fecha de Nacimiento <span class="required">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                               id="fecha_nacimiento"
                               name="fecha_nacimiento"
                               data-fecha-nacimiento
                               value="{{ old('fecha_nacimiento') }}"
                               placeholder="dd/mm/aaaa"
                               maxlength="10"
                               inputmode="numeric"
                               autocomplete="bday"
                               required>
                        <div class="form-text">
                            Escriba día, mes y año (ej. 10/12/1987)
                        </div>
                        @error('fecha_nacimiento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-key"></i> Verificar Identidad y Acceder
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <div class="alert alert-light">
                        <h6><i class="fas fa-question-circle"></i> ¿Problemas para acceder?</h6>
                        <p class="mb-2">Si no puede acceder al cuestionario, verifique que:</p>
                        <ul class="text-start">
                            <li>Su número de DPI sea correcto (13 dígitos)</li>
                            <li>Su fecha de nacimiento coincida con la registrada</li>
                            <li>El enlace no haya expirado</li>
                        </ul>
                        <p class="mb-0">
                            <small>Para asistencia, contacte a su coordinador de REPRO</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ \App\Support\PublicAsset::url('js/fecha-nacimiento-mask.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dpiInput = document.getElementById('dpi');
    const form = document.getElementById('verificacionForm');
    
    // Validación en tiempo real del DPI
    dpiInput.addEventListener('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/\D/g, '');
        
        // Limitar a 13 dígitos
        if (this.value.length > 13) {
            this.value = this.value.substring(0, 13);
        }
        
        // Validación visual
        const isValid = validateDPI(this.value);
        
        if (this.value.length === 13) {
            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    // Prevenir envío si el DPI no es válido
    form.addEventListener('submit', function(e) {
        cuestionarioHelpers.showLoading();
        
        const dpi = dpiInput.value;
        if (dpi.length !== 13 || !validateDPI(dpi)) {
            e.preventDefault();
            cuestionarioHelpers.hideLoading();
            cuestionarioHelpers.showAlert('Por favor, ingrese un número de DPI válido de 13 dígitos', 'error');
            return false;
        }
    });
    
    // Algoritmo de validación DPI Guatemala
    function validateDPI(dpi) {
        if (dpi.length !== 13) return false;
        
        let suma = 0;
        let multiplicador = 2;
        
        // Calcular dígito verificador
        for (let i = 0; i < 12; i++) {
            suma += parseInt(dpi[i]) * multiplicador;
            multiplicador++;
            if (multiplicador > 9) multiplicador = 2;
        }
        
        const modulo = suma % 11;
        let digitoVerificador = 11 - modulo;
        
        if (digitoVerificador === 11) digitoVerificador = 0;
        if (digitoVerificador === 10) return false;
        
        return digitoVerificador === parseInt(dpi[12]);
    }
});
</script>
@endpush