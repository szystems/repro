{{-- Sección 3: Historial Laboral --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información sobre su experiencia laboral y situación actual</strong>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="situacion_laboral_actual" class="form-label">
                Situación Laboral Actual <span class="required">*</span>
            </label>
            <select class="form-control @error('situacion_laboral_actual') is-invalid @enderror" 
                    id="situacion_laboral_actual" 
                    name="situacion_laboral_actual" 
                    required>
                <option value="">Seleccione...</option>
                <option value="empleado" {{ old('situacion_laboral_actual', $respuestasExistentes['situacion_laboral_actual'] ?? '') == 'empleado' ? 'selected' : '' }}>Empleado</option>
                <option value="independiente" {{ old('situacion_laboral_actual', $respuestasExistentes['situacion_laboral_actual'] ?? '') == 'independiente' ? 'selected' : '' }}>Trabajador Independiente</option>
                <option value="empresario" {{ old('situacion_laboral_actual', $respuestasExistentes['situacion_laboral_actual'] ?? '') == 'empresario' ? 'selected' : '' }}>Empresario</option>
                <option value="desempleado" {{ old('situacion_laboral_actual', $respuestasExistentes['situacion_laboral_actual'] ?? '') == 'desempleado' ? 'selected' : '' }}>Desempleado</option>
                <option value="estudiante" {{ old('situacion_laboral_actual', $respuestasExistentes['situacion_laboral_actual'] ?? '') == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                <option value="jubilado" {{ old('situacion_laboral_actual', $respuestasExistentes['situacion_laboral_actual'] ?? '') == 'jubilado' ? 'selected' : '' }}>Jubilado</option>
            </select>
            @error('situacion_laboral_actual')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="anos_experiencia_laboral" class="form-label">
                Años de Experiencia Laboral Total <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('anos_experiencia_laboral') is-invalid @enderror" 
                   id="anos_experiencia_laboral" 
                   name="anos_experiencia_laboral" 
                   value="{{ old('anos_experiencia_laboral', $respuestasExistentes['anos_experiencia_laboral'] ?? '') }}"
                   min="0" 
                   max="50"
                   required>
            @error('anos_experiencia_laboral')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div id="seccion_empleado" class="d-none">
    <h5 class="mt-4 mb-3">Información del Empleo Actual</h5>
    
    <div class="form-group">
        <label for="empresa_actual" class="form-label">
            Nombre de la Empresa/Institución Actual
        </label>
        <input type="text" 
               class="form-control @error('empresa_actual') is-invalid @enderror" 
               id="empresa_actual" 
               name="empresa_actual" 
               value="{{ old('empresa_actual', $respuestasExistentes['empresa_actual'] ?? '') }}"
               maxlength="100">
        @error('empresa_actual')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="puesto_actual" class="form-label">
                    Puesto/Cargo Actual
                </label>
                <input type="text" 
                       class="form-control @error('puesto_actual') is-invalid @enderror" 
                       id="puesto_actual" 
                       name="puesto_actual" 
                       value="{{ old('puesto_actual', $respuestasExistentes['puesto_actual'] ?? '') }}"
                       maxlength="100">
                @error('puesto_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="fecha_inicio_actual" class="form-label">
                    Fecha de Inicio en el Empleo Actual
                </label>
                <input type="date" 
                       class="form-control @error('fecha_inicio_actual') is-invalid @enderror" 
                       id="fecha_inicio_actual" 
                       name="fecha_inicio_actual" 
                       value="{{ old('fecha_inicio_actual', $respuestasExistentes['fecha_inicio_actual'] ?? '') }}"
                       max="{{ date('Y-m-d') }}">
                @error('fecha_inicio_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="salario_actual" class="form-label">
                    Salario Mensual Actual (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('salario_actual') is-invalid @enderror" 
                       id="salario_actual" 
                       name="salario_actual" 
                       value="{{ old('salario_actual', $respuestasExistentes['salario_actual'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('salario_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="jefe_inmediato" class="form-label">
                    Nombre del Jefe Inmediato
                </label>
                <input type="text" 
                       class="form-control @error('jefe_inmediato') is-invalid @enderror" 
                       id="jefe_inmediato" 
                       name="jefe_inmediato" 
                       value="{{ old('jefe_inmediato', $respuestasExistentes['jefe_inmediato'] ?? '') }}"
                       maxlength="100">
                @error('jefe_inmediato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div id="seccion_independiente" class="d-none">
    <h5 class="mt-4 mb-3">Información del Trabajo Independiente</h5>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="tipo_negocio" class="form-label">
                    Tipo de Negocio/Actividad
                </label>
                <input type="text" 
                       class="form-control @error('tipo_negocio') is-invalid @enderror" 
                       id="tipo_negocio" 
                       name="tipo_negocio" 
                       value="{{ old('tipo_negocio', $respuestasExistentes['tipo_negocio'] ?? '') }}"
                       maxlength="100">
                @error('tipo_negocio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="ingresos_mensuales" class="form-label">
                    Ingresos Mensuales Promedio (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('ingresos_mensuales') is-invalid @enderror" 
                       id="ingresos_mensuales" 
                       name="ingresos_mensuales" 
                       value="{{ old('ingresos_mensuales', $respuestasExistentes['ingresos_mensuales'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('ingresos_mensuales')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<h5 class="mt-4 mb-3">Historial de Empleos Anteriores</h5>

<div class="form-group">
    <label for="empleos_anteriores" class="form-label">
        Detalle sus últimos 3 empleos (más recientes)
    </label>
    <textarea class="form-control @error('empleos_anteriores') is-invalid @enderror" 
              id="empleos_anteriores" 
              name="empleos_anteriores" 
              rows="6"
              placeholder="Para cada empleo indique: Empresa, Puesto, Fechas (inicio-fin), Motivo de salida, Jefe inmediato y teléfono de referencia...">{{ old('empleos_anteriores', $respuestasExistentes['empleos_anteriores'] ?? '') }}</textarea>
    @error('empleos_anteriores')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="motivo_busqueda" class="form-label">
        Si está buscando empleo, ¿cuál es el motivo principal?
    </label>
    <select class="form-control @error('motivo_busqueda') is-invalid @enderror" 
            id="motivo_busqueda" 
            name="motivo_busqueda">
        <option value="">No aplica / No estoy buscando empleo</option>
        <option value="desempleo" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'desempleo' ? 'selected' : '' }}>Actualmente desempleado</option>
        <option value="mejor_oportunidad" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'mejor_oportunidad' ? 'selected' : '' }}>Busco mejor oportunidad</option>
        <option value="cambio_de_area" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'cambio_de_area' ? 'selected' : '' }}>Cambio de área profesional</option>
        <option value="mejores_ingresos" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'mejores_ingresos' ? 'selected' : '' }}>Mejores ingresos económicos</option>
        <option value="crecimiento_profesional" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'crecimiento_profesional' ? 'selected' : '' }}>Crecimiento profesional</option>
        <option value="ambiente_laboral" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'ambiente_laboral' ? 'selected' : '' }}>Mejor ambiente laboral</option>
        <option value="otro" {{ old('motivo_busqueda', $respuestasExistentes['motivo_busqueda'] ?? '') == 'otro' ? 'selected' : '' }}>Otro motivo</option>
    </select>
    @error('motivo_busqueda')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@php
    use App\Support\HistorialAcademico;
    use App\Support\HistorialLaboralIntegridad;
    $resp = $respuestasExistentes ?? [];
    $ultimoNivel = old('ultimo_nivel_academico', $resp['ultimo_nivel_academico'] ?? 'ninguno');
    $filasAcademicas = HistorialAcademico::filasParaFormulario($ultimoNivel, $tablasExistentes['formacion_academica'] ?? []);
