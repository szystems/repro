{{-- Sección 3: Historial Laboral / Situación Laboral Actual --}}
@php
    // Determinar si es formulario periódico con situación laboral actual
    $esPeriodico = isset($respuestas['situacion_laboral_actual']) || isset($respuestas['empresa_actual']) || isset($respuestas['puesto_actual']);
@endphp
<div class="section-content">
    @if(!($ocultarEstadoSeccion ?? false))
    @if($completada)
        <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> Sección completada
        </div>
    @else
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i> Sección pendiente o incompleta
        </div>
    @endif
    @endif
    
    <h5 class="section-title mb-4">
        <i class="bi bi-briefcase"></i> {{ $nombreSeccion ?? 'Historial Laboral' }}
    </h5>
    
    @if($esPeriodico)
        {{-- Vista para formulario periódico --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Situación Laboral Actual</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['situacion_laboral_actual']))
                            <tr>
                                <td class="fw-bold">Situación:</td>
                                <td>
                                    <span class="badge 
                                        @if($respuestas['situacion_laboral_actual'] == 'empleado') bg-success
                                        @elseif($respuestas['situacion_laboral_actual'] == 'desempleado') bg-danger
                                        @elseif($respuestas['situacion_laboral_actual'] == 'independiente') bg-info
                                        @else bg-secondary
                                        @endif
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $respuestas['situacion_laboral_actual'])) }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['empresa_actual']) && $respuestas['empresa_actual'])
                            <tr>
                                <td class="fw-bold">Empresa actual:</td>
                                <td>{{ $respuestas['empresa_actual'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['puesto_actual']) && $respuestas['puesto_actual'])
                            <tr>
                                <td class="fw-bold">Puesto actual:</td>
                                <td>{{ $respuestas['puesto_actual'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['fecha_inicio_actual']) && $respuestas['fecha_inicio_actual'])
                            <tr>
                                <td class="fw-bold">Fecha de inicio:</td>
                                <td>{{ $respuestas['fecha_inicio_actual'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['jefe_inmediato']) && $respuestas['jefe_inmediato'])
                            <tr>
                                <td class="fw-bold">Jefe inmediato:</td>
                                <td>{{ $respuestas['jefe_inmediato'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['tipo_negocio']) && $respuestas['tipo_negocio'])
                            <tr>
                                <td class="fw-bold">Tipo de negocio:</td>
                                <td>{{ $respuestas['tipo_negocio'] }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Información Económica Laboral</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['salario_actual']) && $respuestas['salario_actual'])
                            <tr>
                                <td class="fw-bold">Salario actual:</td>
                                <td class="text-success fw-bold">Q{{ number_format($respuestas['salario_actual'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['ingresos_mensuales']) && $respuestas['ingresos_mensuales'])
                            <tr>
                                <td class="fw-bold">Ingresos mensuales:</td>
                                <td class="text-success">Q{{ number_format($respuestas['ingresos_mensuales'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['anos_experiencia_laboral']))
                            <tr>
                                <td class="fw-bold">Años de experiencia:</td>
                                <td>{{ $respuestas['anos_experiencia_laboral'] }} años</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['empleos_anteriores']))
                            <tr>
                                <td class="fw-bold">Empleos anteriores:</td>
                                <td>{{ $respuestas['empleos_anteriores'] }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($respuestas['motivo_busqueda']) && $respuestas['motivo_busqueda'])
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Motivo de Búsqueda de Empleo</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{!! nl2br(e($respuestas['motivo_busqueda'])) !!}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        {{-- Vista para formulario preempleo: Historial de Empleos --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">{{ $respuestas['anios_experiencia_total'] ?? '0' }}</h3>
                        <p class="mb-0">Años de experiencia total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info">{{ $respuestas['numero_empleos'] ?? '0' }}</h3>
                        <p class="mb-0">Empleos anteriores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">{{ isset($respuestas['salario_actual']) ? 'Q' . number_format($respuestas['salario_actual'], 2) : 'N/A' }}</h3>
                        <p class="mb-0">Salario actual/esperado</p>
                    </div>
                </div>
            </div>
        </div>
        
        @php $tablas = $tablas ?? []; @endphp

        @if(!empty($tablas['formacion_academica']))
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Formación académica</h6></div>
                <div class="card-body">
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $tablas['formacion_academica'],
                        'columnas' => \App\Support\TablaDinamica::columnasFormacionAcademica(),
                    ])
                </div>
            </div>
        @endif

        @if(!empty($tablas['empleos']))
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Historial de empleos</h6></div>
                <div class="card-body">
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $tablas['empleos'],
                        'columnas' => \App\Support\TablaDinamica::columnasEmpleos(),
                    ])
                </div>
            </div>
        @endif

        @if(count($respuestas) > 0 && empty($tablas['formacion_academica']) && empty($tablas['empleos']))
            <div class="card">
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        @foreach($respuestas as $campo => $valor)
                            @if(!str_starts_with($campo, 'integridad_'))
                            <tr>
                                <td class="fw-bold" style="width: 30%;">{{ ucfirst(str_replace('_', ' ', $campo)) }}:</td>
                                <td>{{ is_array($valor) ? json_encode($valor) : $valor }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
