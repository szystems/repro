{{-- Sección 4: Situación Económica --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información detallada sobre sus ingresos, gastos y situación financiera</strong>
</div>

<h5 class="mb-3">Ingresos</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ingresos_principales" class="form-label">
                Ingresos Mensuales Principales (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('ingresos_principales') is-invalid @enderror" 
                   id="ingresos_principales" 
                   name="ingresos_principales" 
                   value="{{ old('ingresos_principales', $respuestasExistentes['ingresos_principales'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Salario, honorarios o ingresos por negocio propio</div>
            @error('ingresos_principales')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ingresos_adicionales" class="form-label">
                Ingresos Adicionales (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('ingresos_adicionales') is-invalid @enderror" 
                   id="ingresos_adicionales" 
                   name="ingresos_adicionales" 
                   value="{{ old('ingresos_adicionales', $respuestasExistentes['ingresos_adicionales'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Rentas, comisiones, trabajos extras, etc.</div>
            @error('ingresos_adicionales')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="ingresos_familiares" class="form-label">
                Ingresos de Otros Miembros del Hogar (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('ingresos_familiares') is-invalid @enderror" 
                   id="ingresos_familiares" 
                   name="ingresos_familiares" 
                   value="{{ old('ingresos_familiares', $respuestasExistentes['ingresos_familiares'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Ingresos de pareja, hijos, padres, etc.</div>
            @error('ingresos_familiares')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="total_ingresos" class="form-label">
                Total de Ingresos Mensuales (Q.)
            </label>
            <input type="number" 
                   class="form-control" 
                   id="total_ingresos" 
                   name="total_ingresos" 
                   readonly>
            <div class="form-text">Se calcula automáticamente</div>
        </div>
    </div>
</div>

<h5 class="mt-4 mb-3">Gastos Mensuales</h5>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_vivienda" class="form-label">
                Gastos de Vivienda (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('gastos_vivienda') is-invalid @enderror" 
                   id="gastos_vivienda" 
                   name="gastos_vivienda" 
                   value="{{ old('gastos_vivienda', $respuestasExistentes['gastos_vivienda'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Alquiler, hipoteca, servicios básicos</div>
            @error('gastos_vivienda')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_alimentacion" class="form-label">
                Gastos de Alimentación (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('gastos_alimentacion') is-invalid @enderror" 
                   id="gastos_alimentacion" 
                   name="gastos_alimentacion" 
                   value="{{ old('gastos_alimentacion', $respuestasExistentes['gastos_alimentacion'] ?? '') }}"
                   min="0" 
                   step="0.01"
                   required>
            <div class="form-text">Supermercado, restaurantes, etc.</div>
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
                Gastos de Transporte (Q.) <span class="required">*</span>
            </label>
            <input type="number" 
                   class="form-control @error('gastos_transporte') is-invalid @enderror" 
                   id="gastos_transporte" 
                   name="gastos_transporte" 
                   value="{{ old('gastos_transporte', $respuestasExistentes['gastos_transporte'] ?? '') }}"
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
            <label for="gastos_educacion" class="form-label">
                Gastos de Educación (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('gastos_educacion') is-invalid @enderror" 
                   id="gastos_educacion" 
                   name="gastos_educacion" 
                   value="{{ old('gastos_educacion', $respuestasExistentes['gastos_educacion'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Colegiaturas, uniformes, útiles escolares</div>
            @error('gastos_educacion')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_salud" class="form-label">
                Gastos de Salud (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('gastos_salud') is-invalid @enderror" 
                   id="gastos_salud" 
                   name="gastos_salud" 
                   value="{{ old('gastos_salud', $respuestasExistentes['gastos_salud'] ?? '') }}"
                   min="0" 
                   step="0.01">
            <div class="form-text">Medicinas, consultas médicas, seguros</div>
            @error('gastos_salud')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="gastos_otros" class="form-label">
                Otros Gastos (Q.)
            </label>
            <input type="number" 
                   class="form-control @error('gastos_otros') is-invalid @enderror" 
                   id="gastos_otros" 
                   name="gastos_otros" 
                   value="{{ old('gastos_otros', $respuestasExistentes['gastos_otros'] ?? '') }}"
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
            <label for="total_gastos" class="form-label">
                Total de Gastos Mensuales (Q.)
            </label>
            <input type="number" 
                   class="form-control" 
                   id="total_gastos" 
                   name="total_gastos" 
                   readonly>
            <div class="form-text">Se calcula automáticamente</div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="balance_mensual" class="form-label">
                Balance Mensual (Q.)
            </label>
            <input type="number" 
                   class="form-control" 
                   id="balance_mensual" 
                   name="balance_mensual" 
                   readonly>
            <div class="form-text">Ingresos - Gastos (se calcula automáticamente)</div>
        </div>
    </div>
</div>

<h5 class="mt-4 mb-3">Información Financiera Adicional</h5>

<div class="form-group">
    <label for="tiene_deudas" class="form-label">
        ¿Tiene deudas actuales? <span class="required">*</span>
    </label>
    <select class="form-control @error('tiene_deudas') is-invalid @enderror" 
            id="tiene_deudas" 
            name="tiene_deudas" 
            required>
        <option value="">Seleccione...</option>
        <option value="si" {{ old('tiene_deudas', $respuestasExistentes['tiene_deudas'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ old('tiene_deudas', $respuestasExistentes['tiene_deudas'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('tiene_deudas')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="seccion_deudas" class="d-none">
    <div class="form-group">
        <label for="detalle_deudas" class="form-label">
            Detalle de Deudas
        </label>
        <textarea class="form-control @error('detalle_deudas') is-invalid @enderror" 
                  id="detalle_deudas" 
                  name="detalle_deudas" 
                  rows="4"
                  placeholder="Indique: Tipo de deuda, institución, monto pendiente, pago mensual...">{{ old('detalle_deudas', $respuestasExistentes['detalle_deudas'] ?? '') }}</textarea>
        @error('detalle_deudas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="tiene_ahorros" class="form-label">
        ¿Tiene ahorros o inversiones? <span class="required">*</span>
    </label>
    <select class="form-control @error('tiene_ahorros') is-invalid @enderror" 
            id="tiene_ahorros" 
            name="tiene_ahorros" 
            required>
        <option value="">Seleccione...</option>
        <option value="si" {{ old('tiene_ahorros', $respuestasExistentes['tiene_ahorros'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ old('tiene_ahorros', $respuestasExistentes['tiene_ahorros'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('tiene_ahorros')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="observaciones_economicas" class="form-label">
        Observaciones sobre su situación económica
    </label>
    <textarea class="form-control @error('observaciones_economicas') is-invalid @enderror" 
              id="observaciones_economicas" 
              name="observaciones_economicas" 
              rows="3"
              placeholder="Información adicional relevante sobre su situación financiera...">{{ old('observaciones_economicas', $respuestasExistentes['observaciones_economicas'] ?? '') }}</textarea>
    @error('observaciones_economicas')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tieneDeudas = document.getElementById('tiene_deudas');
    const seccionDeudas = document.getElementById('seccion_deudas');
    
    // Campos de ingresos
    const ingresosPrincipales = document.getElementById('ingresos_principales');
    const ingresosAdicionales = document.getElementById('ingresos_adicionales');
    const ingresosFamiliares = document.getElementById('ingresos_familiares');
    const totalIngresos = document.getElementById('total_ingresos');
    
    // Campos de gastos
    const gastosVivienda = document.getElementById('gastos_vivienda');
    const gastosAlimentacion = document.getElementById('gastos_alimentacion');
    const gastosTransporte = document.getElementById('gastos_transporte');
    const gastosEducacion = document.getElementById('gastos_educacion');
    const gastosSalud = document.getElementById('gastos_salud');
    const gastosOtros = document.getElementById('gastos_otros');
    const totalGastos = document.getElementById('total_gastos');
    
    const balanceMensual = document.getElementById('balance_mensual');
    
    // Mostrar/ocultar sección de deudas
    function toggleSeccionDeudas() {
        if (tieneDeudas.value === 'si') {
            seccionDeudas.classList.remove('d-none');
        } else {
            seccionDeudas.classList.add('d-none');
            document.getElementById('detalle_deudas').value = '';
        }
    }
    
    // Calcular totales
    function calcularTotales() {
        // Calcular total de ingresos
        const ingPrincipales = parseFloat(ingresosPrincipales.value) || 0;
        const ingAdicionales = parseFloat(ingresosAdicionales.value) || 0;
        const ingFamiliares = parseFloat(ingresosFamiliares.value) || 0;
        const totalIng = ingPrincipales + ingAdicionales + ingFamiliares;
        totalIngresos.value = totalIng.toFixed(2);
        
        // Calcular total de gastos
        const gastVivienda = parseFloat(gastosVivienda.value) || 0;
        const gastAlimentacion = parseFloat(gastosAlimentacion.value) || 0;
        const gastTransporte = parseFloat(gastosTransporte.value) || 0;
        const gastEducacion = parseFloat(gastosEducacion.value) || 0;
        const gastSalud = parseFloat(gastosSalud.value) || 0;
        const gastOtros = parseFloat(gastosOtros.value) || 0;
        const totalGast = gastVivienda + gastAlimentacion + gastTransporte + gastEducacion + gastSalud + gastOtros;
        totalGastos.value = totalGast.toFixed(2);
        
        // Calcular balance
        const balance = totalIng - totalGast;
        balanceMensual.value = balance.toFixed(2);
        
        // Cambiar color según el balance
        if (balance < 0) {
            balanceMensual.style.color = 'red';
        } else if (balance > 0) {
            balanceMensual.style.color = 'green';
        } else {
            balanceMensual.style.color = 'black';
        }
    }
    
    // Event listeners
    tieneDeudas.addEventListener('change', toggleSeccionDeudas);
    
    // Event listeners para cálculos automáticos
    const camposIngresos = [ingresosPrincipales, ingresosAdicionales, ingresosFamiliares];
    const camposGastos = [gastosVivienda, gastosAlimentacion, gastosTransporte, gastosEducacion, gastosSalud, gastosOtros];
    
    [...camposIngresos, ...camposGastos].forEach(campo => {
        campo.addEventListener('input', calcularTotales);
    });
    
    // Inicializar estado al cargar
    toggleSeccionDeudas();
    calcularTotales();
});
</script>
@endpush