@php
    use App\Support\SocioeconomicoComplementariaCampos;
    use App\Support\TablaDinamica;

    $respuestas = $respuestasExistentes ?? [];
    $tablas = $tablasExistentes ?? [];
    $empleosHistorial = $empleosHistorial ?? [];
    $presupuestoExistente = SocioeconomicoComplementariaCampos::filasPresupuestoIniciales(
        old('presupuesto', $tablas['presupuesto'] ?? [])
    );
@endphp

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Complete las referencias, bienes, gastos e información de vivienda según el formulario original.
    <br><small class="text-muted">Los totales de bienes y gastos se calculan automáticamente.</small>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">
    <i class="fas fa-users"></i> {{ SocioeconomicoComplementariaCampos::TITULO_REFERENCIAS_FAMILIARES }}
</h6>
<x-tabla-dinamica
    name="referencias_familiares"
    titulo="Referencias familiares (mínimo 3)"
    :columnas="TablaDinamica::columnasReferenciasFamiliares()"
    :filas="$tablas['referencias_familiares'] ?? []"
    :min-filas="3"
    texto-agregar="Agregar referencia familiar"
/>

<h6 class="text-primary border-bottom pb-2 mb-3 mt-4">
    <i class="fas fa-user-friends"></i> REFERENCIAS PERSONALES: (que no sean familiares)
</h6>
<x-tabla-dinamica
    name="referencias_personales"
    titulo="Referencias personales (mínimo 3)"
    :columnas="TablaDinamica::columnasReferenciasPersonales()"
    :filas="$tablas['referencias_personales'] ?? []"
    :min-filas="3"
    texto-agregar="Agregar referencia personal"
/>

<x-tabla-dinamica
    name="referencias_vecinales"
    titulo="Referencias vecinales (mínimo 1)"
    :columnas="TablaDinamica::columnasReferenciasVecinales()"
    :filas="$tablas['referencias_vecinales'] ?? []"
    :min-filas="1"
    texto-agregar="Agregar referencia vecinal"
/>

<div class="d-flex justify-content-between align-items-center mb-2 mt-3">
    <h6 class="mb-0 text-muted">Referencias laborales (opcional)</h6>
    @if(count($empleosHistorial) > 0)
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnImportarEmpleos" data-empleos='@json($empleosHistorial)'>
            <i class="fas fa-download"></i> Importar del historial laboral
        </button>
    @endif
</div>
<x-tabla-dinamica
    name="referencias_laborales"
    titulo=""
    :columnas="TablaDinamica::columnasReferenciasLaborales()"
    :filas="$tablas['referencias_laborales'] ?? []"
    :min-filas="0"
    texto-agregar="Agregar referencia laboral"
/>

<h6 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-box"></i> Bienes y pertenencias</h6>
<div class="socio-total-wrapper" data-total-field="valor" data-display-id="bienes_total_display" data-hidden-id="bienes_total">
<x-tabla-dinamica
    name="bienes"
    titulo="Liste bienes de valor (vehículos, electrodomésticos, joyas, etc.)"
    :columnas="TablaDinamica::columnasBienes()"
    :filas="$tablas['bienes'] ?? []"
    :min-filas="0"
    texto-agregar="Agregar bien"
/>
</div>
<div class="row mb-4">
    <div class="col-md-4 ms-auto">
        <label class="form-label fw-bold">Total estimado de bienes (Q.)</label>
        <input type="text" class="form-control" id="bienes_total_display" readonly
               value="{{ number_format((float) ($respuestas['bienes_total'] ?? 0), 2) }}">
        <input type="hidden" name="bienes_total" id="bienes_total" value="{{ $respuestas['bienes_total'] ?? '0' }}">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3">{{ SocioeconomicoComplementariaCampos::TITULO_GASTOS }}</h6>
