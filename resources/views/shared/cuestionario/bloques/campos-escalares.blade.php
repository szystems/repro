{{-- Campos escalares en modo lectura --}}
@props(['campos' => [], 'respuestas' => []])

@php
    $visibles = collect($campos)->filter(fn ($c) => array_key_exists($c['key'], $respuestas) && ($respuestas[$c['key']] ?? '') !== '');
@endphp

@if($visibles->isNotEmpty())
    <div class="card mb-3">
        <div class="card-body">
            <table class="table table-sm table-borderless mb-0">
                @foreach($visibles as $campo)
                    @php
                        $valor = $respuestas[$campo['key']];
                        if (in_array(strtolower((string) $valor), ['si', 'sí', '1'], true)) {
                            $valorMostrar = 'Sí';
                        } elseif (in_array(strtolower((string) $valor), ['no', '0'], true)) {
                            $valorMostrar = 'No';
                        } elseif (is_numeric($valor) && str_contains($campo['key'], 'salario')) {
                            $valorMostrar = 'Q' . number_format((float) $valor, 2);
                        } else {
                            $valorMostrar = $valor;
                        }
                    @endphp
                    <tr>
                        <td class="fw-bold" style="width: 35%;">{{ $campo['label'] }}:</td>
                        <td>{!! is_string($valorMostrar) ? nl2br(e($valorMostrar)) : e($valorMostrar) !!}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endif
