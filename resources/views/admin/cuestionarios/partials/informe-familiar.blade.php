@php
    $datos = is_array($datos ?? null) ? $datos : [];
    $soloLectura = $soloLectura ?? false;
    $tipoFormulario = $tipoFormulario ?? 'preempleo';
    $incluyeHermanos = \App\Support\InformePreempleo::incluyeHermanos($tipoFormulario);
    $convive = $datos['convive_con'] ?? [];
    $conviveTexto = is_array($convive) ? implode(', ', $convive) : (string) $convive;
    $camposProgenitor = [
        'nombre' => 'Nombre',
        'vive' => '¿Vive? (si/no)',
        'edad' => 'Edad',
        'telefono' => 'Teléfono',
        'direccion' => 'Dirección',
        'ocupacion' => 'Ocupación',
    ];
@endphp

@if($soloLectura)
    <div class="small">
        <p class="mb-2"><strong>Convive con:</strong> {{ $conviveTexto ?: '—' }}</p>
        @foreach(['padre' => 'Padre', 'madre' => 'Madre'] as $prefijo => $etiqueta)
            @if(!empty($datos[$prefijo]['nombre'] ?? null))
                <p class="mb-1">
                    <strong>{{ $etiqueta }}:</strong>
                    {{ $datos[$prefijo]['nombre'] }}
                    @if(isset($datos[$prefijo]['vive']))
                        ({{ ($datos[$prefijo]['vive'] ?? '') === 'si' ? 'vive' : 'fallecido' }})
                    @endif
                    @if(!empty($datos[$prefijo]['direccion'] ?? null))
                        — {{ $datos[$prefijo]['direccion'] }}
                    @endif
                    @if(!empty($datos[$prefijo]['ocupacion'] ?? null))
                        — {{ $datos[$prefijo]['ocupacion'] }}
                    @endif
                </p>
            @endif
        @endforeach
        @if($datos['pareja']['tiene'] ?? false)
            <p class="mb-1"><strong>Pareja:</strong> {{ $datos['pareja']['nombre'] ?? '—' }} ({{ $datos['pareja']['tipo'] ?? '' }})</p>
        @endif
        @if($datos['expareja']['aplica'] ?? false)
            <p class="mb-1"><strong>Expareja:</strong> {{ $datos['expareja']['nombre'] ?? '—' }}</p>
        @endif
        @if(!empty($datos['hijos']))
            @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                'filas' => $datos['hijos'],
                'columnas' => \App\Support\TablaDinamica::columnasHijos(),
            ])
        @endif
        @if($incluyeHermanos && !empty($datos['hermanos']))
            @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                'filas' => $datos['hermanos'],
                'columnas' => \App\Support\TablaDinamica::columnasHermanos(),
            ])
        @endif
    </div>
@else
    <div class="row g-2 mb-3">
        <div class="col-12">
            <label class="form-label">Convive con (separado por comas)</label>
            <input type="text"
                   class="form-control form-control-sm"
                   name="informe_tablas[familiar][convive_con]"
                   value="{{ old('informe_tablas.familiar.convive_con', $conviveTexto) }}">
        </div>
    </div>

    @foreach(['padre' => 'Padre', 'madre' => 'Madre'] as $prefijo => $etiqueta)
        <fieldset class="border rounded p-2 mb-3">
            <legend class="float-none w-auto px-2 fs-6">{{ $etiqueta }}</legend>
            <div class="row g-2">
                @foreach($camposProgenitor as $campo => $label)
                    <div class="col-md-4">
                        <label class="form-label small">{{ $label }}</label>
                        <input type="text"
                               class="form-control form-control-sm"
                               name="informe_tablas[familiar][{{ $prefijo }}][{{ $campo }}]"
                               value="{{ old("informe_tablas.familiar.{$prefijo}.{$campo}", $datos[$prefijo][$campo] ?? '') }}">
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endforeach

    <fieldset class="border rounded p-2 mb-3">
        <legend class="float-none w-auto px-2 fs-6">Pareja actual</legend>
        <div class="row g-2">
            @foreach(['tipo' => 'Tipo relación', 'nombre' => 'Nombre', 'edad' => 'Edad', 'telefono' => 'Teléfono', 'direccion' => 'Dirección', 'ocupacion' => 'Ocupación', 'tiempo_relacion' => 'Tiempo de relación', 'calidad_relacion' => 'Estado de la relación'] as $campo => $label)
                <div class="col-md-4">
                    <label class="form-label small">{{ $label }}</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           name="informe_tablas[familiar][pareja][{{ $campo }}]"
                           value="{{ old("informe_tablas.familiar.pareja.{$campo}", $datos['pareja'][$campo] ?? '') }}">
                </div>
            @endforeach
            <div class="col-md-4">
                <label class="form-label small">Tiene pareja (si/no)</label>
                <input type="text"
                       class="form-control form-control-sm"
                       name="informe_tablas[familiar][pareja][tiene]"
                       value="{{ old('informe_tablas.familiar.pareja.tiene', ($datos['pareja']['tiene'] ?? false) ? 'si' : 'no') }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="border rounded p-2 mb-3">
        <legend class="float-none w-auto px-2 fs-6">Expareja / unión anterior</legend>
        <div class="row g-2">
            @foreach(['nombre' => 'Nombre', 'tipo' => 'Tipo', 'tiempo_relacion' => 'Tiempo de relación', 'hijos_comun' => 'Hijos en común', 'cantidad_hijos' => 'Cantidad de hijos', 'problemas_legales' => 'Problemas legales'] as $campo => $label)
                <div class="col-md-6">
                    <label class="form-label small">{{ $label }}</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           name="informe_tablas[familiar][expareja][{{ $campo }}]"
                           value="{{ old("informe_tablas.familiar.expareja.{$campo}", $datos['expareja'][$campo] ?? '') }}">
                </div>
            @endforeach
            <div class="col-md-4">
                <label class="form-label small">Aplica (si/no)</label>
                <input type="text"
                       class="form-control form-control-sm"
                       name="informe_tablas[familiar][expareja][aplica]"
                       value="{{ old('informe_tablas.familiar.expareja.aplica', ($datos['expareja']['aplica'] ?? false) ? 'si' : 'no') }}">
            </div>
        </div>
    </fieldset>

    @php
        $tablasFamiliares = ['hijos' => \App\Support\TablaDinamica::columnasHijos()];
        if ($incluyeHermanos) {
            $tablasFamiliares['hermanos'] = \App\Support\TablaDinamica::columnasHermanos();
        }
    @endphp
    @foreach($tablasFamiliares as $tablaClave => $columnasTabla)
        <h6 class="mt-3">{{ $tablaClave === 'hijos' ? 'Hijos' : 'Hermanos' }}</h6>
        <x-tabla-dinamica
            :name="'informe_tablas[familiar]['.$tablaClave.']'"
            :columnas="$columnasTabla"
            :filas="$datos[$tablaClave] ?? []"
            :minFilas="0"
            :titulo="null"
            :permitirAgregar="true"
            :permitirEliminar="true"
            :textoAgregar="$tablaClave === 'hijos' ? 'Agregar hijo' : 'Agregar hermano'"
        />
    @endforeach
@endif
