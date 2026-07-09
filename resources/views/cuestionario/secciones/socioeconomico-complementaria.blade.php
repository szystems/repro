@php
    use App\Support\SocioeconomicoComplementariaCampos;
    use App\Support\TablaDinamica;

    $respuestas = $respuestasExistentes ?? [];
    $tablas = $tablasExistentes ?? [];
    $empleosHistorial = $empleosHistorial ?? [];
@endphp

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Complete las referencias, bienes, presupuesto e información de vivienda. Los totales de bienes y gastos se calculan automáticamente.
    <br><small class="text-muted">En tablas con mínimo de filas (p. ej. referencias familiares), el botón eliminar se habilita al agregar filas extra. Al eliminar, se pedirá confirmación.</small>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-users"></i> Referencias</h6>

<x-tabla-dinamica
    name="referencias_familiares"
    titulo="Referencias familiares (mínimo 2)"
    :columnas="TablaDinamica::columnasReferenciasFamiliares()"
    :filas="$tablas['referencias_familiares'] ?? []"
    :min-filas="2"
    texto-agregar="Agregar referencia familiar"
/>

<x-tabla-dinamica
    name="referencias_personales"
    titulo="Referencias personales (mínimo 2)"
    :columnas="TablaDinamica::columnasReferenciasPersonales()"
    :filas="$tablas['referencias_personales'] ?? []"
    :min-filas="2"
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

<div class="d-flex justify-content-between align-items-center mb-2">
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

<h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-calculator"></i> Presupuesto personal mensual</h6>
<div class="socio-total-wrapper" data-total-field="monto" data-display-id="presupuesto_total_display" data-hidden-id="presupuesto_total">
<x-tabla-dinamica
    name="presupuesto"
    titulo="Detalle de gastos mensuales"
    :columnas="TablaDinamica::columnasPresupuesto()"
    :filas="$tablas['presupuesto'] ?? []"
    :min-filas="0"
    texto-agregar="Agregar gasto"
/>
</div>
<div class="row mb-4">
    <div class="col-md-4 ms-auto">
        <label class="form-label fw-bold">Total mensual de gastos (Q.)</label>
        <input type="text" class="form-control" id="presupuesto_total_display" readonly
               value="{{ number_format((float) ($respuestas['presupuesto_total'] ?? 0), 2) }}">
        <input type="hidden" name="presupuesto_total" id="presupuesto_total" value="{{ $respuestas['presupuesto_total'] ?? '0' }}">
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-home"></i> Información de vivienda</h6>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="viv_tiempo_residencia" class="form-label">Tiempo en el domicilio actual <span class="required">*</span></label>
        <input type="text" class="form-control" id="viv_tiempo_residencia" name="viv_tiempo_residencia"
               value="{{ old('viv_tiempo_residencia', $respuestas['viv_tiempo_residencia'] ?? '') }}"
               placeholder="Ej: 3 años, 6 meses">
    </div>
    <div class="col-md-6 mb-3">
        <label for="viv_tipo_vivienda" class="form-label">Tipo de vivienda <span class="required">*</span></label>
        <select class="form-select" id="viv_tipo_vivienda" name="viv_tipo_vivienda">
            <option value="">Seleccione...</option>
            @foreach(SocioeconomicoComplementariaCampos::tiposVivienda() as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('viv_tipo_vivienda', $respuestas['viv_tipo_vivienda'] ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>
</div>

<x-campo-condicional trigger="viv_tipo_vivienda" show-when="alquilada,familiar,prestada">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="viv_propietario" class="form-label">Nombre del propietario / familiar</label>
            <input type="text" class="form-control" id="viv_propietario" name="viv_propietario"
                   value="{{ old('viv_propietario', $respuestas['viv_propietario'] ?? '') }}">
        </div>
    </div>
</x-campo-condicional>

<x-campo-condicional trigger="viv_tipo_vivienda" show-when="alquilada">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="viv_monto_alquiler" class="form-label">Monto de alquiler mensual (Q.)</label>
            <input type="number" class="form-control" id="viv_monto_alquiler" name="viv_monto_alquiler" min="0" step="0.01"
                   value="{{ old('viv_monto_alquiler', $respuestas['viv_monto_alquiler'] ?? '') }}">
        </div>
    </div>
</x-campo-condicional>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="viv_num_habitantes" class="form-label">Personas que habitan la vivienda <span class="required">*</span></label>
        <input type="number" class="form-control" id="viv_num_habitantes" name="viv_num_habitantes" min="1" max="50"
               value="{{ old('viv_num_habitantes', $respuestas['viv_num_habitantes'] ?? '') }}">
    </div>
    <div class="col-md-8 mb-3">
        <label for="viv_refs_ubicacion" class="form-label">Referencias para ubicar la vivienda</label>
        <textarea class="form-control" id="viv_refs_ubicacion" name="viv_refs_ubicacion" rows="2"
                  placeholder="Colonia, calle, color de fachada, puntos de referencia...">{{ old('viv_refs_ubicacion', $respuestas['viv_refs_ubicacion'] ?? '') }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="viv_zona_riesgo" class="form-label">¿Considera que vive en zona de riesgo? <span class="required">*</span></label>
        <select class="form-select" id="viv_zona_riesgo" name="viv_zona_riesgo">
            <option value="">Seleccione...</option>
            <option value="si" @selected(old('viv_zona_riesgo', $respuestas['viv_zona_riesgo'] ?? '') === 'si')>Sí</option>
            <option value="no" @selected(old('viv_zona_riesgo', $respuestas['viv_zona_riesgo'] ?? '') === 'no')>No</option>
        </select>
    </div>
</div>

<x-campo-condicional trigger="viv_zona_riesgo" show-when="si">
    <div class="mb-3">
        <label for="viv_detalle_zona_riesgo" class="form-label">Detalle de la zona de riesgo</label>
        <textarea class="form-control" id="viv_detalle_zona_riesgo" name="viv_detalle_zona_riesgo" rows="2">{{ old('viv_detalle_zona_riesgo', $respuestas['viv_detalle_zona_riesgo'] ?? '') }}</textarea>
    </div>
</x-campo-condicional>

<div class="mb-3">
    <label for="viv_direcciones_anteriores" class="form-label">Direcciones anteriores (últimos 5 años)</label>
    <textarea class="form-control" id="viv_direcciones_anteriores" name="viv_direcciones_anteriores" rows="3"
              placeholder="Indique domicilios previos y tiempo aproximado en cada uno">{{ old('viv_direcciones_anteriores', $respuestas['viv_direcciones_anteriores'] ?? '') }}</textarea>
</div>

<div class="alert alert-warning mt-4">
    <h6><i class="fas fa-info-circle"></i> Información importante</h6>
    <p class="mb-0 small">Revise que las referencias y totales sean correctos antes de finalizar. La información es confidencial. En la pantalla final podrá adjuntar constancia laboral y recibo de luz si los tiene disponibles.</p>
</div>

@push('scripts')
<script src="{{ asset('js/socioeconomico-complementaria.js') }}"></script>
@endpush
