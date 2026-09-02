{{--
    Formulario completado por el candidato — referencia secundaria para la empresa.
    Los informes oficiales están en _informes_evaluado_empresa.blade.php
--}}
@php
    $cuestionarioModel = $evaluado->cuestionario;
    $secciones = $cuestionarioModel && method_exists($cuestionarioModel, 'getSeccionesConfig')
        ? $cuestionarioModel->getSeccionesConfig()
        : [];
@endphp

<details class="border rounded mb-3">
    <summary class="px-3 py-2 bg-light fw-medium user-select-none">
        <i class="bi bi-ui-checks text-secondary"></i> Formulario del candidato (solo referencia)
    </summary>
    <div class="p-3 border-top">
        <p class="text-muted small">
            Respuestas que el evaluado ingresó en línea. El informe oficial de REPRO aparece arriba.
        </p>
        <div class="d-flex gap-2 flex-wrap mb-3">
            @if($cuestionarioModel)
            <a href="{{ route('empresa.cuestionarios.pdf-autorizacion', $evaluado) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-file-earmark-check"></i> PDF autorización
            </a>
            @endif
        </div>

        @if($cuestionarioModel && $secciones)
        <ul class="nav nav-tabs" id="seccionesTabs-{{ $evaluado->id }}" role="tablist">
            @foreach($secciones as $i => $nombreSeccion)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="seccion{{ $evaluado->id }}-{{ $i }}-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#seccion{{ $evaluado->id }}-{{ $i }}"
                            type="button"
                            role="tab"
                            title="{{ $nombreSeccion }}">
                        {{ $i }}. {{ $nombreSeccion }}
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content mt-3" id="seccionesTabContent-{{ $evaluado->id }}">
            @foreach($secciones as $i => $nombreSeccion)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                     id="seccion{{ $evaluado->id }}-{{ $i }}"
                     role="tabpanel">
                    @include('shared.cuestionario.seccion-lectura', [
                        'cuestionario' => $cuestionarioModel,
                        'numeroSeccion' => $i,
                        'nombreSeccion' => $nombreSeccion,
                    ])
                </div>
            @endforeach
        </div>
        @endif
    </div>
</details>