<div id="presupuesto_fijo_wrapper" class="socio-total-wrapper" data-total-field="monto" data-display-id="presupuesto_total_display" data-hidden-id="presupuesto_total">
    <div class="table-responsive">
        <table class="table table-bordered" id="presupuesto_fijo_table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th style="width: 180px;">Monto (Q.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($presupuestoExistente as $i => $fila)
                    <tr>
                        <td>
                            {{ $fila['concepto'] }}
                            <input type="hidden" name="presupuesto[{{ $i }}][concepto]" value="{{ $fila['concepto'] }}">
                        </td>
                        <td>
                            <input type="number" class="form-control presupuesto-monto @error('presupuesto.'.$i.'.monto') is-invalid @enderror"
                                   name="presupuesto[{{ $i }}][monto]" min="0" step="0.01"
                                   value="{{ old('presupuesto.'.$i.'.monto', $fila['monto'] ?? '') }}" required>
                            @error('presupuesto.'.$i.'.monto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-4 ms-auto">
        <label class="form-label fw-bold">Total mensual de gastos (Q.)</label>
        <input type="text" class="form-control" id="presupuesto_total_display" readonly
               value="{{ number_format((float) ($respuestas['presupuesto_total'] ?? 0), 2) }}">
        <input type="hidden" name="presupuesto_total" id="presupuesto_total" value="{{ $respuestas['presupuesto_total'] ?? '0' }}">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-home"></i> {{ SocioeconomicoComplementariaCampos::TITULO_VIVIENDA }}</h6>

<div class="form-group">
    <label for="viv_tiempo_residencia" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_TIEMPO_RESIDENCIA }} <span class="required">*</span></label>
    <input type="text" class="form-control @error('viv_tiempo_residencia') is-invalid @enderror"
           id="viv_tiempo_residencia" name="viv_tiempo_residencia"
           value="{{ old('viv_tiempo_residencia', $respuestas['viv_tiempo_residencia'] ?? '') }}" required>
    @error('viv_tiempo_residencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="viv_tipo_vivienda_detalle" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_TIPO_VIVIENDA }} <span class="required">*</span></label>
    <input type="text" class="form-control @error('viv_tipo_vivienda_detalle') is-invalid @enderror"
           id="viv_tipo_vivienda_detalle" name="viv_tipo_vivienda_detalle"
           value="{{ old('viv_tipo_vivienda_detalle', $respuestas['viv_tipo_vivienda_detalle'] ?? $respuestas['viv_tipo_vivienda'] ?? '') }}" required>
    @error('viv_tipo_vivienda_detalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="viv_monto_alquiler" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_MONTO_RENTA }}</label>
        <input type="number" class="form-control @error('viv_monto_alquiler') is-invalid @enderror"
               id="viv_monto_alquiler" name="viv_monto_alquiler" min="0" step="0.01"
               value="{{ old('viv_monto_alquiler', $respuestas['viv_monto_alquiler'] ?? '') }}">
        @error('viv_monto_alquiler')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="viv_propietario" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_PROPIETARIO }}</label>
        <input type="text" class="form-control @error('viv_propietario') is-invalid @enderror"
               id="viv_propietario" name="viv_propietario"
               value="{{ old('viv_propietario', $respuestas['viv_propietario'] ?? '') }}">
        @error('viv_propietario')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-group">
    <label for="viv_habitantes_detalle" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_HABITANTES }} <span class="required">*</span></label>
    <textarea class="form-control @error('viv_habitantes_detalle') is-invalid @enderror"
              id="viv_habitantes_detalle" name="viv_habitantes_detalle" rows="2" required>{{ old('viv_habitantes_detalle', $respuestas['viv_habitantes_detalle'] ?? $respuestas['viv_num_habitantes'] ?? '') }}</textarea>
    @error('viv_habitantes_detalle')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="viv_refs_ubicacion" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_REFS_UBICACION }} <span class="required">*</span></label>
    <textarea class="form-control @error('viv_refs_ubicacion') is-invalid @enderror"
              id="viv_refs_ubicacion" name="viv_refs_ubicacion" rows="2" required>{{ old('viv_refs_ubicacion', $respuestas['viv_refs_ubicacion'] ?? '') }}</textarea>
    @error('viv_refs_ubicacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="viv_zona_riesgo" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_ZONA_ROJA }} <span class="required">*</span></label>
    <select class="form-control @error('viv_zona_riesgo') is-invalid @enderror" id="viv_zona_riesgo" name="viv_zona_riesgo" required>
        <option value="">Seleccione...</option>
        <option value="si" @selected(old('viv_zona_riesgo', $respuestas['viv_zona_riesgo'] ?? '') === 'si')>Sí</option>
        <option value="no" @selected(old('viv_zona_riesgo', $respuestas['viv_zona_riesgo'] ?? '') === 'no')>No</option>
    </select>
    @error('viv_zona_riesgo')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="viv_zona_riesgo" show-when="si">
    <div class="form-group">
        <label for="viv_detalle_zona_riesgo" class="form-label">Detalle de la zona roja</label>
        <textarea class="form-control @error('viv_detalle_zona_riesgo') is-invalid @enderror"
                  id="viv_detalle_zona_riesgo" name="viv_detalle_zona_riesgo" rows="2">{{ old('viv_detalle_zona_riesgo', $respuestas['viv_detalle_zona_riesgo'] ?? '') }}</textarea>
        @error('viv_detalle_zona_riesgo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</x-campo-condicional>

<div class="form-group">
    <label for="viv_direcciones_anteriores" class="form-label">{{ SocioeconomicoComplementariaCampos::LABEL_DIRECCIONES_ANTERIORES }}</label>
    <textarea class="form-control @error('viv_direcciones_anteriores') is-invalid @enderror"
              id="viv_direcciones_anteriores" name="viv_direcciones_anteriores" rows="3">{{ old('viv_direcciones_anteriores', $respuestas['viv_direcciones_anteriores'] ?? '') }}</textarea>
    @error('viv_direcciones_anteriores')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="comp_ha_laborado_empresa" class="form-label">
        {{ SocioeconomicoComplementariaCampos::LABEL_HA_LABORADO_EMPRESA }} <span class="required">*</span>
    </label>
    <textarea class="form-control @error('comp_ha_laborado_empresa') is-invalid @enderror"
              id="comp_ha_laborado_empresa" name="comp_ha_laborado_empresa" rows="2" required>{{ old('comp_ha_laborado_empresa', $respuestas['comp_ha_laborado_empresa'] ?? '') }}</textarea>
    @error('comp_ha_laborado_empresa')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@include('cuestionario.partials.informacion-importante', [
    'tipoFormulario' => 'socioeconomico',
])

@push('scripts')
<script src="{{ asset('js/socioeconomico-complementaria.js') }}"></script>
@endpush
