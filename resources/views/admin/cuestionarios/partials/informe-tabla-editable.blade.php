@php
    $clave = $clave ?? 'tabla';
    $namePrefix = $namePrefix ?? "informe_tablas[{$clave}]";
    $filas = $filas ?? [];
    $columnas = $columnas ?? [];
@endphp

<div class="table-responsive">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                @foreach($columnas as $columna)
                    <th>{{ $columna['label'] ?? $columna['key'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $index => $fila)
                <tr>
                    @foreach($columnas as $columna)
                        @php
                            $key = $columna['key'];
                            $valor = old(str_replace(['[', ']'], ['.', ''], "{$namePrefix}.{$index}.{$key}"), $fila[$key] ?? '');
                        @endphp
                        <td>
                            <input type="text"
                                   class="form-control form-control-sm"
                                   name="{{ $namePrefix }}[{{ $index }}][{{ $key }}]"
                                   value="{{ $valor }}">
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columnas) ?: 1 }}" class="text-muted">Sin filas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
