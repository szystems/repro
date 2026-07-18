@extends('layouts.cuestionario')

@section('title', 'Esperando información - REPRO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-hourglass-half"></i> Proceso en preparación</h1>
            </div>
            <div class="form-content">
                <div class="alert alert-warning">
                    <p class="mb-2">Su evaluación requiere que REPRO registre el <strong>motivo o hecho de la evaluación</strong> antes de que pueda firmar la autorización.</p>
                    <p class="mb-0">Por favor contacte a REPRO o a la empresa solicitante. Una vez registrado el dato, podrá continuar con el mismo enlace.</p>
                </div>
                <p class="text-muted small mb-0">
                    Evaluado: {{ $evaluado->nombre }} {{ $evaluado->apellidos }} · DPI {{ $evaluado->dpi }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
