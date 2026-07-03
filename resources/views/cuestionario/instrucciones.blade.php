@extends('layouts.cuestionario')

@section('title', 'Instrucciones - Cuestionario REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-info-circle"></i> {{ config('cuestionario_instrucciones.titulo') }}</h1>
                <p>Lea cuidadosamente antes de iniciar el cuestionario</p>
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
                    <small>DPI: {{ $evaluado->dpi }} | Empresa: {{ $evaluado->orden->empresa->nombre ?? 'N/A' }}</small>
                </div>

                <p class="mb-4">{{ config('cuestionario_instrucciones.intro') }}</p>

                <div class="border rounded p-3 mb-4" style="max-height: 420px; overflow-y: auto; background: #fafafa;">
                    <ul class="mb-0 ps-3">
                        @foreach(config('cuestionario_instrucciones.puntos') as $punto)
                            <li class="mb-2">{{ $punto }}</li>
                        @endforeach
                    </ul>
                </div>

                <form action="{{ route('cuestionario.aceptar-instrucciones', ['token' => $token]) }}" method="POST" id="formInstrucciones">
                    @csrf

                    <div class="form-check mb-4">
                        <input class="form-check-input @error('acepta_instrucciones') is-invalid @enderror"
                               type="checkbox"
                               id="acepta_instrucciones"
                               name="acepta_instrucciones"
                               value="1"
                               required>
                        <label class="form-check-label" for="acepta_instrucciones">
                            {{ config('cuestionario_instrucciones.boton') }}
                        </label>
                        @error('acepta_instrucciones')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnContinuarInstrucciones" disabled>
                            Continuar <i class="fas fa-arrow-right"></i>
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
    const check = document.getElementById('acepta_instrucciones');
    const btn = document.getElementById('btnContinuarInstrucciones');
    if (check && btn) {
        check.addEventListener('change', function() {
            btn.disabled = !check.checked;
        });
    }
});
</script>
@endpush
