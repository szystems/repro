{{-- Sección 4: Situación Económica --}}
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
        <i class="bi bi-currency-dollar"></i> {{ $nombreSeccion ?? 'Situación Económica' }}
    </h5>
    
    @if(count($respuestas) > 0)
        <div class="row">
            {{-- Ingresos --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Ingresos</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['ingresos_principales']) && $respuestas['ingresos_principales'])
                            <tr>
                                <td class="fw-bold">Ingresos principales:</td>
                                <td class="text-success fw-bold">Q{{ number_format($respuestas['ingresos_principales'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['ingresos_adicionales']) && $respuestas['ingresos_adicionales'])
                            <tr>
                                <td class="fw-bold">Ingresos adicionales:</td>
                                <td class="text-success">Q{{ number_format($respuestas['ingresos_adicionales'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['ingresos_familiares']) && $respuestas['ingresos_familiares'])
                            <tr>
                                <td class="fw-bold">Ingresos familiares:</td>
                                <td class="text-success">Q{{ number_format($respuestas['ingresos_familiares'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['total_ingresos']) && $respuestas['total_ingresos'])
                            <tr class="border-top">
                                <td class="fw-bold fs-5">TOTAL INGRESOS:</td>
                                <td class="text-success fw-bold fs-5">Q{{ number_format($respuestas['total_ingresos'], 2) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Gastos --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-down-circle"></i> Gastos</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['gastos_vivienda']) && $respuestas['gastos_vivienda'])
                            <tr>
                                <td class="fw-bold">Vivienda:</td>
                                <td class="text-danger">Q{{ number_format($respuestas['gastos_vivienda'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['gastos_alimentacion']) && $respuestas['gastos_alimentacion'])
                            <tr>
                                <td class="fw-bold">Alimentación:</td>
                                <td class="text-danger">Q{{ number_format($respuestas['gastos_alimentacion'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['gastos_transporte']) && $respuestas['gastos_transporte'])
                            <tr>
                                <td class="fw-bold">Transporte:</td>
                                <td class="text-danger">Q{{ number_format($respuestas['gastos_transporte'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['gastos_educacion']) && $respuestas['gastos_educacion'])
                            <tr>
                                <td class="fw-bold">Educación:</td>
                                <td class="text-danger">Q{{ number_format($respuestas['gastos_educacion'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['gastos_salud']) && $respuestas['gastos_salud'])
                            <tr>
                                <td class="fw-bold">Salud:</td>
                                <td class="text-danger">Q{{ number_format($respuestas['gastos_salud'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['gastos_otros']) && $respuestas['gastos_otros'])
                            <tr>
                                <td class="fw-bold">Otros gastos:</td>
                                <td class="text-danger">Q{{ number_format($respuestas['gastos_otros'], 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($respuestas['total_gastos']) && $respuestas['total_gastos'])
                            <tr class="border-top">
                                <td class="fw-bold fs-5">TOTAL GASTOS:</td>
                                <td class="text-danger fw-bold fs-5">Q{{ number_format($respuestas['total_gastos'], 2) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Balance y Deudas --}}
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Balance Mensual</h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $balance = $respuestas['balance_mensual'] ?? 0;
                        @endphp
                        <h2 class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                            Q{{ number_format($balance, 2) }}
                        </h2>
                        <p class="mb-0 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                            @if($balance > 0)
                                <i class="bi bi-arrow-up"></i> Superávit mensual
                            @elseif($balance < 0)
                                <i class="bi bi-arrow-down"></i> Déficit mensual
                            @else
                                <i class="bi bi-dash"></i> Equilibrado
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-wallet2"></i> Situación Financiera</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            @if(isset($respuestas['tiene_ahorros']))
                            <tr>
                                <td class="fw-bold">¿Tiene ahorros?:</td>
                                <td>
                                    @if($respuestas['tiene_ahorros'] == '1' || $respuestas['tiene_ahorros'] === 'si')
                                        <span class="badge bg-success">Sí</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['tiene_deudas']))
                            <tr>
                                <td class="fw-bold">¿Tiene deudas?:</td>
                                <td>
                                    @if($respuestas['tiene_deudas'] == '1' || $respuestas['tiene_deudas'] === 'si')
                                        <span class="badge bg-warning">Sí</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @if(isset($respuestas['detalle_deudas']) && $respuestas['detalle_deudas'])
                            <tr>
                                <td class="fw-bold">Detalle de deudas:</td>
                                <td>{!! nl2br(e($respuestas['detalle_deudas'])) !!}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($respuestas['observaciones_economicas']) && $respuestas['observaciones_economicas'])
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Observaciones Económicas</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{!! nl2br(e($respuestas['observaciones_economicas'])) !!}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @php $tablas = $tablas ?? []; @endphp
        @if(!empty($tablas['deudas']))
            <div class="card mt-3">
                <div class="card-header"><h6 class="mb-0">Detalle de deudas</h6></div>
                <div class="card-body">
                    @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                        'filas' => $tablas['deudas'],
                        'columnas' => \App\Support\TablaDinamica::columnasDeudas(),
                    ])
                </div>
            </div>
        @endif
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay información económica registrada en esta sección.
        </div>
    @endif
</div>
