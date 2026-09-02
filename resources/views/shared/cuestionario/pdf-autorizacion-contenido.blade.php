{{-- Contenido de autorización legal + firmas (PDF cuestionario separado, ago 2026) --}}
@php
    $evaluado = $cuestionario->evaluadoOrden;
@endphp

@if($cuestionario->acepta_terminos)
    <div class="seccion autorizacion-documento">
        <div class="seccion-titulo">Autorización y Términos</div>
        <div class="autorizacion-cuerpo">
            @if($cuestionario->texto_autorizacion_html)
                {!! $cuestionario->texto_autorizacion_html !!}
            @else
                @include('cuestionario.partials.autorizacion-legal', [
                    'evaluado' => $evaluado,
                    'contenidoAutorizacion' => \App\Support\AutorizacionesLegales::renderHtml($evaluado),
                ])
            @endif
        </div>

        @if($cuestionario->firma_digital)
            <div class="firma-container">
                <img src="{{ $cuestionario->firma_digital }}" alt="Firma Digital" class="firma-imagen">
                <div class="firma-texto">
                    <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong><br>
                    DPI: {{ $evaluado->dpi }}<br>
                    Firmado digitalmente el {{ $cuestionario->completado_at ? $cuestionario->completado_at->format('d/m/Y \a \l\a\s H:i:s') : ($cuestionario->acepta_terminos_at ? $cuestionario->acepta_terminos_at->format('d/m/Y \a \l\a\s H:i:s') : 'N/A') }}
                </div>
            </div>
        @endif

        @if($evaluado->responsable)
            <div class="firma-responsable">
                <div class="firma-responsable-linea">
                    <div class="firma-responsable-nombre">{{ $evaluado->responsable->name }}</div>
                    @if($evaluado->responsable->cargo)
                        <div class="firma-responsable-cargo">{{ $evaluado->responsable->cargo }}</div>
                    @endif
                    <div class="firma-responsable-rol">Responsable del Proceso — REPRO Guatemala</div>
                </div>
            </div>
        @endif
    </div>

    @if($cuestionario->acepta_infornet && $cuestionario->texto_infornet_html)
        <div class="seccion autorizacion-documento">
            <div class="seccion-titulo">Autorización Infornet</div>
            <div class="autorizacion-cuerpo">
                {!! $cuestionario->texto_infornet_html !!}
            </div>
            @if($cuestionario->acepta_infornet_at)
                <p class="infornet-fecha">
                    Aceptada el {{ $cuestionario->acepta_infornet_at->format('d/m/Y H:i:s') }}.
                </p>
            @endif
            @if($cuestionario->firma_digital)
                <div class="firma-container">
                    <img src="{{ $cuestionario->firma_digital }}" alt="Firma Digital" class="firma-imagen">
                    <div class="firma-texto">
                        <strong>{{ $evaluado->nombre }} {{ $evaluado->apellidos }}</strong><br>
                        Misma firma de la autorización principal
                    </div>
                </div>
            @endif
        </div>
    @endif
@elseif($cuestionario->firma_digital)
    <div class="seccion autorizacion-documento">
        <div class="seccion-titulo">Firma Digital</div>
        <div class="firma-container">
            <img src="{{ $cuestionario->firma_digital }}" alt="Firma Digital" class="firma-imagen">
            <div class="firma-texto">
                Firmado digitalmente el {{ $cuestionario->completado_at ? $cuestionario->completado_at->format('d/m/Y \a \l\a\s H:i:s') : 'N/A' }}
            </div>
        </div>
    </div>
@else
    <p class="texto-vacio">El candidato aún no ha aceptado los términos ni registrado firma digital.</p>
@endif
