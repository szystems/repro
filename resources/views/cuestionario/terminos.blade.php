@extends('layouts.cuestionario')

@section('title', 'Autorización y Términos - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-file-contract"></i> Autorización y Términos</h1>
                <p>Lea cuidadosamente y firme para continuar</p>
            </div>

            <div class="form-content">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Información del evaluado --}}
                <div class="alert alert-info">
                    <strong><i class="fas fa-user"></i> {{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong><br>
                    <small>DPI: {{ $evaluado->dpi }} | Empresa: {{ $evaluado->orden->empresa->nombre ?? 'N/A' }}</small>
                </div>

                {{-- Contenido de Términos y Condiciones --}}
                <div class="section-title">
                    <i class="fas fa-scroll"></i> Términos y Condiciones / Autorización General
                </div>

                <div class="border rounded p-3 mb-4" style="max-height: 400px; overflow-y: auto; background: #fafafa;">
                    <h5 class="text-center mb-3">AUTORIZACIÓN PARA EVALUACIÓN</h5>

                    <p>Yo, <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong>, identificado(a) con DPI número <strong>{{ $evaluado->dpi }}</strong>, por medio de la presente autorizo libre y voluntariamente a <strong>REPRO Guatemala</strong> para que realice la siguiente evaluación:</p>

                    <div class="mb-3">
                        <strong>Tipo de evaluación:</strong>
                        <ul>
                            @if($tipoServicio === 'poligrafo')
                                <li><i class="fas fa-check"></i> Evaluación Poligráfica</li>
                            @elseif($tipoServicio === 'vsa')
                                <li><i class="fas fa-check"></i> Evaluación VSA (Voice Stress Analysis)</li>
                            @elseif($tipoServicio === 'socioeconomico')
                                <li><i class="fas fa-check"></i> Estudio Socioeconómico</li>
                            @endif
                        </ul>
                    </div>

                    <p>Declaro que:</p>
                    <ol>
                        <li>Participo de manera <strong>voluntaria</strong> en este proceso de evaluación.</li>
                        <li>He sido informado(a) sobre el procedimiento que se llevará a cabo.</li>
                        <li>Autorizo la recopilación, almacenamiento y procesamiento de mis datos personales exclusivamente para los fines de esta evaluación.</li>
                        <li>Entiendo que los resultados de esta evaluación serán compartidos con la empresa solicitante <strong>{{ $evaluado->orden->empresa->nombre ?? '' }}</strong>.</li>
                        <li>Comprendo que puedo retirarme del proceso en cualquier momento antes de la finalización de la evaluación.</li>
                        <li>La información que proporcionaré es verídica y correcta según mi mejor conocimiento.</li>
                        <li>Autorizo el uso de medios digitales (firma electrónica) como constancia de mi aceptación.</li>
                    </ol>

                    @if($tipoServicio === 'poligrafo' || $tipoServicio === 'vsa')
                    <div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
                        <strong><i class="fas fa-exclamation-triangle text-warning"></i> Consentimiento adicional para evaluación {{ $tipoServicio === 'poligrafo' ? 'poligráfica' : 'VSA' }}:</strong>
                        <p class="mb-0 mt-1">Autorizo que se me realice una evaluación mediante {{ $tipoServicio === 'poligrafo' ? 'polígrafo (detector de verdad)' : 'análisis de estrés de voz (VSA)' }}. Declaro que me encuentro en pleno uso de mis facultades mentales y no me encuentro bajo efectos de sustancias que alteren mi estado de conciencia. Confirmo que no tengo impedimento médico alguno para realizar este examen.</p>
                    </div>
                    @endif

                    <p class="mt-3"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
                    <p><strong>Lugar:</strong> Guatemala</p>
                </div>

                {{-- Formulario de aceptación --}}
                <form action="{{ route('cuestionario.aceptar-terminos', $token) }}" method="POST" id="terminosForm">
                    @csrf

                    <input type="hidden" name="tipo_proceso" value="{{ $tipoServicio }}">

                    {{-- Checkbox de aceptación --}}
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('acepta_terminos') is-invalid @enderror"
                               type="checkbox" id="acepta_terminos" name="acepta_terminos" value="1" required>
                        <label class="form-check-label" for="acepta_terminos">
                            <strong>He leído, entiendo y acepto los términos y condiciones anteriores. Autorizo la realización de la evaluación.</strong>
                        </label>
                        @error('acepta_terminos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="btnAceptar" disabled>
                            <i class="fas fa-check-circle"></i> Acepto y Continuar al Cuestionario
                        </button>
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
    const btnAceptar = document.getElementById('btnAceptar');
    const checkTerminos = document.getElementById('acepta_terminos');

    function toggleSubmit() {
        btnAceptar.disabled = !checkTerminos.checked;
    }

    checkTerminos.addEventListener('change', toggleSubmit);
});
</script>
@endpush
