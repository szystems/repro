{{-- Sección 2: Información Familiar / Cambios Familiares --}}
@php
    // Determinar si es formulario periódico con cambios familiares
    $esPeriodico = isset($respuestas['vive_con_pareja']) || isset($respuestas['tiene_hijos']) || isset($respuestas['tipo_vivienda']);
@endphp
<div class="section-content">
    @if($completada)
        <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle-fill"></i> Sección completada
        </div>
    @else
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i> Sección pendiente o incompleta
        </div>
    @endif
    
    <h5 class="section-title mb-4">
        <i class="bi bi-people"></i> {{ $nombreSeccion ?? 'Información Familiar' }}
    </h5>
    
    @if($esPeriodico)
        {{-- Vista para formulario periódico --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Situación Familiar</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['vive_con_pareja']))
                            <tr>
                                <td class="fw-bold">¿Vive con pareja?:</td>
                                <td>{{ $respuestas['vive_con_pareja'] == '1' || $respuestas['vive_con_pareja'] === 'si' ? 'Sí' : 'No' }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['pareja_trabaja']))
                            <tr>
                                <td class="fw-bold">¿Pareja trabaja?:</td>
                                <td>{{ $respuestas['pareja_trabaja'] == '1' || $respuestas['pareja_trabaja'] === 'si' ? 'Sí' : 'No' }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['estado_civil_detalle']))
                            <tr>
                                <td class="fw-bold">Estado Civil Detalle:</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $respuestas['estado_civil_detalle'])) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['tiene_hijos']))
                            <tr>
                                <td class="fw-bold">¿Tiene hijos?:</td>
                                <td>{{ $respuestas['tiene_hijos'] == '1' || $respuestas['tiene_hijos'] === 'si' ? 'Sí' : 'No' }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['numero_hijos']))
                            <tr>
                                <td class="fw-bold">Número de hijos:</td>
                                <td>{{ $respuestas['numero_hijos'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['hijos_menores']))
                            <tr>
                                <td class="fw-bold">Hijos menores de edad:</td>
                                <td>{{ $respuestas['hijos_menores'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['hijos_dependientes']))
                            <tr>
                                <td class="fw-bold">Hijos dependientes:</td>
                                <td>{{ $respuestas['hijos_dependientes'] }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Situación de Vivienda</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['tipo_vivienda']))
                            <tr>
                                <td class="fw-bold">Tipo de vivienda:</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $respuestas['tipo_vivienda'])) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['personas_hogar']))
                            <tr>
                                <td class="fw-bold">Personas en el hogar:</td>
                                <td>{{ $respuestas['personas_hogar'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['dependientes_economicos']))
                            <tr>
                                <td class="fw-bold">Dependientes económicos:</td>
                                <td>{{ $respuestas['dependientes_economicos'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['personas_contribuyen_gastos']))
                            <tr>
                                <td class="fw-bold">Personas que contribuyen:</td>
                                <td>{{ $respuestas['personas_contribuyen_gastos'] }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['monto_alquiler']) && $respuestas['monto_alquiler'])
                            <tr>
                                <td class="fw-bold">Monto de alquiler:</td>
                                <td>Q{{ number_format($respuestas['monto_alquiler'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['monto_hipoteca']) && $respuestas['monto_hipoteca'])
                            <tr>
                                <td class="fw-bold">Monto de hipoteca:</td>
                                <td>Q{{ number_format($respuestas['monto_hipoteca'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['anos_restantes_hipoteca']) && $respuestas['anos_restantes_hipoteca'])
                            <tr>
                                <td class="fw-bold">Años restantes hipoteca:</td>
                                <td>{{ $respuestas['anos_restantes_hipoteca'] }} años</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($respuestas['observaciones_familiares']) && $respuestas['observaciones_familiares'])
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Observaciones Familiares</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{!! nl2br(e($respuestas['observaciones_familiares'])) !!}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        {{-- Vista para formulario preempleo: Educación y Formación --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Nivel Educativo</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Nivel más alto:</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $respuestas['nivel_educativo'] ?? 'No especificado')) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Especialidad/Carrera:</td>
                                <td>{{ $respuestas['especialidad'] ?? 'No especificada' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Institución:</td>
                                <td>{{ $respuestas['institucion_educativa'] ?? 'No especificada' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Año de graduación:</td>
                                <td>{{ $respuestas['anio_graduacion'] ?? 'No especificado' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Información Adicional</h6>
                    </div>
                    <div class="card-body">
                        @if(count($respuestas) > 0)
                            <table class="table table-borderless">
                                @foreach($respuestas as $campo => $valor)
                                    @if(!in_array($campo, ['nivel_educativo', 'especialidad', 'institucion_educativa', 'anio_graduacion']))
                                    <tr>
                                        <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $campo)) }}:</td>
                                        <td>{{ is_array($valor) ? json_encode($valor) : $valor }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </table>
                        @else
                            <p class="text-muted">No hay información adicional</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
