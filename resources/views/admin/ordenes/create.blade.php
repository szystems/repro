@extends(session('layout', 'layouts.admin'))
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
                                @if(Auth::user()->role_as >= 2)
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
                                <!-- Usuario empresa: enviar su empresa_id como hidden -->
                                <input type="hidden" name="empresa_id" value="{{ Auth::user()->empresa_id }}">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->empresa->nombre ?? 'No asignada' }}" readonly>
                                </div>
                                @endif

                                <!-- Prioridad (Solo REPRO) -->
                                @if(Auth::user()->role_as >= 2)
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
                                @endif

                                <!-- Observaciones Internas (Solo REPRO) -->
                                @if(Auth::user()->role_as >= 2)
                                <div class="col-12 mb-3">
                                    <label class="form-label">Observaciones Internas <small class="text-muted">(solo visible para REPRO)</small></label>
                                    <textarea class="form-control @error('observaciones_internas') is-invalid @enderror"
                                              name="observaciones_internas" rows="2" placeholder="Observaciones internas de esta orden...">{{ old('observaciones_internas') }}</textarea>
                                    @error('observaciones_internas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif

                                <!-- Instrucciones Generales -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Instrucciones Generales</label>
                                    <textarea class="form-control @error('instrucciones_generales') is-invalid @enderror"
                                              name="instrucciones_generales" rows="3" placeholder="Instrucciones que aplican a todos los evaluados de esta orden...">{{ old('instrucciones_generales') }}</textarea>
                                    @error('instrucciones_generales')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Requerimientos Generales (Solo REPRO) -->
                                @if(Auth::user()->role_as >= 2)
                                <div class="col-12 mb-3">
                                    <label class="form-label">Requerimientos del Cliente <small class="text-muted">(solo editable por REPRO)</small></label>
                                    <textarea class="form-control @error('requerimientos_generales') is-invalid @enderror"
                                              name="requerimientos_generales" rows="2" placeholder="Requerimientos específicos del cliente para esta orden...">{{ old('requerimientos_generales') }}</textarea>
                                    @error('requerimientos_generales')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
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
                                <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary me-2" id="btn-cancelar">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary" id="btn-crear-orden">
                                    <span class="btn-text">
                                        <i class="bi bi-check-circle"></i> Crear Orden
                                    </span>
                                    <span class="btn-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Procesando...
                                    </span>
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
const esUsuarioEmpresa = {{ Auth::user()->role_as == 1 ? 'true' : 'false' }};
const evaluadosOld = @json(old('evaluados', []));

function agregarEvaluado(datos = {}) {
    contadorEvaluados++;

    const nombre = datos.nombre || '';
    const apellidos = datos.apellidos || '';
    const dpi = datos.dpi || '';
    const email = datos.email || '';
    const telefono = datos.telefono || '';
    const direccion = datos.direccion || '';
    const observaciones = datos.observaciones || '';
    const tipoServicio = datos.tipo_servicio || 'poligrafo';
    const tipoFormulario = datos.tipo_formulario || 'preempleo';
    const fechaProgramada = datos.fecha_programada || '';

    // Solo mostrar fecha programada si NO es usuario empresa
    const fechaProgramadaHtml = esUsuarioEmpresa ? '' : `
                <div class="col-md-3 mb-2">
                    <label class="form-label">Fecha Programada</label>
                    <input type="date" class="form-control" name="evaluados[${contadorEvaluados}][fecha_programada]" min="{{ date('Y-m-d') }}" value="${fechaProgramada}">
                </div>`;

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
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][nombre]" placeholder="Nombres" value="${nombre}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Apellidos</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][apellidos]" placeholder="Apellidos" value="${apellidos}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">DPI</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][dpi]"
                           placeholder="1234567890123" maxlength="13" pattern="[0-9]{13}" value="${dpi}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="evaluados[${contadorEvaluados}][email]" placeholder="evaluado@empresa.com" value="${email}" required>
                </div>

                <!-- Campos específicos por evaluado -->
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Servicio</label>
                    <select class="form-select" name="evaluados[${contadorEvaluados}][tipo_servicio]" required>
                        <option value="poligrafo" ${tipoServicio === 'poligrafo' ? 'selected' : ''}>Polígrafo</option>
                        <option value="vsa" ${tipoServicio === 'vsa' ? 'selected' : ''}>VSA</option>
                        <option value="socioeconomico" ${tipoServicio === 'socioeconomico' ? 'selected' : ''}>Socioeconómico</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Tipo Formulario</label>
                    <select class="form-select" name="evaluados[${contadorEvaluados}][tipo_formulario]" required>
                        <option value="preempleo" ${tipoFormulario === 'preempleo' ? 'selected' : ''}>Pre-empleo</option>
                        <option value="periodica" ${tipoFormulario === 'periodica' ? 'selected' : ''}>Periódica</option>
                        <option value="especifica" ${tipoFormulario === 'especifica' ? 'selected' : ''}>Específica</option>
                    </select>
                </div>
                ${fechaProgramadaHtml}
                <div class="col-md-3 mb-2">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][telefono]" placeholder="23451234" value="${telefono}">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control" name="evaluados[${contadorEvaluados}][direccion]" placeholder="Dirección del evaluado" value="${direccion}" maxlength="300">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="evaluados[${contadorEvaluados}][observaciones]" rows="1" placeholder="Observaciones sobre este evaluado..." maxlength="1000">${observaciones}</textarea>
                </div>
            </div>
        </div>
    `;

    document.getElementById('evaluados-container').insertAdjacentHTML('beforeend', html);
}

function removerEvaluado(id) {
    document.getElementById(`evaluado-${id}`).remove();
}

document.addEventListener('DOMContentLoaded', function() {
    // Repoblar evaluados desde old() si hubo error de validación
    const oldKeys = Object.keys(evaluadosOld);
    if (oldKeys.length > 0) {
        oldKeys.forEach(function(key) {
            agregarEvaluado(evaluadosOld[key]);
        });
    } else {
        agregarEvaluado();
    }

    // Prevenir doble envío del formulario
    const form = document.getElementById('form-orden');
    const btnCrear = document.getElementById('btn-crear-orden');
    const btnCancelar = document.getElementById('btn-cancelar');
    let formSubmitting = false;

    if (form && btnCrear) {
        form.addEventListener('submit', function(e) {
            // Prevenir doble envío
            if (formSubmitting) {
                e.preventDefault();
                return false;
            }

            // Verificar evaluados
            const evaluados = document.querySelectorAll('#evaluados-container .evaluado-item');

            if (evaluados.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un evaluado.');
                return false;
            }

            // Marcar como enviando y bloquear botón
            formSubmitting = true;
            btnCrear.disabled = true;
            btnCrear.querySelector('.btn-text').classList.add('d-none');
            btnCrear.querySelector('.btn-loading').classList.remove('d-none');

            // También deshabilitar el botón cancelar
            if (btnCancelar) {
                btnCancelar.classList.add('disabled');
                btnCancelar.style.pointerEvents = 'none';
            }

            return true;
        });
    }
});
</script>
@endpush
