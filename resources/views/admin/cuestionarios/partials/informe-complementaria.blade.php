@php
    $filas = $filas ?? [];
    $soloLectura = $soloLectura ?? false;
@endphp

@if($soloLectura)
    @if(!empty($filas))
        <table class="table table-sm table-bordered mb-0">
            @foreach($filas as $fila)
                <tr>
                    <th style="width: 40%;">{{ $fila['pregunta'] ?? '' }}</th>
                    <td>{!! nl2br(e($fila['respuesta'] ?? '')) !!}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="text-muted mb-0">Sin datos registrados.</p>
    @endif
@else
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th style="width: 40%;">Pregunta</th>
                <th>Respuesta (editable)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $index => $fila)
                <tr>
                    <td>
                        <input type="hidden"
                               name="informe_tablas[complementaria][{{ $index }}][pregunta]"
                               value="{{ old("informe_tablas.complementaria.{$index}.pregunta", $fila['pregunta'] ?? '') }}">
                        {{ $fila['pregunta'] ?? '' }}
                    </td>
                    <td>
                        <textarea class="form-control form-control-sm"
                                  name="informe_tablas[complementaria][{{ $index }}][respuesta]"
                                  rows="2">{{ old("informe_tablas.complementaria.{$index}.respuesta", $fila['respuesta'] ?? '') }}</textarea>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-muted">Sin datos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif
