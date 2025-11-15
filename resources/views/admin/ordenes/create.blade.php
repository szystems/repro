@extends('layouts.admin')
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-file-earmark-plus"></i>
            </div>
            <div class="page-title">
                <h5>Nueva Orden de Evaluación</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        <div class="row gx-3">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Datos de la Orden</div>
                        <div class="card-options">
                            <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form id="form-orden" action="{{ route('ordenes.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Empresa (solo para admin/repro) -->
                                @if(Auth::user()->hasAnyRole(['admin', 'repro']))
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Empresa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('empresa_id') is-invalid @enderror" name="empresa_id" required>
                                        <option value="">Seleccionar empresa...</option>
                                        @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('empresa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @else
                                <!-- Usuario empresa: mostrar su empresa -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->empresa->nombre ?? 'No asignada' }}" readonly>
                                </div>
                                @endif

                                <!-- Prioridad -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prioridad</label>
                                    <select class="form-select @error('prioridad') is-invalid @enderror" name="prioridad">
                                        <option value="normal" {{ old('prioridad', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ old('prioridad') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
                                    @error('prioridad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fecha Límite -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Límite</label>
                                    <input type="date" class="form-control @error('fecha_limite') is-invalid @enderror" 
                                           name="fecha_limite" value="{{ old('fecha_limite') }}" min="{{ date('Y-m-d') }}">
                                    @error('fecha_limite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Observaciones Generales -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Observaciones Generales</label>
                                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                              name="observaciones" rows="2" placeholder="Observaciones generales para esta orden...">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Instrucciones Generales -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Instrucciones Generales</label>
                                    <textarea class="form-control @error('instrucciones_generales') is-invalid @enderror" 
                                              name="instrucciones_generales" rows="3" placeholder="Instrucciones que aplican a todos los evaluados de esta orden...">{{ old('instrucciones_generales') }}</textarea>
                                    @error('instrucciones_generales')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sección de Evaluados (Opcional) -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <div class="card-title">Evaluados (Opcional)</div>
                                    <div class="card-options">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarEvaluado()">
                                            <i class="bi bi-person-plus"></i> Agregar Evaluado
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-info-circle"></i> 
                                        Puede agregar los evaluados ahora o hacerlo después. Los evaluados recibirán un token único para completar el cuestionario.
                                    </p>

                                    <div id="evaluados-container">
                                        <!-- Los evaluados se agregan dinámicamente aquí -->
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Crear Orden
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

@endsection

@push('scripts')
<script>
let contadorEvaluados = 0;

function agregarEvaluado() {
    contadorEvaluados++;
    
    const html = `
        <div class="evaluado-item border rounded p-3 mb-3" id="evaluado-${contadorEvaluados}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Evaluado #${contadorEvaluados}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerEvaluado(${contadorEvaluados})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Nombres</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][nombre]" placeholder="Nombres" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Apellidos</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][apellidos]" placeholder="Apellidos" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">DPI</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][dpi]" 
                           placeholder="1234567890123" maxlength="13" pattern="[0-9]{13}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="evaluados[${contadorEvaluados}][email]" placeholder="evaluado@empresa.com" required>
                </div>
                
                <!-- Campos específicos por evaluado -->
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Servicio</label>
                    <select class="form-select" name="evaluados[${contadorEvaluados}][tipo_servicio]" required>
                        <option value="poligrafo">Polígrafo</option>
                        <option value="vsa">VSA</option>
                        <option value="socioeconomico">Socioeconómico</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Formulario</label>
                    <select class="form-select" name="evaluados[${contadorEvaluados}][tipo_formulario]" required>
                        <option value="preempleo">Pre-empleo</option>
                        <option value="periodica">Periódica</option>
                        <option value="especifica">Específica</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Fecha Programada</label>
                    <input type="date" class="form-control" name="evaluados[${contadorEvaluados}][fecha_programada]" min="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][telefono]" placeholder="23451234">
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('evaluados-container').insertAdjacentHTML('beforeend', html);
}

function removerEvaluado(id) {
    document.getElementById(`evaluado-${id}`).remove();
}

// JavaScript con logging detallado
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript cargado correctamente');
    
    // Agregar un evaluado por defecto si no hay ninguno
    const container = document.getElementById('evaluados-container');
    if (container && container.children.length === 0) {
        console.log('Agregando evaluado por defecto...');
        agregarEvaluado();
    }
    
    // Interceptar envío del formulario para debugging
    const form = document.getElementById('form-orden');
    if (form) {
        console.log('Formulario encontrado, agregando listener...');
        form.addEventListener('submit', function(e) {
            console.log('=== ENVÍO DE FORMULARIO ===');
            
            // Log de datos del formulario
            const formData = new FormData(form);
            console.log('Datos del formulario:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Verificar evaluados
            const evaluados = document.querySelectorAll('#evaluados-container .evaluado-item');
            console.log(`Número de evaluados: ${evaluados.length}`);
            
            if (evaluados.length === 0) {
                console.log('❌ Error: No hay evaluados');
                e.preventDefault();
                alert('Debe agregar al menos un evaluado.');
                return false;
            }
            
            console.log('✅ Formulario válido, enviando...');
            return true;
        });
    } else {
        console.log('❌ No se encontró el formulario');
    }
});
</script>
@endpush