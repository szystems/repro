{{-- Lista de preguntas/respuestas en modo lectura --}}
@props(['bloques' => [], 'respuestas' => [], 'inicioNumeracion' => 1])

@foreach($bloques as $bloque)
    @php
        $preguntasVisibles = collect($bloque['preguntas'] ?? [])
            ->filter(fn ($p) => array_key_exists($p['key'], $respuestas) && ($respuestas[$p['key']] ?? '') !== '')
            ->values();
    @endphp

    @if($preguntasVisibles->isNotEmpty() || !empty($bloque['mostrar_vacio']))
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">{{ $bloque['titulo'] }}</h6>
                @if(!empty($bloque['badge']))
                    <span class="badge bg-secondary mt-1">{{ $bloque['badge'] }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($preguntasVisibles->isEmpty())
                    <p class="text-muted mb-0">Sin respuestas registradas.</p>
                @else
                    <table class="table table-sm table-borderless mb-0">
                        @foreach($preguntasVisibles as $i => $pregunta)
                            <tr class="border-bottom">
                                <td class="fw-bold align-top" style="width: 35%;">
                                    {{ ($inicioNumeracion + $i) }}. {{ $pregunta['label'] }}
                                </td>
                                <td class="align-top">{!! nl2br(e($respuestas[$pregunta['key']])) !!}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </div>
    @endif
@endforeach
