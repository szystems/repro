{{-- Sección 3: Historial Laboral --}}

<div class="alert alert-info">
    <i class="fas fa-briefcase"></i>
    <strong>Información sobre su experiencia laboral y situación de empleo actual</strong>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="situacion_laboral_actual" class="form-label">
                Situación laboral actual <span class="required">*</span>
            </label>
            <select class="form-control @error('situacion_laboral_actual') is-invalid @enderror" 
                    id="situacion_laboral_actual" 
                    name="situacion_laboral_actual" 
                    required>
                <option value="">Seleccione...</option>
                <option value="empleado_tiempo_completo" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'empleado_tiempo_completo' ? 'selected' : '' }}>Empleado tiempo completo</option>
                <option value="empleado_tiempo_parcial" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'empleado_tiempo_parcial' ? 'selected' : '' }}>Empleado tiempo parcial</option>
                <option value="trabajador_independiente" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'trabajador_independiente' ? 'selected' : '' }}>Trabajador independiente</option>
                <option value="empresario" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'empresario' ? 'selected' : '' }}>Empresario/Dueño de negocio</option>
                <option value="desempleado" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'desempleado' ? 'selected' : '' }}>Desempleado</option>
                <option value="estudiante" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'estudiante' ? 'selected' : '' }}>Estudiante</option>
                <option value="jubilado" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'jubilado' ? 'selected' : '' }}>Jubilado/Pensionado</option>
                <option value="ama_casa" {{ old('situacion_laboral_actual', $respuestas['situacion_laboral_actual'] ?? '') == 'ama_casa' ? 'selected' : '' }}>Ama de casa</option>
            </select>
            @error('situacion_laboral_actual')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="anos_experiencia_laboral" class="form-label">
                Años de experiencia laboral total <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('anos_experiencia_laboral') is-invalid @enderror" 
                   id="anos_experiencia_laboral" 
                   name="anos_experiencia_laboral" 
                   value="{{ old('anos_experiencia_laboral', $respuestas['anos_experiencia_laboral'] ?? '') }}"
                   min="0" 
                   max="60"
                   required>
            @error('anos_experiencia_laboral')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div id="seccion_empleo_actual" class="d-none">
    <h5 class="section-subtitle mt-4 mb-3">
        <i class="fas fa-building"></i> Empleo Actual
    </h5>
    
    <div class="form-group">
        <label for="empresa_actual" class="form-label">
            Nombre de la empresa actual
        </label>
        <input type="text" 
               class="form-control @error('empresa_actual') is-invalid @enderror" 
               id="empresa_actual" 
               name="empresa_actual" 
               value="{{ old('empresa_actual', $respuestas['empresa_actual'] ?? '') }}">
        @error('empresa_actual')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="puesto_actual" class="form-label">
                    Puesto/Cargo actual
                </label>
                <input type="text" 
                       class="form-control @error('puesto_actual') is-invalid @enderror" 
                       id="puesto_actual" 
                       name="puesto_actual" 
                       value="{{ old('puesto_actual', $respuestas['puesto_actual'] ?? '') }}">
                @error('puesto_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="tiempo_empresa_actual" class="form-label">
                    Tiempo en la empresa actual (meses)
                </label>
                <input type="number" 
                       class="form-control @error('tiempo_empresa_actual') is-invalid @enderror" 
                       id="tiempo_empresa_actual" 
                       name="tiempo_empresa_actual" 
                       value="{{ old('tiempo_empresa_actual', $respuestas['tiempo_empresa_actual'] ?? '') }}"
                       min="0" 
                       max="600">
                @error('tiempo_empresa_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="salario_actual" class="form-label">
                    Salario mensual actual (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('salario_actual') is-invalid @enderror" 
                       id="salario_actual" 
                       name="salario_actual" 
                       value="{{ old('salario_actual', $respuestas['salario_actual'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('salario_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="tipo_contrato" class="form-label">
                    Tipo de contrato
                </label>
                <select class="form-control @error('tipo_contrato') is-invalid @enderror" 
                        id="tipo_contrato" 
                        name="tipo_contrato">
                    <option value="">Seleccione...</option>
                    <option value="indefinido" {{ old('tipo_contrato', $respuestas['tipo_contrato'] ?? '') == 'indefinido' ? 'selected' : '' }}>Indefinido</option>
                    <option value="temporal" {{ old('tipo_contrato', $respuestas['tipo_contrato'] ?? '') == 'temporal' ? 'selected' : '' }}>Temporal</option>
                    <option value="por_proyecto" {{ old('tipo_contrato', $respuestas['tipo_contrato'] ?? '') == 'por_proyecto' ? 'selected' : '' }}>Por proyecto</option>
                    <option value="prestacion_servicios" {{ old('tipo_contrato', $respuestas['tipo_contrato'] ?? '') == 'prestacion_servicios' ? 'selected' : '' }}>Prestación de servicios</option>
                    <option value="sin_contrato" {{ old('tipo_contrato', $respuestas['tipo_contrato'] ?? '') == 'sin_contrato' ? 'selected' : '' }}>Sin contrato formal</option>
                </select>
                @error('tipo_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="descripcion_funciones" class="form-label">
            Descripción de sus principales funciones
        </label>
        <textarea class="form-control @error('descripcion_funciones') is-invalid @enderror" 
                  id="descripcion_funciones" 
                  name="descripcion_funciones" 
                  rows="3"
                  placeholder="Describa brevemente sus principales responsabilidades...">{{ old('descripcion_funciones', $respuestas['descripcion_funciones'] ?? '') }}</textarea>
        @error('descripcion_funciones')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div id="seccion_negocio_propio" class="d-none">
    <h5 class="section-subtitle mt-4 mb-3">
        <i class="fas fa-store"></i> Negocio Propio
    </h5>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="nombre_negocio" class="form-label">
                    Nombre del negocio
                </label>
                <input type="text" 
                       class="form-control @error('nombre_negocio') is-invalid @enderror" 
                       id="nombre_negocio" 
                       name="nombre_negocio" 
                       value="{{ old('nombre_negocio', $respuestas['nombre_negocio'] ?? '') }}">
                @error('nombre_negocio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="giro_negocio" class="form-label">
                    Giro del negocio
                </label>
                <input type="text" 
                       class="form-control @error('giro_negocio') is-invalid @enderror" 
                       id="giro_negocio" 
                       name="giro_negocio" 
                       value="{{ old('giro_negocio', $respuestas['giro_negocio'] ?? '') }}"
                       placeholder="Ej: Venta de ropa, restaurante, servicios...">
                @error('giro_negocio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="tiempo_negocio" class="form-label">
                    Tiempo con el negocio (meses)
                </label>
                <input type="number" 
                       class="form-control @error('tiempo_negocio') is-invalid @enderror" 
                       id="tiempo_negocio" 
                       name="tiempo_negocio" 
                       value="{{ old('tiempo_negocio', $respuestas['tiempo_negocio'] ?? '') }}"
                       min="0" 
                       max="600">
                @error('tiempo_negocio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="ingresos_negocio" class="form-label">
                    Ingresos mensuales promedio (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('ingresos_negocio') is-invalid @enderror" 
                       id="ingresos_negocio" 
                       name="ingresos_negocio" 
                       value="{{ old('ingresos_negocio', $respuestas['ingresos_negocio'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('ingresos_negocio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div id="seccion_desempleo" class="d-none">
    <h5 class="section-subtitle mt-4 mb-3">
        <i class="fas fa-clock"></i> Situación de Desempleo
    </h5>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="tiempo_desempleado" class="form-label">
                    Tiempo desempleado (meses)
                </label>
                <input type="number" 
                       class="form-control @error('tiempo_desempleado') is-invalid @enderror" 
                       id="tiempo_desempleado" 
                       name="tiempo_desempleado" 
                       value="{{ old('tiempo_desempleado', $respuestas['tiempo_desempleado'] ?? '') }}"
                       min="0" 
                       max="120">
                @error('tiempo_desempleado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="razon_desempleo" class="form-label">
                    Razón del desempleo
                </label>
                <select class="form-control @error('razon_desempleo') is-invalid @enderror" 
                        id="razon_desempleo" 
                        name="razon_desempleo">
                    <option value="">Seleccione...</option>
                    <option value="despido" {{ old('razon_desempleo', $respuestas['razon_desempleo'] ?? '') == 'despido' ? 'selected' : '' }}>Despido</option>
                    <option value="renuncia" {{ old('razon_desempleo', $respuestas['razon_desempleo'] ?? '') == 'renuncia' ? 'selected' : '' }}>Renuncia voluntaria</option>
                    <option value="fin_contrato" {{ old('razon_desempleo', $respuestas['razon_desempleo'] ?? '') == 'fin_contrato' ? 'selected' : '' }}>Fin de contrato</option>
                    <option value="cierre_empresa" {{ old('razon_desempleo', $respuestas['razon_desempleo'] ?? '') == 'cierre_empresa' ? 'selected' : '' }}>Cierre de empresa</option>
                    <option value="primera_vez" {{ old('razon_desempleo', $respuestas['razon_desempleo'] ?? '') == 'primera_vez' ? 'selected' : '' }}>Busca trabajo por primera vez</option>
                    <option value="otro" {{ old('razon_desempleo', $respuestas['razon_desempleo'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('razon_desempleo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<h5 class="section-subtitle mt-4 mb-3">
    <i class="fas fa-history"></i> Historial Laboral Anterior
</h5>

<div class="form-group">
    <label for="numero_empleos_anteriores" class="form-label">
        Número de empleos anteriores <span class="required">*</span>
    </label>
    <input type="number" 
           class="form-control @error('numero_empleos_anteriores') is-invalid @enderror" 
           id="numero_empleos_anteriores" 
           name="numero_empleos_anteriores" 
           value="{{ old('numero_empleos_anteriores', $respuestas['numero_empleos_anteriores'] ?? '') }}"
           min="0" 
           max="50"
           required>
    @error('numero_empleos_anteriores')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="seccion_ultimo_empleo" class="d-none">
    <h6 class="mt-3 mb-2">
        <i class="fas fa-briefcase"></i> Último empleo anterior
    </h6>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="ultima_empresa" class="form-label">
                    Nombre de la empresa
                </label>
                <input type="text" 
                       class="form-control @error('ultima_empresa') is-invalid @enderror" 
                       id="ultima_empresa" 
                       name="ultima_empresa" 
                       value="{{ old('ultima_empresa', $respuestas['ultima_empresa'] ?? '') }}">
                @error('ultima_empresa')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="ultimo_puesto" class="form-label">
                    Puesto desempeñado
                </label>
                <input type="text" 
                       class="form-control @error('ultimo_puesto') is-invalid @enderror" 
                       id="ultimo_puesto" 
                       name="ultimo_puesto" 
                       value="{{ old('ultimo_puesto', $respuestas['ultimo_puesto'] ?? '') }}">
                @error('ultimo_puesto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4">
            <div class="form-group">
                <label for="ultimo_salario" class="form-label">
                    Último salario (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('ultimo_salario') is-invalid @enderror" 
                       id="ultimo_salario" 
                       name="ultimo_salario" 
                       value="{{ old('ultimo_salario', $respuestas['ultimo_salario'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('ultimo_salario')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="form-group">
                <label for="fecha_inicio_ultimo" class="form-label">
                    Fecha de inicio
                </label>
                <input type="date" 
                       class="form-control @error('fecha_inicio_ultimo') is-invalid @enderror" 
                       id="fecha_inicio_ultimo" 
                       name="fecha_inicio_ultimo" 
                       value="{{ old('fecha_inicio_ultimo', $respuestas['fecha_inicio_ultimo'] ?? '') }}">
                @error('fecha_inicio_ultimo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="form-group">
                <label for="fecha_fin_ultimo" class="form-label">
                    Fecha de finalización
                </label>
                <input type="date" 
                       class="form-control @error('fecha_fin_ultimo') is-invalid @enderror" 
                       id="fecha_fin_ultimo" 
                       name="fecha_fin_ultimo" 
                       value="{{ old('fecha_fin_ultimo', $respuestas['fecha_fin_ultimo'] ?? '') }}">
                @error('fecha_fin_ultimo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="motivo_salida_ultimo" class="form-label">
            Motivo de salida del último empleo
        </label>
        <select class="form-control @error('motivo_salida_ultimo') is-invalid @enderror" 
                id="motivo_salida_ultimo" 
                name="motivo_salida_ultimo">
            <option value="">Seleccione...</option>
            <option value="renuncia_voluntaria" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'renuncia_voluntaria' ? 'selected' : '' }}>Renuncia voluntaria</option>
            <option value="despido" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'despido' ? 'selected' : '' }}>Despido</option>
            <option value="fin_contrato" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'fin_contrato' ? 'selected' : '' }}>Finalización de contrato</option>
            <option value="mejor_oportunidad" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'mejor_oportunidad' ? 'selected' : '' }}>Mejor oportunidad laboral</option>
            <option value="razon_personal" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'razon_personal' ? 'selected' : '' }}>Razón personal</option>
            <option value="cierre_empresa" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'cierre_empresa' ? 'selected' : '' }}>Cierre de empresa</option>
            <option value="otro" {{ old('motivo_salida_ultimo', $respuestas['motivo_salida_ultimo'] ?? '') == 'otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('motivo_salida_ultimo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const situacionLaboral = document.getElementById('situacion_laboral_actual');
    const seccionEmpleoActual = document.getElementById('seccion_empleo_actual');
    const seccionNegocioPropio = document.getElementById('seccion_negocio_propio');
    const seccionDesempleo = document.getElementById('seccion_desempleo');
    const numeroEmpleosAnteriores = document.getElementById('numero_empleos_anteriores');
    const seccionUltimoEmpleo = document.getElementById('seccion_ultimo_empleo');
    
    function toggleSecciones() {
        // Ocultar todas las secciones
        seccionEmpleoActual.classList.add('d-none');
        seccionNegocioPropio.classList.add('d-none');
        seccionDesempleo.classList.add('d-none');
        
        // Mostrar sección correspondiente
        const situacion = situacionLaboral.value;
        
        if (situacion === 'empleado_tiempo_completo' || situacion === 'empleado_tiempo_parcial') {
            seccionEmpleoActual.classList.remove('d-none');
        } else if (situacion === 'trabajador_independiente' || situacion === 'empresario') {
            seccionNegocioPropio.classList.remove('d-none');
        } else if (situacion === 'desempleado') {
            seccionDesempleo.classList.remove('d-none');
        }
    }
    
    function toggleUltimoEmpleo() {
        const numero = parseInt(numeroEmpleosAnteriores.value) || 0;
        
        if (numero > 0) {
            seccionUltimoEmpleo.classList.remove('d-none');
        } else {
            seccionUltimoEmpleo.classList.add('d-none');
        }
    }
    
    // Event listeners
    situacionLaboral.addEventListener('change', toggleSecciones);
    numeroEmpleosAnteriores.addEventListener('change', toggleUltimoEmpleo);
    
    // Validación de fechas
    const fechaInicioUltimo = document.getElementById('fecha_inicio_ultimo');
    const fechaFinUltimo = document.getElementById('fecha_fin_ultimo');
    
    function validarFechas() {
        if (fechaInicioUltimo.value && fechaFinUltimo.value) {
            const inicio = new Date(fechaInicioUltimo.value);
            const fin = new Date(fechaFinUltimo.value);
            
            if (fin <= inicio) {
                fechaFinUltimo.setCustomValidity('La fecha de finalización debe ser posterior a la fecha de inicio');
            } else {
                fechaFinUltimo.setCustomValidity('');
            }
        }
    }
    
    fechaInicioUltimo.addEventListener('change', validarFechas);
    fechaFinUltimo.addEventListener('change', validarFechas);
    
    // Inicializar estado
    toggleSecciones();
    toggleUltimoEmpleo();
});
</script>
@endpush