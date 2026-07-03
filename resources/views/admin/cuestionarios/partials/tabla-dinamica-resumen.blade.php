{{-- Resumen de tabla dinámica (admin / PDF) --}}
@props([
    'filas' => [],
    'columnas' => [],
    'tableClass' => 'table table-sm table-striped mb-0',
])

@if(count($filas) > 0)
    <div class="table-responsive">
        <table class="{{ $tableClass }}">
            <thead>
                <tr>
                    @foreach($columnas as $col)
                        <th>{{ $col['label'] ?? $col['key'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($filas as $fila)
                    <tr>
                        @foreach($columnas as $col)
                            @php
                                $valor = $fila[$col['key']] ?? '';
                                if (($col['key'] ?? '') === 'vive_con_candidato' && $valor !== '') {
                                    $valor = $valor === 'si' ? 'Sí' : ($valor === 'no' ? 'No' : $valor);
                                }
                            @endphp
                            <td>{{ $valor !== '' ? $valor : '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted mb-0">Sin filas registradas.</p>
@endif
