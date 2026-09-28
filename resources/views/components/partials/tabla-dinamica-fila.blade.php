@php
    $valor = fn (string $key) => old("{$name}.{$index}.{$key}", $fila[$key] ?? '');
@endphp

<tr class="tabla-dinamica-row" data-index="{{ $index }}">
    @foreach($columnas as $col)
        <td data-label="{{ $col['label'] }}{{ ($col['required'] ?? false) ? ' *' : '' }}">
            @include('components.partials.tabla-dinamica-campo', [
                'name' => $name,
                'index' => $index,
                'col' => $col,
                'valor' => $valor($col['key']),
            ])
        </td>
    @endforeach
    @if(($permitirEliminar ?? true) || ($permitirReordenar ?? false))
    <td class="text-center tabla-dinamica-actions" data-label="">
        <div class="tabla-dinamica-actions-inner">
            @if($permitirReordenar ?? false)
                <button type="button" class="btn btn-outline-secondary btn-sm tabla-dinamica-move" data-direccion="-1" title="Subir" aria-label="Subir">
                    <i class="fas fa-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm tabla-dinamica-move" data-direccion="1" title="Bajar" aria-label="Bajar">
                    <i class="fas fa-arrow-down"></i>
                </button>
            @endif
            @if($permitirEliminar ?? true)
                <button type="button" class="btn btn-outline-danger btn-sm tabla-dinamica-remove" title="{{ $textoEliminar }}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            @endif
        </div>
    </td>
    @endif
</tr>