@endphp

<hr class="my-4">
<h5 class="mt-4 mb-3">Formación académica</h5>
<div class="form-group">
    <label for="ultimo_nivel_academico" class="form-label">Último nivel académico alcanzado <span class="required">*</span></label>
    <select class="form-control @error('ultimo_nivel_academico') is-invalid @enderror" id="ultimo_nivel_academico" name="ultimo_nivel_academico" required>
        <option value="ninguno" {{ $ultimoNivel === 'ninguno' ? 'selected' : '' }}>Ninguno</option>
        @foreach(HistorialAcademico::NIVELES as $k => $et)
            <option value="{{ $k }}" {{ $ultimoNivel === $k ? 'selected' : '' }}>{{ $et }}</option>
        @endforeach
    </select>
    @error('ultimo_nivel_academico')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="ultimo_nivel_academico" hide-when="ninguno" id="seccion_formacion_academica">
    <p class="text-muted small mb-2">
        Complete una fila por cada nivel académico desde primaria hasta el último nivel que seleccionó arriba.
    </p>
    <x-tabla-dinamica
        name="formacion_academica"
        titulo="Detalle por nivel académico"
        :columnas="\App\Support\TablaDinamica::columnasFormacionAcademica()"
        :filas="$filasAcademicas"
        :minFilas="max(1, count($filasAcademicas))"
        :permitirAgregar="false"
        :permitirEliminar="false"
    />
