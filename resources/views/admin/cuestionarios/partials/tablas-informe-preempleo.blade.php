{{-- E3.2 — Tablas del informe Pre-empleo (solo REPRO/ADMIN) --}}
@if(($informePreempleoActivo ?? false) && ($puedeGestionarInformePreempleo ?? false))
@php
    $soloLectura = $soloLectura ?? false;
    $tablasInforme = $tablasInforme ?? [];
    $overridesInforme = $overridesInforme ?? [];
@endphp
<div class="card mt-4 border-success">
    <div class="card-header bg-success bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Tablas para informe
            <span class="badge bg-success ms-2">Solo REPRO</span>
        </h6>
        <small class="text-muted d-block mt-1">
            Datos compilados desde las respuestas del candidato. Puede editarlos antes de generar el informe final.
        </small>
    </div>
    <div class="card-body">
        <div class="accordion" id="accordionTablasInforme">
            @foreach(\App\Support\InformePreempleo::CLAVES_TABLAS as $clave => $titulo)
                @php
                    $datos = $tablasInforme[$clave] ?? [];
                    $editado = in_array($clave, $overridesInforme, true);
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="informeHeading{{ $clave }}">
                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#informeCollapse{{ $clave }}">
                            {{ $titulo }}
                            @if($editado)
                                <span class="badge bg-warning text-dark ms-2">Editado</span>
                            @endif
                        </button>
                    </h2>
                    <div id="informeCollapse{{ $clave }}"
                         class="accordion-collapse collapse"
                         data-bs-parent="#accordionTablasInforme">
                        <div class="accordion-body">
                            @if(!$soloLectura)
                                <div class="form-check mb-3">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="informe_tablas_restaurar[{{ $clave }}]"
                                           value="1"
                                           id="restaurar_{{ $clave }}">
                                    <label class="form-check-label text-muted" for="restaurar_{{ $clave }}">
                                        Restaurar desde cuestionario (descartar ediciones del evaluador)
                                    </label>
                                </div>
                            @endif

                            @if($clave === 'familiar')
                                @include('admin.cuestionarios.partials.informe-familiar', [
                                    'datos' => $datos,
                                    'soloLectura' => $soloLectura,
                                ])
                            @elseif($clave === 'complementaria')
                                @include('admin.cuestionarios.partials.informe-complementaria', [
                                    'filas' => is_array($datos) ? $datos : [],
                                    'soloLectura' => $soloLectura,
                                ])
                            @else
                                @php
                                    $columnasMap = [
                                        'academico' => \App\Support\TablaDinamica::columnasFormacionAcademica(),
                                        'laboral' => \App\Support\TablaDinamica::columnasEmpleos(),
                                        'deudas' => \App\Support\TablaDinamica::columnasDeudas(),
                                    ];
                                    $columnas = $columnasMap[$clave] ?? [];
                                    $filas = is_array($datos) ? $datos : [];
                                @endphp
                                @if($soloLectura)
                                    @if(!empty($filas))
                                        @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                                            'filas' => $filas,
                                            'columnas' => $columnas,
                                        ])
                                    @else
                                        <p class="text-muted mb-0">Sin datos registrados.</p>
                                    @endif
                                @else
                                    @include('admin.cuestionarios.partials.informe-tabla-editable', [
                                        'clave' => $clave,
                                        'filas' => $filas,
                                        'columnas' => $columnas,
                                    ])
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
