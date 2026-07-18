@extends('layouts.cuestionario')

@section('title', 'Autorización Infornet - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-file-signature"></i> Autorización Infornet</h1>
                <p>Documento adicional requerido para procesos de pre-empleo</p>
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

                <div class="alert alert-info">
                    <strong><i class="fas fa-user"></i> {{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong><br>
                    <small>DPI: {{ $evaluado->dpi }}</small>
                </div>

                <div class="border rounded p-3 mb-4" style="max-height: 400px; overflow-y: auto; background: #fafafa;">
                    {!! $contenidoInfornet !!}
                    <p class="mt-3 mb-0"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
                </div>

                @if($cuestionario->firma_digital)
                    <div class="mb-3 text-center">
                        <p class="small text-muted mb-2">Firma registrada en la autorización principal:</p>
                        <img src="{{ $cuestionario->firma_digital }}" alt="Firma" class="img-fluid" style="max-height: 120px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                @endif

                <form action="{{ route('cuestionario.aceptar-infornet', $token) }}" method="POST">
                    @csrf
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('acepta_infornet') is-invalid @enderror"
                               type="checkbox" id="acepta_infornet" name="acepta_infornet" value="1" required>
                        <label class="form-check-label" for="acepta_infornet">
                            <strong>He leído y acepto la autorización Infornet. Confirmo que la firma mostrada corresponde a mi consentimiento.</strong>
                        </label>
                        @error('acepta_infornet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-check-circle"></i> Aceptar y Continuar al Cuestionario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
