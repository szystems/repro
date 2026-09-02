@php
    use App\Support\FechasLaboradasCampo;

    $errorKey = "{$name}.{$index}.{$col['key']}";
    $esRequerido = (bool) ($col['required'] ?? false);

    $parsed = FechasLaboradasCampo::parse($valor);

    $claveInicio = "{$name}.{$index}.{$col['key']}".FechasLaboradasCampo::SUFIJO_INICIO;
    $claveFin = "{$name}.{$index}.{$col['key']}".FechasLaboradasCampo::SUFIJO_FIN;
    $claveActual = "{$name}.{$index}.{$col['key']}".FechasLaboradasCampo::SUFIJO_ACTUAL;

    $actualVal = old($claveActual, $parsed['actual'] ? '1' : '');

    $inicioPartes = FechasLaboradasCampo::partes(old($claveInicio, $parsed['inicio']));
    $finPartes = FechasLaboradasCampo::partes(old($claveFin, $parsed['fin']));

    $idBase = str_replace(['[', ']'], ['_', ''], "{$name}[{$index}][{$col['key']}]");
    $invalido = $errors->has($errorKey);

    $campoBase = "{$name}[{$index}][{$col['key']}";

    $extremos = [
        [
            'etiqueta' => 'Desde (mes y año)',
            'nombreMes' => $campoBase.FechasLaboradasCampo::SUFIJO_INICIO.FechasLaboradasCampo::SUFIJO_MES.']',
            'nombreAnio' => $campoBase.FechasLaboradasCampo::SUFIJO_INICIO.FechasLaboradasCampo::SUFIJO_ANIO.']',
            'id' => $idBase.'_inicio',
            'marcador' => 'inicio',
            'mes' => old($claveInicio.FechasLaboradasCampo::SUFIJO_MES, $inicioPartes['mes']),
            'anio' => old($claveInicio.FechasLaboradasCampo::SUFIJO_ANIO, $inicioPartes['anio']),
            'requerido' => $esRequerido,
            'deshabilitado' => false,
        ],
        [
            'etiqueta' => 'Hasta (mes y año)',
            'nombreMes' => $campoBase.FechasLaboradasCampo::SUFIJO_FIN.FechasLaboradasCampo::SUFIJO_MES.']',
            'nombreAnio' => $campoBase.FechasLaboradasCampo::SUFIJO_FIN.FechasLaboradasCampo::SUFIJO_ANIO.']',
            'id' => $idBase.'_fin',
            'marcador' => 'fin',
            'mes' => old($claveFin.FechasLaboradasCampo::SUFIJO_MES, $finPartes['mes']),
            'anio' => old($claveFin.FechasLaboradasCampo::SUFIJO_ANIO, $finPartes['anio']),
            'requerido' => $esRequerido && ! $actualVal,
            'deshabilitado' => (bool) $actualVal,
        ],
    ];
@endphp

<div class="fechas-laboradas-range" data-fechas-laboradas-range>
    @foreach($extremos as $extremo)
        <div class="fechas-laboradas-extremo mb-1">
            <label class="form-label form-label-sm mb-0 text-muted" for="{{ $extremo['id'] }}_mes">
                {{ $extremo['etiqueta'] }}
            </label>
            <div class="fechas-laboradas-selects">
                <select class="form-control form-control-sm @if($invalido) is-invalid @endif"
                        id="{{ $extremo['id'] }}_mes"
                        name="{{ $extremo['nombreMes'] }}"
                        aria-label="{{ $extremo['etiqueta'] }} — mes"
                        data-fechas-{{ $extremo['marcador'] }}
                        @if($extremo['requerido']) required @endif
                        @if($extremo['deshabilitado']) disabled @endif>
                    <option value="">Mes</option>
                    @foreach(FechasLaboradasCampo::MESES as $mesVal => $mesLabel)
                        <option value="{{ $mesVal }}" @selected((string) $extremo['mes'] === (string) $mesVal)>{{ $mesLabel }}</option>
                    @endforeach
                </select>
                <select class="form-control form-control-sm @if($invalido) is-invalid @endif"
                        id="{{ $extremo['id'] }}_anio"
                        name="{{ $extremo['nombreAnio'] }}"
                        aria-label="{{ $extremo['etiqueta'] }} — año"
                        data-fechas-{{ $extremo['marcador'] }}
                        @if($extremo['requerido']) required @endif
                        @if($extremo['deshabilitado']) disabled @endif>
                    <option value="">Año</option>
                    @foreach(FechasLaboradasCampo::anios() as $anioVal)
                        <option value="{{ $anioVal }}" @selected((string) $extremo['anio'] === (string) $anioVal)>{{ $anioVal }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endforeach

    <div class="form-check form-check-sm mt-1">
        <input class="form-check-input"
               type="checkbox"
               id="{{ $idBase }}_actual"
               name="{{ $name }}[{{ $index }}][{{ $col['key'] }}{{ FechasLaboradasCampo::SUFIJO_ACTUAL }}]"
               value="1"
               data-fechas-actual
               @checked($actualVal)>
        <label class="form-check-label small" for="{{ $idBase }}_actual">Sigue laborando</label>
    </div>
</div>
