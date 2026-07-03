{{-- Sección 2: Información Familiar / Cambios Familiares --}}
@php
    use App\Support\InformacionFamiliarPadres;
    use App\Support\InformacionFamiliarPareja;
    use App\Support\TablaDinamica;
    $esPeriodico = isset($respuestas['vive_con_pareja']) || isset($respuestas['tiene_hijos']) || isset($respuestas['tipo_vivienda']);
    $tienePadres = isset($respuestas['padre_nombre']) || isset($respuestas['madre_nombre']);
    $convive = InformacionFamiliarPadres::normalizarConviveCon($respuestas['convive_con'] ?? null);
    $tieneParejaActual = InformacionFamiliarPareja::tienePareja($respuestas['vive_con_pareja'] ?? null);
    $tablas = $tablas ?? [];
    $filasHijos = $tablas['hijos'] ?? [];
    $tieneHijos = ($respuestas['tiene_hijos'] ?? '') === 'si' || $filasHijos !== [];
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

    @if($tienePadres)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Padres</h6></div>
            <div class="card-body">
                @if($convive !== [])
                    <p class="mb-3"><strong>¿Con quién vive?:</strong>
                        {{ collect($convive)->map(fn ($k) => InformacionFamiliarPadres::CONVIVE_OPCIONES[$k] ?? $k)->implode(', ') }}
                    </p>
                @endif
                <div class="row">
                    @foreach(['padre' => 'Padre', 'madre' => 'Madre'] as $prefijo => $titulo)
                        @if(!empty($respuestas[$prefijo.'_nombre']))
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ $titulo }}</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="fw-bold">Nombre:</td><td>{{ $respuestas[$prefijo.'_nombre'] }}</td></tr>
                                <tr><td class="fw-bold">¿Vive?:</td><td>{{ ($respuestas[$prefijo.'_vive'] ?? '') === 'si' ? 'Sí' : 'No' }}</td></tr>
                                @if(($respuestas[$prefijo.'_vive'] ?? '') === 'si')
                                    <tr><td class="fw-bold">Edad:</td><td>{{ $respuestas[$prefijo.'_edad'] ?? '—' }}</td></tr>
                                    <tr><td class="fw-bold">Teléfono:</td><td>{{ $respuestas[$prefijo.'_telefono'] ?? '—' }}</td></tr>
                                    <tr><td class="fw-bold">Ocupación:</td><td>{{ $respuestas[$prefijo.'_ocupacion'] ?? '—' }}</td></tr>
                                @endif
                            </table>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(isset($respuestas['vive_con_pareja']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Pareja actual</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="fw-bold">¿Tiene pareja actual?:</td>
                        <td>{{ $tieneParejaActual ? 'Sí' : 'No' }}</td>
                    </tr>
                    @if($tieneParejaActual)
                        @if(!empty($respuestas['pareja_tipo_relacion']))
                        <tr>
                            <td class="fw-bold">Tipo de relación:</td>
                            <td>{{ InformacionFamiliarPareja::etiquetaTipo($respuestas['pareja_tipo_relacion']) }}</td>
                        </tr>
                        @endif
                        @if(!empty($respuestas['pareja_nombre']))
                        <tr><td class="fw-bold">Nombre:</td><td>{{ $respuestas['pareja_nombre'] }}</td></tr>
                        @endif
                        @if(!empty($respuestas['pareja_edad']))
                        <tr><td class="fw-bold">Edad:</td><td>{{ $respuestas['pareja_edad'] }}</td></tr>
                        @endif
                        @if(!empty($respuestas['pareja_telefono']))
                        <tr><td class="fw-bold">Teléfono:</td><td>{{ $respuestas['pareja_telefono'] }}</td></tr>
                        @endif
                        @if(!empty($respuestas['pareja_direccion']))
                        <tr><td class="fw-bold">Dirección:</td><td>{{ $respuestas['pareja_direccion'] }}</td></tr>
                        @endif
                        @if(!empty($respuestas['pareja_ocupacion']))
                        <tr><td class="fw-bold">Ocupación:</td><td>{{ $respuestas['pareja_ocupacion'] }}</td></tr>
                        @endif
                        @if(!empty($respuestas['pareja_lugar_trabajo']))
                        <tr><td class="fw-bold">Lugar de trabajo:</td><td>{{ $respuestas['pareja_lugar_trabajo'] }}</td></tr>
                        @endif
                        @if(isset($respuestas['pareja_trabaja']))
                        <tr>
                            <td class="fw-bold">¿Trabaja?:</td>
                            <td>{{ ($respuestas['pareja_trabaja'] ?? '') === 'si' ? 'Sí' : 'No' }}</td>
                        </tr>
                        @endif
                        @if(!empty($respuestas['pareja_tiempo_relacion']))
                        <tr><td class="fw-bold">Tiempo de relación:</td><td>{{ $respuestas['pareja_tiempo_relacion'] }}</td></tr>
                        @endif
                        @if(!empty($respuestas['pareja_calidad_relacion']))
                        <tr>
                            <td class="fw-bold">Calidad de relación:</td>
                            <td>{{ InformacionFamiliarPareja::etiquetaCalidad($respuestas['pareja_calidad_relacion']) }}</td>
                        </tr>
                        @endif
                    @endif
                </table>
            </div>
        </div>
    @endif

    @if(($respuestas['tuvo_matrimonio_union_hijos'] ?? '') === 'si')
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Expareja</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    @if(!empty($respuestas['expareja_nombre']))
                    <tr><td class="fw-bold">Nombre:</td><td>{{ $respuestas['expareja_nombre'] }}</td></tr>
                    @endif
                    @if(!empty($respuestas['expareja_tipo_relacion']))
                    <tr><td class="fw-bold">Tipo:</td><td>{{ \App\Support\InformacionFamiliarExparejas::TIPOS_RELACION[$respuestas['expareja_tipo_relacion']] ?? $respuestas['expareja_tipo_relacion'] }}</td></tr>
                    @endif
                    @if(!empty($respuestas['expareja_tiempo_relacion']))
                    <tr><td class="fw-bold">Tiempo:</td><td>{{ $respuestas['expareja_tiempo_relacion'] }}</td></tr>
                    @endif
                    @if(isset($respuestas['expareja_hijos_comun']))
                    <tr><td class="fw-bold">Hijos en común:</td><td>{{ $respuestas['expareja_hijos_comun'] === 'si' ? 'Sí' : 'No' }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    @endif

    @if($tieneHijos)
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Hijos</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-3">
                    @if(isset($respuestas['tiene_hijos']))
                    <tr>
                        <td class="fw-bold">¿Tiene hijos?:</td>
                        <td>{{ ($respuestas['tiene_hijos'] ?? '') === 'si' ? 'Sí' : 'No' }}</td>
                    </tr>
                    @endif
                    @if(!empty($respuestas['numero_hijos']))
                    <tr><td class="fw-bold">Número de hijos:</td><td>{{ $respuestas['numero_hijos'] }}</td></tr>
                    @endif
                    @if(isset($respuestas['hijos_menores']) && $respuestas['hijos_menores'] !== '')
                    <tr><td class="fw-bold">Hijos menores de edad:</td><td>{{ $respuestas['hijos_menores'] }}</td></tr>
                    @endif
                    @if(isset($respuestas['hijos_dependientes']) && $respuestas['hijos_dependientes'] !== '')
                    <tr><td class="fw-bold">Hijos dependientes:</td><td>{{ $respuestas['hijos_dependientes'] }}</td></tr>
                    @endif
                </table>
                @if($filasHijos !== [])
                    <h6 class="text-muted small text-uppercase mb-2">Detalle de hijos</h6>
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $filasHijos,
                        'columnas' => TablaDinamica::columnasHijos(),
                    ])
                @endif
            </div>
        </div>
    @endif

    @if($esPeriodico === false && isset($cuestionario))
        @php $resumenFamiliar = \App\Support\ResumenFamiliar::compilar($cuestionario); @endphp
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Resumen familiar (informe)</h6></div>
            <div class="card-body small">
                <p class="mb-2"><strong>Convive con:</strong> {{ implode(', ', $resumenFamiliar['convive_con'] ?? []) ?: '—' }}</p>
                @if(($resumenFamiliar['padre']['nombre'] ?? '') !== '')
                    <p class="mb-1"><strong>Padre:</strong> {{ $resumenFamiliar['padre']['nombre'] }} ({{ ($resumenFamiliar['padre']['vive'] ?? '') === 'si' ? 'vive' : 'fallecido' }})</p>
                @endif
                @if(($resumenFamiliar['madre']['nombre'] ?? '') !== '')
                    <p class="mb-1"><strong>Madre:</strong> {{ $resumenFamiliar['madre']['nombre'] }} ({{ ($resumenFamiliar['madre']['vive'] ?? '') === 'si' ? 'vive' : 'fallecido' }})</p>
                @endif
                @if($resumenFamiliar['pareja']['tiene'] ?? false)
                    <p class="mb-1"><strong>Pareja:</strong> {{ $resumenFamiliar['pareja']['nombre'] ?? '—' }} ({{ $resumenFamiliar['pareja']['tipo'] ?? '' }})</p>
                @endif
                @if($resumenFamiliar['expareja']['aplica'] ?? false)
                    <p class="mb-1"><strong>Expareja:</strong> {{ $resumenFamiliar['expareja']['nombre'] ?? '—' }}</p>
                @endif
                @if(!empty($resumenFamiliar['hijos']))
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $resumenFamiliar['hijos'],
                        'columnas' => TablaDinamica::columnasHijos(),
                    ])
                @endif
                @if(!empty($resumenFamiliar['hermanos']))
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $resumenFamiliar['hermanos'],
                        'columnas' => TablaDinamica::columnasHermanos(),
                    ])
                @endif
            </div>
        </div>
    @endif

    @if(!empty($tablas['hermanos']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Hermanos</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['hermanos'],
                    'columnas' => TablaDinamica::columnasHermanos(),
                ])
            </div>
        </div>
    @endif
    
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
                            @if(isset($respuestas['estado_civil_detalle']))
                            <tr>
                                <td class="fw-bold">Estado Civil Detalle:</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $respuestas['estado_civil_detalle'])) }}</td>
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
