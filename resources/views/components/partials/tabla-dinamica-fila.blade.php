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
    @if($permitirEliminar ?? true)
    <td class="text-center tabla-dinamica-actions" data-label="">
        <button type="button" class="btn btn-outline-danger btn-sm tabla-dinamica-remove" title="{{ $textoEliminar }}">
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
    @endif
</tr>
