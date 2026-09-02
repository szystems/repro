@props([
    'name',
    'titulo' => '',
    'columnas' => [],
    'filas' => [],
    'minFilas' => 0,
    'textoAgregar' => 'Agregar fila',
    'textoEliminar' => 'Eliminar',
    'textoVacio' => 'No hay filas. Use el botón para agregar.',
    'permitirAgregar' => true,
    'permitirEliminar' => true,
])

@php
    $filasRender = old($name, $filas ?? []);
    if (! is_array($filasRender)) {
        $filasRender = [];
    }
    if (count($filasRender) === 0 && ($minFilas ?? 0) > 0) {
        $filasRender = [[]];
    }
    $columnasJson = json_encode($columnas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<div class="tabla-dinamica-wrapper mb-4"
     data-tabla-dinamica
     data-name="{{ $name }}"
     data-columnas='{!! $columnasJson !!}'
     data-min-filas="{{ (int) $minFilas }}"
     data-texto-agregar="{{ $textoAgregar }}"
     data-texto-eliminar="{{ $textoEliminar }}"
     data-permitir-agregar="{{ $permitirAgregar ? '1' : '0' }}"
     data-permitir-eliminar="{{ $permitirEliminar ? '1' : '0' }}"
     @if($name === 'formacion_academica') data-formacion-academica="1" @endif>

    @if($titulo)
        <div class="section-subtitle mb-2">
            <i class="fas fa-table"></i> {{ $titulo }}
        </div>
    @endif

    @if(count($columnas) >= 6)
        <p class="text-muted small tabla-dinamica-scroll-hint mb-2 d-none d-sm-block">
            <i class="fas fa-arrows-alt-h"></i> Deslice horizontalmente para ver todas las columnas.
        </p>
    @endif

    {{-- Los errores por fila se muestran junto a su campo; aquí solo el error de la tabla. --}}
    @error($name)
        <div class="alert alert-danger py-2">{{ $message }}</div>
    @enderror

    <div class="table-responsive">
        <table class="table table-bordered table-sm tabla-dinamica-table align-middle mb-2">
            <thead class="table-light">
                <tr>
                    @foreach($columnas as $col)
                        <th>
                            {{ $col['label'] }}
                            @if($col['required'] ?? false)<span class="required">*</span>@endif
                        </th>
                    @endforeach
                    @if($permitirEliminar)
                    <th style="width: 90px;" class="text-center">Acción</th>
                    @endif
                </tr>
            </thead>
            <tbody class="tabla-dinamica-body">
                @foreach($filasRender as $index => $fila)
                    @include('components.partials.tabla-dinamica-fila', [
                        'name' => $name,
                        'columnas' => $columnas,
                        'fila' => is_array($fila) ? $fila : [],
                        'index' => $index,
                        'textoEliminar' => $textoEliminar,
                        'permitirEliminar' => $permitirEliminar,
                    ])
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-muted small tabla-dinamica-empty {{ count($filasRender) > 0 ? 'd-none' : '' }}">{{ $textoVacio }}</p>

    @if($permitirAgregar)
    <button type="button" class="btn btn-outline-primary btn-sm tabla-dinamica-add">
        <i class="fas fa-plus"></i> {{ $textoAgregar }}
    </button>
    @endif
</div>

@once
    @push('styles')
        <style>
            .tabla-dinamica-wrapper .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                max-width: 100%;
            }

            .tabla-dinamica-table {
                width: max-content;
                min-width: 100%;
            }

            .tabla-dinamica-table th {
                white-space: nowrap;
            }

            .tabla-dinamica-table .form-control-sm {
                min-width: 5.5rem;
            }

            .tabla-dinamica-remove:disabled {
                opacity: 0.45;
                cursor: not-allowed;
            }

            /* Selectores mes/año: mismo comportamiento en Android, iOS y escritorio. */
            .fechas-laboradas-selects {
                display: flex;
                gap: 0.35rem;
            }

            .fechas-laboradas-selects select {
                min-width: 0;
                flex: 1 1 50%;
            }

            .fechas-laboradas-range select:disabled {
                background-color: #e9ecef;
                cursor: not-allowed;
            }

            .fechas-laboradas-range {
                min-width: 13rem;
            }

            /* Móvil: misma fila/campos del DOM, presentación tipo tarjeta (un solo guardado) */
            @media (max-width: 767.98px) {
                .tabla-dinamica-scroll-hint {
                    display: none !important;
                }

                .tabla-dinamica-wrapper .table-responsive {
                    overflow-x: visible;
                }

                .tabla-dinamica-table thead {
                    display: none;
                }

                .tabla-dinamica-table,
                .tabla-dinamica-table tbody,
                .tabla-dinamica-table tr,
                .tabla-dinamica-table td {
                    display: block;
                    width: 100%;
                }

                .tabla-dinamica-row {
                    margin-bottom: 1rem;
                    padding: 1rem;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    background: #fff;
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
                }

                .tabla-dinamica-row td {
                    border: none !important;
                    padding: 0.25rem 0 0.75rem;
                }

                .tabla-dinamica-row td:not(.tabla-dinamica-actions)::before {
                    content: attr(data-label);
                    display: block;
                    font-weight: 600;
                    font-size: 0.8rem;
                    color: var(--repro-blue, #000555);
                    margin-bottom: 0.35rem;
                }

                .tabla-dinamica-row td.tabla-dinamica-actions {
                    padding-top: 0.5rem;
                    text-align: left;
                }

                .tabla-dinamica-row .tabla-dinamica-remove {
                    width: 100%;
                }

                .tabla-dinamica-row .tabla-dinamica-remove:disabled {
                    opacity: 0.45;
                    cursor: not-allowed;
                }

                .tabla-dinamica-table .form-control-sm {
                    min-width: 0;
                    width: 100%;
                }
            }
        </style>
    @endpush
    @push('scripts')
        <script src="{{ asset('js/tabla-dinamica.js') }}?v={{ filemtime(public_path('js/tabla-dinamica.js')) }}"></script>
    @endpush
@endonce
