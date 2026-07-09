{{-- Sección 5: Antecedentes y Referencias --}}
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
        <i class="bi bi-file-earmark-text"></i> {{ $nombreSeccion ?? 'Antecedentes y Referencias' }}
    </h5>
    
    @if(count($respuestas) > 0 || !empty($tablas))
        @if(!($soloEmpresa ?? false))
        <div class="row">
            {{-- Antecedentes Legales y Laborales --}}
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-shield-check"></i> Antecedentes</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['antecedentes_penales']))
                            <tr>
                                <td class="fw-bold">Antecedentes penales:</td>
                                <td>
                                    @if($respuestas['antecedentes_penales'] == 'si' || $respuestas['antecedentes_penales'] == '1')
                                        <span class="badge bg-danger">Sí</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['despedido_trabajo']))
                            <tr>
                                <td class="fw-bold">¿Ha sido despedido?:</td>
                                <td>
                                    @if($respuestas['despedido_trabajo'] == 'si' || $respuestas['despedido_trabajo'] == '1')
                                        <span class="badge bg-warning">Sí</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['motivo_despido']) && $respuestas['motivo_despido'])
                            <tr>
                                <td class="fw-bold">Motivo del despido:</td>
                                <td>{!! nl2br(e($respuestas['motivo_despido'])) !!}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['detalle_antecedentes']) && $respuestas['detalle_antecedentes'])
                            <tr>
                                <td class="fw-bold">Detalle antecedentes:</td>
                                <td>{!! nl2br(e($respuestas['detalle_antecedentes'])) !!}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Salud y Hábitos --}}
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-heart-pulse"></i> Salud y Hábitos</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['problemas_salud_mental']))
                            <tr>
                                <td class="fw-bold">Problemas de salud mental:</td>
                                <td>
                                    @if($respuestas['problemas_salud_mental'] == 'si' || $respuestas['problemas_salud_mental'] == '1')
                                        <span class="badge bg-warning">Sí</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['detalle_salud_mental']) && $respuestas['detalle_salud_mental'])
                            <tr>
                                <td class="fw-bold">Detalle salud mental:</td>
                                <td>{!! nl2br(e($respuestas['detalle_salud_mental'])) !!}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['consume_alcohol']))
                            <tr>
                                <td class="fw-bold">Consumo de alcohol:</td>
                                <td>
                                    @php
                                        $alcohol = $respuestas['consume_alcohol'];
                                        $badgeClass = match($alcohol) {
                                            'nunca' => 'bg-success',
                                            'ocasionalmente' => 'bg-info',
                                            'frecuentemente' => 'bg-warning',
                                            'diariamente' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($alcohol) }}</span>
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['consume_drogas']))
                            <tr>
                                <td class="fw-bold">Consumo de drogas:</td>
                                <td>
                                    @php
                                        $drogas = $respuestas['consume_drogas'];
                                        $badgeClass = match($drogas) {
                                            'nunca' => 'bg-success',
                                            'ocasionalmente' => 'bg-warning',
                                            'frecuentemente', 'diariamente' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($drogas) }}</span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Referencias Personales --}}
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-people"></i> Referencias Personales</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Relación</th>
                                <th>Teléfono</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $tieneReferencias = false; @endphp
                            @for($i = 1; $i <= 3; $i++)
                                @php
                                    // Buscar en múltiples formatos de campo
                                    $nombre = $respuestas["referencia{$i}_nombre"] ?? $respuestas["referencia_{$i}_nombre"] ?? $respuestas["ref_personal_{$i}_nombre"] ?? null;
                                    $relacion = $respuestas["referencia{$i}_relacion"] ?? $respuestas["referencia_{$i}_relacion"] ?? $respuestas["ref_personal_{$i}_relacion"] ?? null;
                                    $telefono = $respuestas["referencia{$i}_telefono"] ?? $respuestas["referencia_{$i}_telefono"] ?? $respuestas["ref_personal_{$i}_telefono"] ?? null;
                                @endphp
                                @if($nombre || $relacion || $telefono)
                                    @php $tieneReferencias = true; @endphp
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $nombre ?? '-' }}</td>
                                        <td>{{ ucfirst($relacion ?? '-') }}</td>
                                        <td>{{ $telefono ?? '-' }}</td>
                                    </tr>
                                @endif
                            @endfor
                            @if(!$tieneReferencias)
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay referencias registradas</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        @php
            $tablas = $tablas ?? [];
            $compKeys = array_column(\App\Support\InformacionComplementaria::PREGUNTAS, 'key');
            $tieneComplementaria = collect($compKeys)->contains(fn ($k) => !empty($respuestas[$k] ?? null));
        @endphp

        @if(!empty($tablas['tatuajes']))
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Tatuajes</h6></div>
                <div class="card-body">
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $tablas['tatuajes'],
                        'columnas' => \App\Support\TablaDinamica::columnasTatuajes(),
                    ])
                </div>
            </div>
        @endif

        @if(!empty($tablas['perforaciones']))
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Perforaciones</h6></div>
                <div class="card-body">
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $tablas['perforaciones'],
                        'columnas' => \App\Support\TablaDinamica::columnasPerforaciones(),
                    ])
                </div>
            </div>
        @endif

        @if($tieneComplementaria)
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Información complementaria (informe)</h6></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        @foreach(\App\Support\InformacionComplementaria::PREGUNTAS as $p)
                            @if(!empty($respuestas[$p['key']] ?? null))
                            <tr>
                                <td class="fw-bold" style="width: 35%;">{{ $p['label'] }}</td>
                                <td>{!! nl2br(e($respuestas[$p['key']])) !!}</td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            </div>
        @endif

        @if(!empty($respuestas['informacion_adicional_final']))
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-chat-left-dots"></i> Información adicional del candidato</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($respuestas['informacion_adicional_final'])) !!}</p>
            </div>
        </div>
        @endif

        {{-- Observaciones adicionales --}}
        @if(isset($respuestas['observaciones_adicionales']) && $respuestas['observaciones_adicionales'])
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-chat-text"></i> Observaciones Adicionales</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($respuestas['observaciones_adicionales'])) !!}</p>
            </div>
        </div>
        @endif
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay información visible registrada en esta sección.
        </div>
    @endif
</div>