</x-campo-condicional>

@push('scripts')
<script>
    window.formacionAcademicaNiveles = @json(HistorialAcademico::NIVELES);
</script>
<script src="{{ asset('js/formacion-academica.js') }}?v={{ filemtime(public_path('js/formacion-academica.js')) }}"></script>
@endpush

<hr class="my-4">
<h5 class="mb-3">Experiencia laboral previa</h5>
<div class="form-group">
    <label for="experiencia_previa" class="form-label">¿Posee experiencia laboral previa? <span class="required">*</span></label>
    <select class="form-control @error('experiencia_previa') is-invalid @enderror" id="experiencia_previa" name="experiencia_previa" required>
        <option value="">Seleccione...</option>
        <option value="si" {{ old('experiencia_previa', $resp['experiencia_previa'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ old('experiencia_previa', $resp['experiencia_previa'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('experiencia_previa')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="experiencia_previa" show-when="si" id="seccion_empleos">
    <x-tabla-dinamica
        name="empleos"
        titulo="Historial de empleos"
        :columnas="\App\Support\TablaDinamica::columnasEmpleos()"
        :filas="$tablasExistentes['empleos'] ?? []"
        :minFilas="1"
        textoAgregar="Agregar empleo"
        textoEliminar="Quitar empleo"
    />
</x-campo-condicional>

<div class="form-group">
    <label for="observaciones_laborales" class="form-label">Observaciones laborales</label>
    <textarea class="form-control" id="observaciones_laborales" name="observaciones_laborales" rows="3">{{ old('observaciones_laborales', $resp['observaciones_laborales'] ?? '') }}</textarea>
</div>

@include('cuestionario.secciones.partials.preguntas-textarea', [
    'titulo' => 'Preguntas complementarias de integridad',
    'badge' => 'Confidencial',
    'preguntas' => HistorialLaboralIntegridad::PREGUNTAS,
    'respuestas' => $resp,
])

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const situacionLaboral = document.getElementById('situacion_laboral_actual');
    const seccionEmpleado = document.getElementById('seccion_empleado');
    const seccionIndependiente = document.getElementById('seccion_independiente');
    
    function toggleSecciones() {
        // Ocultar todas las secciones
        seccionEmpleado.classList.add('d-none');
        seccionIndependiente.classList.add('d-none');
        
        // Limpiar campos
        const camposEmpleado = ['empresa_actual', 'puesto_actual', 'fecha_inicio_actual', 'salario_actual', 'jefe_inmediato'];
        const camposIndependiente = ['tipo_negocio', 'ingresos_mensuales'];
        
        camposEmpleado.forEach(campo => {
            const element = document.getElementById(campo);
            if (element) {
                element.required = false;
                if (situacionLaboral.value !== 'empleado') {
                    element.value = '';
                }
            }
        });
        
        camposIndependiente.forEach(campo => {
            const element = document.getElementById(campo);
            if (element) {
                if (situacionLaboral.value !== 'independiente') {
                    element.value = '';
                }
            }
        });
        
        // Mostrar sección correspondiente
        if (situacionLaboral.value === 'empleado') {
            seccionEmpleado.classList.remove('d-none');
            // Hacer campos requeridos
            document.getElementById('empresa_actual').required = true;
            document.getElementById('puesto_actual').required = true;
        } else if (situacionLaboral.value === 'independiente' || situacionLaboral.value === 'empresario') {
            seccionIndependiente.classList.remove('d-none');
        }
    }
    
    // Event listener
    situacionLaboral.addEventListener('change', toggleSecciones);
    
    // Inicializar estado al cargar
    toggleSecciones();
});
</script>
@endpush