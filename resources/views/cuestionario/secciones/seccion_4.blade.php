{{-- Sección 4: Situación Económica --}}

<div class="alert alert-info">
    <i class="fas fa-chart-line"></i>
    <strong>Información sobre sus ingresos, gastos y situación económica general</strong>
</div>

<h5 class="section-subtitle mb-3">
    <i class="fas fa-money-bill-wave"></i> Ingresos
</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ingresos_mensuales_totales" class="form-label">
                Ingresos mensuales totales (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('ingresos_mensuales_totales') is-invalid @enderror" 
                   id="ingresos_mensuales_totales" 
                   name="ingresos_mensuales_totales" 
                   value="{{ old('ingresos_mensuales_totales', $respuestas['ingresos_mensuales_totales'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Incluya todos sus ingresos (salario, bonos, otros trabajos, etc.)</div>
            @error('ingresos_mensuales_totales')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="otros_ingresos" class="form-label">
                Otros ingresos mensuales (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('otros_ingresos') is-invalid @enderror" 
                   id="otros_ingresos" 
                   name="otros_ingresos" 
                   value="{{ old('otros_ingresos', $respuestas['otros_ingresos'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Rentas, negocios adicionales, remesas, etc.</div>
            @error('otros_ingresos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="fuentes_otros_ingresos" class="form-label">
        Especifique las fuentes de otros ingresos
    </label>
    <textarea class="form-control @error('fuentes_otros_ingresos') is-invalid @enderror" 
              id="fuentes_otros_ingresos" 
              name="fuentes_otros_ingresos" 
              rows="2"
              placeholder="Ejemplo: Renta de propiedad, comisiones, trabajos de fin de semana...">{{ old('fuentes_otros_ingresos', $respuestas['fuentes_otros_ingresos'] ?? '') }}</textarea>
    @error('fuentes_otros_ingresos')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<h5 class="section-subtitle mt-4 mb-3">
    <i class="fas fa-receipt"></i> Gastos Mensuales
</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_vivienda" class="form-label">
                Gastos de vivienda (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('gastos_vivienda') is-invalid @enderror" 
                   id="gastos_vivienda" 
                   name="gastos_vivienda" 
                   value="{{ old('gastos_vivienda', $respuestas['gastos_vivienda'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Alquiler/hipoteca, servicios, mantenimiento</div>
            @error('gastos_vivienda')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_alimentacion" class="form-label">
                Gastos de alimentación (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('gastos_alimentacion') is-invalid @enderror" 
                   id="gastos_alimentacion" 
                   name="gastos_alimentacion" 
                   value="{{ old('gastos_alimentacion', $respuestas['gastos_alimentacion'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Mercado, restaurantes, despensa familiar</div>
            @error('gastos_alimentacion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_transporte" class="form-label">
                Gastos de transporte (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('gastos_transporte') is-invalid @enderror" 
                   id="gastos_transporte" 
                   name="gastos_transporte" 
                   value="{{ old('gastos_transporte', $respuestas['gastos_transporte'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Combustible, transporte público, mantenimiento vehículo</div>
            @error('gastos_transporte')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_salud" class="form-label">
                Gastos de salud (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('gastos_salud') is-invalid @enderror" 
                   id="gastos_salud" 
                   name="gastos_salud" 
                   value="{{ old('gastos_salud', $respuestas['gastos_salud'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Medicinas, consultas médicas, seguros</div>
            @error('gastos_salud')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_educacion" class="form-label">
                Gastos de educación (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('gastos_educacion') is-invalid @enderror" 
                   id="gastos_educacion" 
                   name="gastos_educacion" 
                   value="{{ old('gastos_educacion', $respuestas['gastos_educacion'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Colegiaturas, útiles, cursos</div>
            @error('gastos_educacion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_otros" class="form-label">
                Otros gastos (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('gastos_otros') is-invalid @enderror" 
                   id="gastos_otros" 
                   name="gastos_otros" 
                   value="{{ old('gastos_otros', $respuestas['gastos_otros'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Entretenimiento, ropa, gastos personales</div>
            @error('gastos_otros')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_totales_calculados" class="form-label">
                Total gastos mensuales (Q.)
            </label>
            <input type="number" 
                   class="form-control bg-light" 
                   id="gastos_totales_calculados" 
                   name="gastos_totales_calculados" 
                   value="{{ old('gastos_totales_calculados', $respuestas['gastos_totales_calculados'] ?? '') }}"
                   readonly>
            <div class="form-text">Se calcula automáticamente</div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="balance_mensual" class="form-label">
                Balance mensual (Q.)
            </label>
            <input type="number" 
                   class="form-control bg-light" 
                   id="balance_mensual" 
                   name="balance_mensual" 
                   value="{{ old('balance_mensual', $respuestas['balance_mensual'] ?? '') }}"
                   readonly>
            <div class="form-text">Ingresos menos gastos (calculado automáticamente)</div>
        </div>
    </div>
</div>

<h5 class="section-subtitle mt-4 mb-3">
    <i class="fas fa-credit-card"></i> Situación Crediticia y Deudas
</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="tiene_deudas" class="form-label">
                ¿Tiene deudas actualmente? <span class="required">*</span>
            </label>
            <select class="form-control @error('tiene_deudas') is-invalid @enderror" 
                    id="tiene_deudas" 
                    name="tiene_deudas" 
                    required>
                <option value="">Seleccione...</option>
                <option value="si" {{ old('tiene_deudas', $respuestas['tiene_deudas'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('tiene_deudas', $respuestas['tiene_deudas'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('tiene_deudas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6" id="seccion_deudas" class="d-none">
        <div class="form-group">
            <label for="monto_total_deudas" class="form-label">
                Monto total de deudas (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('monto_total_deudas') is-invalid @enderror" 
                   id="monto_total_deudas" 
                   name="monto_total_deudas" 
                   value="{{ old('monto_total_deudas', $respuestas['monto_total_deudas'] ?? '') }}"
                   min="0" 
                   step="0.01">
            @error('monto_total_deudas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div id="detalle_deudas" class="d-none">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="deudas_tarjetas_credito" class="form-label">
                    Deudas de tarjetas de crédito (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('deudas_tarjetas_credito') is-invalid @enderror" 
                       id="deudas_tarjetas_credito" 
                       name="deudas_tarjetas_credito" 
                       value="{{ old('deudas_tarjetas_credito', $respuestas['deudas_tarjetas_credito'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('deudas_tarjetas_credito')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="prestamos_bancarios" class="form-label">
                    Préstamos bancarios (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('prestamos_bancarios') is-invalid @enderror" 
                       id="prestamos_bancarios" 
                       name="prestamos_bancarios" 
                       value="{{ old('prestamos_bancarios', $respuestas['prestamos_bancarios'] ?? '') }}"
                       min="0" 
                       step="0.01">
                @error('prestamos_bancarios')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="prestamos_personales" class="form-label">
                    Préstamos personales (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('prestamos_personales') is-invalid @enderror" 
                       id="prestamos_personales" 
                       name="prestamos_personales" 
                       value="{{ old('prestamos_personales', $respuestas['prestamos_personales'] ?? '') }}"
                       min="0" 
                       step="0.01">
                <div class="form-text">Cooperativas, personas particulares</div>
                @error('prestamos_personales')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="form-group">
                <label for="otras_deudas" class="form-label">
                    Otras deudas (Q.)
                </label>
                <input type="number" 
                       class="form-control @error('otras_deudas') is-invalid @enderror" 
                       id="otras_deudas" 
                       name="otras_deudas" 
                       value="{{ old('otras_deudas', $respuestas['otras_deudas'] ?? '') }}"
                       min="0" 
                       step="0.01">
                <div class="form-text">Créditos comerciales, familiares, etc.</div>
                @error('otras_deudas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="pago_mensual_deudas" class="form-label">
            Pago mensual total por deudas (Q.)
        </label>
        <input type="number" 
               class="form-control @error('pago_mensual_deudas') is-invalid @enderror" 
               id="pago_mensual_deudas" 
               name="pago_mensual_deudas" 
               value="{{ old('pago_mensual_deudas', $respuestas['pago_mensual_deudas'] ?? '') }}"
               min="0" 
               step="0.01">
        @error('pago_mensual_deudas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h5 class="section-subtitle mt-4 mb-3">
    <i class="fas fa-piggy-bank"></i> Ahorros y Patrimonio
</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="tiene_ahorros" class="form-label">
                ¿Tiene ahorros? <span class="required">*</span>
            </label>
            <select class="form-control @error('tiene_ahorros') is-invalid @enderror" 
                    id="tiene_ahorros" 
                    name="tiene_ahorros" 
                    required>
                <option value="">Seleccione...</option>
                <option value="si" {{ old('tiene_ahorros', $respuestas['tiene_ahorros'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('tiene_ahorros', $respuestas['tiene_ahorros'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('tiene_ahorros')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6" id="seccion_ahorros" class="d-none">
        <div class="form-group">
            <label for="monto_ahorros" class="form-label">
                Monto aproximado de ahorros (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('monto_ahorros') is-invalid @enderror" 
                   id="monto_ahorros" 
                   name="monto_ahorros" 
                   value="{{ old('monto_ahorros', $respuestas['monto_ahorros'] ?? '') }}"
                   min="0" 
                   step="0.01">
            @error('monto_ahorros')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="posee_vehiculo" class="form-label">
                ¿Posee vehículo? <span class="required">*</span>
            </label>
            <select class="form-control @error('posee_vehiculo') is-invalid @enderror" 
                    id="posee_vehiculo" 
                    name="posee_vehiculo" 
                    required>
                <option value="">Seleccione...</option>
                <option value="si" {{ old('posee_vehiculo', $respuestas['posee_vehiculo'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('posee_vehiculo', $respuestas['posee_vehiculo'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('posee_vehiculo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6" id="seccion_vehiculo" class="d-none">
        <div class="form-group">
            <label for="valor_vehiculo" class="form-label">
                Valor aproximado del vehículo (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('valor_vehiculo') is-invalid @enderror" 
                   id="valor_vehiculo" 
                   name="valor_vehiculo" 
                   value="{{ old('valor_vehiculo', $respuestas['valor_vehiculo'] ?? '') }}"
                   min="0" 
                   step="0.01">
            @error('valor_vehiculo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="observaciones_economicas" class="form-label">
        Observaciones sobre su situación económica
    </label>
    <textarea class="form-control @error('observaciones_economicas') is-invalid @enderror" 
              id="observaciones_economicas" 
              name="observaciones_economicas" 
              rows="3"
              placeholder="Información adicional que considere relevante sobre su situación económica...">{{ old('observaciones_economicas', $respuestas['observaciones_economicas'] ?? '') }}</textarea>
    @error('observaciones_economicas')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tieneDeudas = document.getElementById('tiene_deudas');
    const seccionDeudas = document.getElementById('seccion_deudas');
    const detalleDeudas = document.getElementById('detalle_deudas');
    const tieneAhorros = document.getElementById('tiene_ahorros');
    const seccionAhorros = document.getElementById('seccion_ahorros');
    const poseeVehiculo = document.getElementById('posee_vehiculo');
    const seccionVehiculo = document.getElementById('seccion_vehiculo');
    
    // Campos de gastos para cálculo automático
    const camposGastos = [
        'gastos_vivienda',
        'gastos_alimentacion', 
        'gastos_transporte',
        'gastos_salud',
        'gastos_educacion',
        'gastos_otros'
    ];
    
    const ingresosTotales = document.getElementById('ingresos_mensuales_totales');
    const gastosTotales = document.getElementById('gastos_totales_calculados');
    const balanceMensual = document.getElementById('balance_mensual');
    
    // Mostrar/ocultar secciones
    function toggleSeccionDeudas() {
        if (tieneDeudas.value === 'si') {
            seccionDeudas.classList.remove('d-none');
            detalleDeudas.classList.remove('d-none');
        } else {
            seccionDeudas.classList.add('d-none');
            detalleDeudas.classList.add('d-none');
            // Limpiar campos
            document.getElementById('monto_total_deudas').value = '';
            document.getElementById('deudas_tarjetas_credito').value = '';
            document.getElementById('prestamos_bancarios').value = '';
            document.getElementById('prestamos_personales').value = '';
            document.getElementById('otras_deudas').value = '';
            document.getElementById('pago_mensual_deudas').value = '';
        }
    }
    
    function toggleSeccionAhorros() {
        if (tieneAhorros.value === 'si') {
            seccionAhorros.classList.remove('d-none');
        } else {
            seccionAhorros.classList.add('d-none');
            document.getElementById('monto_ahorros').value = '';
        }
    }
    
    function toggleSeccionVehiculo() {
        if (poseeVehiculo.value === 'si') {
            seccionVehiculo.classList.remove('d-none');
        } else {
            seccionVehiculo.classList.add('d-none');
            document.getElementById('valor_vehiculo').value = '';
        }
    }
    
    // Calcular totales automáticamente
    function calcularTotales() {
        let totalGastos = 0;
        
        camposGastos.forEach(campo => {
            const valor = parseFloat(document.getElementById(campo).value) || 0;
            totalGastos += valor;
        });
        
        gastosTotales.value = totalGastos.toFixed(2);
        
        // Calcular balance
        const ingresos = parseFloat(ingresosTotales.value) || 0;
        const balance = ingresos - totalGastos;
        balanceMensual.value = balance.toFixed(2);
        
        // Cambiar color según el balance
        if (balance < 0) {
            balanceMensual.classList.add('text-danger');
            balanceMensual.classList.remove('text-success');
        } else {
            balanceMensual.classList.add('text-success');
            balanceMensual.classList.remove('text-danger');
        }
    }
    
    // Event listeners
    tieneDeudas.addEventListener('change', toggleSeccionDeudas);
    tieneAhorros.addEventListener('change', toggleSeccionAhorros);
    poseeVehiculo.addEventListener('change', toggleSeccionVehiculo);
    
    // Event listeners para cálculo automático
    ingresosTotales.addEventListener('input', calcularTotales);
    
    camposGastos.forEach(campo => {
        document.getElementById(campo).addEventListener('input', calcularTotales);
    });
    
    // Validar que la suma de deudas detalladas coincida con el monto total
    const camposDeudaDetalle = [
        'deudas_tarjetas_credito',
        'prestamos_bancarios',
        'prestamos_personales',
        'otras_deudas'
    ];
    
    function validarDeudas() {
        let sumaDetalle = 0;
        camposDeudaDetalle.forEach(campo => {
            const valor = parseFloat(document.getElementById(campo).value) || 0;
            sumaDetalle += valor;
        });
        
        const montoTotal = parseFloat(document.getElementById('monto_total_deudas').value) || 0;
        
        // Si hay diferencia significativa, mostrar advertencia
        if (Math.abs(sumaDetalle - montoTotal) > 1 && montoTotal > 0) {
            document.getElementById('monto_total_deudas').setCustomValidity(
                `La suma del detalle (Q.${sumaDetalle.toFixed(2)}) no coincide con el monto total`
            );
        } else {
            document.getElementById('monto_total_deudas').setCustomValidity('');
        }
    }
    
    camposDeudaDetalle.forEach(campo => {
        document.getElementById(campo).addEventListener('input', validarDeudas);
    });
    
    document.getElementById('monto_total_deudas').addEventListener('input', validarDeudas);
    
    // Inicializar estado
    toggleSeccionDeudas();
    toggleSeccionAhorros();
    toggleSeccionVehiculo();
    calcularTotales();
});
</script>
@endpush