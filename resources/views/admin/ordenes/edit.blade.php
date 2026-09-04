@extends(session('layout', 'layouts.admin'))
@section('content')

<!-- Content wrapper scroll start -->
<div class="content-wrapper-scroll">

    <!-- Main header starts -->
    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-pencil"></i>
            </div>
            <div class="page-title">
                <h5>Editar Orden: {{ $orden->codigo_orden }}</h5>
            </div>
        </div>
        <div class="d-flex align-items-end d-none d-sm-block">
            <h6 class="float-end text-light" id="reloj"></h6>
        </div>
    </div>
    <!-- Main header ends -->

    <!-- Content wrapper start -->
    <div class="content-wrapper">

        <!-- Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

            <form action="{{ route('ordenes.update', $orden) }}" method="POST" id="formEditarOrden">
            @csrf
            @method('PUT')
            
            <div class="row gx-3">
                <!-- Información Principal -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Información de la Orden</div>
                        </div>
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Código de Orden</label>
                                    <input type="text" class="form-control" value="{{ $orden->codigo_orden }}" disabled>
                                    <small class="text-muted">El código no se puede modificar</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Estado de Orden</label>
                                    <input type="text" class="form-control" value="{{ $estados[$orden->estado] ?? $orden->estado }}" disabled>
                                    <small class="text-muted">Use el panel lateral para cambiar el estado de la orden</small>
                                </div>
                            </div>

                            @if(Auth::user()->role_as >= 2)
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="empresa_id" class="form-label">Empresa *</label>
                                    <select class="form-select @error('empresa_id') is-invalid @enderror" name="empresa_id" id="empresa_id" required>
                                        <option value="">Seleccionar empresa...</option>
                                        @foreach($empresas as $empresa)
                                        <option value="{{ $empresa->id }}" {{ old('empresa_id', $orden->empresa_id) == $empresa->id ? 'selected' : '' }}>
                                            {{ $empresa->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('empresa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Polígrafos Asignados</label>
                                    <div class="form-control-plaintext">
                                        @php
                                            $poligrafistasAsignados = $orden->evaluados->whereNotNull('poligrafista_id')->pluck('poligrafista.name')->unique();
                                        @endphp
                                        @if($poligrafistasAsignados->isNotEmpty())
                                            @foreach($poligrafistasAsignados as $poligrafista)
                                                <span class="badge bg-info me-1">{{ $poligrafista }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Asignar por evaluado</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">Los polígrafos se asignan individualmente por evaluado</small>
                                </div>
                            </div>
                            @endif

                            @if(Auth::user()->role_as >= 2)
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prioridad" class="form-label">Prioridad</label>
                                    <select class="form-select @error('prioridad') is-invalid @enderror" name="prioridad" id="prioridad">
                                        <option value="baja" {{ old('prioridad', $orden->prioridad) == 'baja' ? 'selected' : '' }}>Baja</option>
                                        <option value="normal" {{ old('prioridad', $orden->prioridad) == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="alta" {{ old('prioridad', $orden->prioridad) == 'alta' ? 'selected' : '' }}>Alta</option>
                                        <option value="urgente" {{ old('prioridad', $orden->prioridad) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                    </select>
                                    @error('prioridad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Creación</label>
                                    <input type="text" class="form-control" value="{{ $orden->created_at ? $orden->created_at->format('d/m/Y H:i') : 'N/A' }}" readonly>
                                </div>
                                @if(isset($sedes) && $sedes->count())
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sede Responsable</label>
                                    <select class="form-select @error('sede_id') is-invalid @enderror" name="sede_id">
                                        <option value="">Sin sede asignada</option>
                                        @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}" {{ old('sede_id', $orden->sede_id) == $sede->id ? 'selected' : '' }}>
                                            {{ $sede->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('sede_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
                            </div>
                            @endif

                            @include('admin.ordenes._campos_reclutador_confidencial', ['orden' => $orden])

                            <div class="mb-3">
                                <label for="fecha_solicitud" class="form-label">Fecha de Solicitud</label>
                                <input type="date" class="form-control @error('fecha_solicitud') is-invalid @enderror" 
                                       name="fecha_solicitud" id="fecha_solicitud" 
                                       value="{{ old('fecha_solicitud', $orden->fecha_solicitud ? \Carbon\Carbon::parse($orden->fecha_solicitud)->format('Y-m-d') : '') }}">
                                @error('fecha_solicitud')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="mb-3">
                                <label for="instrucciones_generales" class="form-label">Instrucciones Generales</label>
                                <textarea class="form-control @error('instrucciones_generales') is-invalid @enderror" 
                                          name="instrucciones_generales" id="instrucciones_generales" rows="3" 
                                          placeholder="Instrucciones generales para todos los evaluados...">{{ old('instrucciones_generales', $orden->instrucciones_generales) }}</textarea>
                                @error('instrucciones_generales')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(Auth::user()->role_as >= 2)
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones Internas <small class="text-muted">(solo visible para REPRO)</small></label>
                                <textarea class="form-control @error('observaciones_internas') is-invalid @enderror" 
                                          name="observaciones_internas" id="observaciones" rows="3" 
                                          placeholder="Detalles adicionales de la orden...">{{ old('observaciones_internas', $orden->observaciones_internas) }}</textarea>
                                @error('observaciones_internas')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="requerimientos_generales" class="form-label">Requerimientos del Cliente <small class="text-muted">(solo editable por REPRO)</small></label>
                                <textarea class="form-control @error('requerimientos_generales') is-invalid @enderror" 
                                          name="requerimientos_generales" id="requerimientos_generales" rows="2" 
                                          placeholder="Requerimientos específicos del cliente...">{{ old('requerimientos_generales', $orden->requerimientos_generales) }}</textarea>
                                @error('requerimientos_generales')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                        </div>
                    </div>

                    <!-- Evaluados -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <div class="card-title">Evaluados</div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                El botón rojo quita a esa persona de <strong>esta</strong> orden al guardar.
                                No se puede dejar la orden vacía, ni quitar a alguien que ya llenó el formulario, tiene papelería o tiene informe.
                            </p>
                            <div id="evaluados-eliminar-inputs"></div>
                            <div id="evaluados-container">
                                @foreach($orden->evaluados as $index => $evaluado)
                                @php
                                    $puedeQuitarEvaluado = $evaluado->puedeEliminarseDeOrden();
                                    $motivoNoQuitar = $evaluado->motivoNoEliminableDeOrden();
                                @endphp
                                <div class="evaluado-item border rounded p-3 mb-3" data-index="{{ $index }}" data-evaluado-id="{{ $evaluado->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">Evaluado {{ $index + 1 }}</h6>
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm remove-evaluado"
                                                data-evaluado-id="{{ $evaluado->id }}"
                                                data-puede-quitar="{{ $puedeQuitarEvaluado ? '1' : '0' }}"
                                                data-motivo="{{ $motivoNoQuitar }}"
                                                @if(!$puedeQuitarEvaluado) disabled title="{{ $motivoNoQuitar }}" @endif>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <input type="hidden" name="evaluados[{{ $index }}][id]" value="{{ $evaluado->id }}">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Nombre *</label>
                                            <input type="text" class="form-control" name="evaluados[{{ $index }}][nombre]" 
                                                   value="{{ old('evaluados.'.$index.'.nombre', $evaluado->nombre) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Apellidos *</label>
                                            <input type="text" class="form-control" name="evaluados[{{ $index }}][apellidos]" 
                                                   value="{{ old('evaluados.'.$index.'.apellidos', $evaluado->apellidos) }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">DPI *</label>
                                            <input type="text" class="form-control" name="evaluados[{{ $index }}][dpi]" 
                                                   value="{{ old('evaluados.'.$index.'.dpi', $evaluado->dpi) }}" 
                                                   pattern="[0-9]{13}" maxlength="13" required>
                                            <small class="text-muted">13 dígitos sin espacios ni guiones</small>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="evaluados[{{ $index }}][email]" 
                                                   value="{{ old('evaluados.'.$index.'.email', $evaluado->email) }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Puesto a Evaluar</label>
                                            <input type="text" class="form-control" name="evaluados[{{ $index }}][puesto_evaluar]"
                                                   value="{{ old('evaluados.'.$index.'.puesto_evaluar', $evaluado->puesto_evaluar) }}"
                                                   placeholder="Ej: Gerente de Ventas" maxlength="100">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Sede REPRO</label>
                                            <select class="form-select" name="evaluados[{{ $index }}][sede_id]">
                                                <option value="">Sin sede</option>
                                                @foreach($sedes as $sede)
                                                <option value="{{ $sede->id }}" {{ old('evaluados.'.$index.'.sede_id', $evaluado->sede_id) == $sede->id ? 'selected' : '' }}>
                                                    {{ $sede->nombre }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" name="evaluados[{{ $index }}][telefono]" 
                                                   value="{{ old('evaluados.'.$index.'.telefono', $evaluado->telefono) }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Tipo de Servicio *</label>
                                            <select class="form-select" name="evaluados[{{ $index }}][tipo_servicio]" required>
                                                <option value="poligrafo" {{ old('evaluados.'.$index.'.tipo_servicio', $evaluado->tipo_servicio) == 'poligrafo' ? 'selected' : '' }}>Polígrafo</option>
                                                <option value="vsa" {{ old('evaluados.'.$index.'.tipo_servicio', $evaluado->tipo_servicio) == 'vsa' ? 'selected' : '' }}>VSA</option>
                                                <option value="socioeconomico" {{ old('evaluados.'.$index.'.tipo_servicio', $evaluado->tipo_servicio) == 'socioeconomico' ? 'selected' : '' }}>Socioeconómico</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Tipo de Formulario *</label>
                                            <select class="form-select" name="evaluados[{{ $index }}][tipo_formulario]" required>
                                                <option value="preempleo" {{ old('evaluados.'.$index.'.tipo_formulario', $evaluado->tipo_formulario) == 'preempleo' ? 'selected' : '' }}>Pre-empleo</option>
                                                <option value="periodica" {{ old('evaluados.'.$index.'.tipo_formulario', $evaluado->tipo_formulario) == 'periodica' ? 'selected' : '' }}>Periódica</option>
                                                <option value="especifica" {{ old('evaluados.'.$index.'.tipo_formulario', $evaluado->tipo_formulario) == 'especifica' ? 'selected' : '' }}>Específica</option>
                                            </select>
                                        </div>
                                        @if(Auth::user()->role_as >= 2)
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Modalidad</label>
                                            <select class="form-select" name="evaluados[{{ $index }}][modalidad]">
                                                <option value="presencial" {{ old('evaluados.'.$index.'.modalidad', $evaluado->modalidad ?? 'presencial') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                                                <option value="virtual" {{ old('evaluados.'.$index.'.modalidad', $evaluado->modalidad ?? 'presencial') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                                            </select>
                                            <small class="text-muted">La modalidad se guarda al guardar la orden o al programar la cita.</small>
                                        </div>
                                        @endif
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Dirección</label>
                                            <input type="text" class="form-control" name="evaluados[{{ $index }}][direccion]" 
                                                   value="{{ old('evaluados.'.$index.'.direccion', $evaluado->direccion) }}" maxlength="300"
                                                   placeholder="Dirección del evaluado">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Sede/Región Empresa</label>
                                            <input type="text" class="form-control" name="evaluados[{{ $index }}][sede_region_empresa]"
                                                   value="{{ old('evaluados.'.$index.'.sede_region_empresa', $evaluado->sede_region_empresa) }}"
                                                   placeholder="Ej: Regional Norte" maxlength="100">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Observaciones</label>
                                            <textarea class="form-control" name="evaluados[{{ $index }}][observaciones]" rows="1" 
                                                      maxlength="1000" placeholder="Observaciones sobre este evaluado...">{{ old('evaluados.'.$index.'.observaciones', $evaluado->observaciones) }}</textarea>
                                        </div>
                                    </div>

                                    @if(Auth::user()->role_as >= 2)
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Polígrafo Asignado</label>
                                            <select class="form-select" name="evaluados[{{ $index }}][poligrafista_id]">
                                                <option value="">Sin asignar</option>
                                                @foreach($poligrafistas as $poligrafista)
                                                <option value="{{ $poligrafista->id }}" {{ old('evaluados.'.$index.'.poligrafista_id', $evaluado->poligrafista_id) == $poligrafista->id ? 'selected' : '' }}>
                                                    {{ $poligrafista->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @endif

                                    @if($evaluado->cuestionario_completado)
                                    <div class="alert alert-info mt-2">
                                        <i class="bi bi-info-circle"></i>
                                        Este evaluado ya completó su cuestionario el {{ \Carbon\Carbon::parse($evaluado->completado_at)->format('d/m/Y H:i') }}.
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            
                            @if($orden->evaluados->count() == 0)
                            <div class="text-center text-muted py-4" id="no-evaluados">
                                <i class="bi bi-person-plus fs-1"></i><br>
                                No hay evaluados agregados.<br>
                                <small>Haga clic en "Agregar Evaluado" para comenzar.</small>
                            </div>
                            @endif

                            <div class="d-flex justify-content-center mt-3">
                                <button type="button" class="btn btn-primary" id="agregarEvaluado">
                                    <i class="bi bi-plus"></i> Agregar Evaluado
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Panel de Acciones -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Acciones</div>
                        </div>
                        <div class="card-body">
                            
                            <button type="submit" class="btn btn-success w-100 mb-2" id="btn-guardar-orden">
                                <span class="btn-text">
                                    <i class="bi bi-check-lg"></i> Guardar Cambios
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Procesando...
                                </span>
                            </button>
                            
                            <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-secondary w-100 mb-2" id="btn-ver-orden">
                                <i class="bi bi-eye"></i> Ver Orden
                            </a>
                            
                            <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Volver al Listado
                            </a>

                        </div>
                    </div>

                    <!-- Información del Estado de Orden -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <div class="card-title">Estado de Orden</div>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <span class="badge fs-6 bg-{{ $orden->estado_color }}">
                                    {{ $orden->estado_human }}
                                </span>
                            </div>
                            
                            @if(Auth::user()->role_as >= 2 && !in_array($orden->estado, ['entregado', 'cancelado']))
                            <hr>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Para cambiar el estado de la orden, vaya a la página de visualización de la orden.
                            </small>
                            @endif
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <div class="card-title">Información</div>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <strong>Creado:</strong> {{ $orden->created_at->format('d/m/Y H:i') }}<br>
                                <strong>Actualizado:</strong> {{ $orden->updated_at->format('d/m/Y H:i') }}<br>
                                <strong>Evaluados:</strong> {{ $orden->evaluados->count() }}
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
    <!-- Content wrapper end -->

</div>
<!-- Content wrapper scroll end -->

<!-- Template para nuevos evaluados -->
<div id="evaluado-template" style="display: none;">
    <div class="evaluado-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0">Evaluado <span class="evaluado-number"></span></h6>
            <button type="button" class="btn btn-outline-danger btn-sm remove-evaluado">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Nombre *</label>
                <input type="text" class="form-control evaluado-nombre" name="" required>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Apellidos *</label>
                <input type="text" class="form-control evaluado-apellidos" name="" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">DPI *</label>
                <input type="text" class="form-control evaluado-dpi" name="" 
                       pattern="[0-9]{13}" maxlength="13" required>
                <small class="text-muted">13 dígitos sin espacios ni guiones</small>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control evaluado-email" name="" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Puesto a Evaluar</label>
                <input type="text" class="form-control evaluado-puesto-evaluar" name="" placeholder="Ej: Gerente de Ventas" maxlength="100">
        </div>
        <div class="col-md-6 mb-2">
            <label class="form-label">Sede REPRO</label>
            <select class="form-select evaluado-sede-id" name="">
                <option value="">Sin sede</option>
                @foreach($sedes as $sede)
                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                @endforeach
            </select>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Teléfono</label>
                <input type="tel" class="form-control evaluado-telefono" name="">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Sede/Región Empresa</label>
                <input type="text" class="form-control evaluado-sede-region-empresa" name="" placeholder="Ej: Regional Norte" maxlength="100">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Tipo de Servicio *</label>
                <select class="form-select evaluado-tipo-servicio" name="" required>
                    <option value="poligrafo">Polígrafo</option>
                    <option value="vsa">VSA</option>
                    <option value="socioeconomico">Socioeconómico</option>
                </select>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Tipo de Formulario *</label>
                <select class="form-select evaluado-tipo-formulario" name="" required>
                    <option value="preempleo">Pre-empleo</option>
                    <option value="periodica">Periódica</option>
                    <option value="especifica">Específica</option>
                </select>
            </div>
            @if(Auth::user()->role_as >= 2)
            <div class="col-md-6 mb-2">
                <label class="form-label">Modalidad</label>
                <select class="form-select evaluado-modalidad" name="">
                    <option value="presencial" selected>Presencial</option>
                    <option value="virtual">Virtual</option>
                </select>
                <small class="text-muted">La modalidad se guarda al guardar la orden o al programar la cita.</small>
            </div>
            @endif
        </div>
        
        @if(Auth::user()->role_as >= 2)
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Polígrafo Asignado</label>
                <select class="form-select evaluado-poligrafista" name="">
                    <option value="">Sin asignar</option>
                    @foreach($poligrafistas as $poligrafista)
                    <option value="{{ $poligrafista->id }}">{{ $poligrafista->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/matriz-formulario-servicio.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let evaluadoIndex = {{ $orden->evaluados->count() }};

    if (window.MatrizFormularioServicioUI) {
        window.MatrizFormularioServicioUI.init(document);
    }
    
    // Función para actualizar números de evaluados
    function actualizarNumerosEvaluados() {
        const evaluados = document.querySelectorAll('.evaluado-item');
        evaluados.forEach((item, index) => {
            const number = index + 1;
            item.querySelector('.evaluado-number').textContent = number;
            item.dataset.index = index;
            
            // Actualizar names de inputs, selects y textareas
            item.querySelectorAll('input[name^="evaluados["], select[name^="evaluados["], textarea[name^="evaluados["]').forEach(el => {
                const match = el.name.match(/^evaluados\[\d+\]\[([^\]]+)\]$/);
                if (match) {
                    el.name = `evaluados[${index}][${match[1]}]`;
                }
            });
        });
        
        // Mostrar/ocultar mensaje de "no evaluados"
        const noEvaluados = document.getElementById('no-evaluados');
        if (noEvaluados) {
            noEvaluados.style.display = evaluados.length === 0 ? 'block' : 'none';
        }
    }
    
    // Agregar evaluado
    document.getElementById('agregarEvaluado').addEventListener('click', function() {
        const template = document.getElementById('evaluado-template');
        const container = document.getElementById('evaluados-container');
        
        const newEvaluado = template.cloneNode(true);
        newEvaluado.style.display = 'block';
        newEvaluado.removeAttribute('id');
        
        // Configurar los names de los inputs
        newEvaluado.querySelector('.evaluado-nombre').name = `evaluados[${evaluadoIndex}][nombre]`;
        newEvaluado.querySelector('.evaluado-apellidos').name = `evaluados[${evaluadoIndex}][apellidos]`;
        newEvaluado.querySelector('.evaluado-dpi').name = `evaluados[${evaluadoIndex}][dpi]`;
        newEvaluado.querySelector('.evaluado-email').name = `evaluados[${evaluadoIndex}][email]`;
        newEvaluado.querySelector('.evaluado-puesto-evaluar').name = `evaluados[${evaluadoIndex}][puesto_evaluar]`;
        newEvaluado.querySelector('.evaluado-sede-id').name = `evaluados[${evaluadoIndex}][sede_id]`;
        newEvaluado.querySelector('.evaluado-sede-region-empresa').name = `evaluados[${evaluadoIndex}][sede_region_empresa]`;
        newEvaluado.querySelector('.evaluado-telefono').name = `evaluados[${evaluadoIndex}][telefono]`;
        newEvaluado.querySelector('.evaluado-tipo-servicio').name = `evaluados[${evaluadoIndex}][tipo_servicio]`;
        newEvaluado.querySelector('.evaluado-tipo-formulario').name = `evaluados[${evaluadoIndex}][tipo_formulario]`;
        const modalidadEl = newEvaluado.querySelector('.evaluado-modalidad');
        if (modalidadEl) modalidadEl.name = `evaluados[${evaluadoIndex}][modalidad]`;
        @if(Auth::user()->role_as >= 2)
        const poligrafistaSelect = newEvaluado.querySelector('.evaluado-poligrafista');
        if (poligrafistaSelect) {
            poligrafistaSelect.name = `evaluados[${evaluadoIndex}][poligrafista_id]`;
        }
        @endif
        
        container.appendChild(newEvaluado);
        evaluadoIndex++;
        
        actualizarNumerosEvaluados();
        actualizarBotonesQuitarEvaluado();
        if (window.MatrizFormularioServicioUI) {
            window.MatrizFormularioServicioUI.sincronizarFila(newEvaluado);
        }
    });
    
    function actualizarBotonesQuitarEvaluado() {
        const items = document.querySelectorAll('#evaluados-container .evaluado-item');
        items.forEach((item) => {
            const btn = item.querySelector('.remove-evaluado');
            if (!btn) {
                return;
            }
            const bloqueado = btn.dataset.puedeQuitar === '0';
            if (items.length <= 1) {
                btn.disabled = true;
                btn.title = 'La orden debe quedar con al menos un evaluado.';
                return;
            }
            if (bloqueado) {
                btn.disabled = true;
                btn.title = btn.dataset.motivo || 'No se puede quitar este evaluado.';
                return;
            }
            btn.disabled = false;
            btn.title = 'Quitar este evaluado de la orden';
        });
    }

    // Remover evaluado
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-evaluado')) {
            e.preventDefault();
            const btn = e.target.closest('.remove-evaluado');
            const evaluadoItem = btn.closest('.evaluado-item');
            const restantes = document.querySelectorAll('#evaluados-container .evaluado-item').length;

            if (restantes <= 1) {
                alert('La orden debe quedar con al menos un evaluado.');
                return;
            }

            if (btn.dataset.puedeQuitar === '0') {
                alert(btn.dataset.motivo || 'No se puede quitar este evaluado.');
                return;
            }

            if (confirm('¿Quitar a este evaluado de la orden? Los demás se mantienen.')) {
                const evaluadoId = btn.dataset.evaluadoId || evaluadoItem.dataset.evaluadoId;
                if (evaluadoId) {
                    const holder = document.getElementById('evaluados-eliminar-inputs');
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'evaluados_eliminar[]';
                    input.value = evaluadoId;
                    holder.appendChild(input);
                }
                evaluadoItem.remove();
                actualizarNumerosEvaluados();
                actualizarBotonesQuitarEvaluado();
            }
        }
    });
    
    // Validación de DPI en tiempo real
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('evaluado-dpi') || e.target.name?.includes('[dpi]')) {
            // Solo permitir números
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            
            // Limitar a 13 caracteres
            if (e.target.value.length > 13) {
                e.target.value = e.target.value.substring(0, 13);
            }
        }
    });
    
    // Validación del formulario
    document.getElementById('formEditarOrden').addEventListener('submit', function(e) {
        const evaluados = document.querySelectorAll('.evaluado-item');
        let valid = true;
        
        if (evaluados.length === 0) {
            alert('Debe agregar al menos un evaluado.');
            e.preventDefault();
            return;
        }
        
        evaluados.forEach(function(item, index) {
            const nombre = item.querySelector('[name*="[nombre]"]').value.trim();
            const apellidos = item.querySelector('[name*="[apellidos]"]').value.trim();
            const dpi = item.querySelector('[name*="[dpi]"]').value.trim();
            const email = item.querySelector('[name*="[email]"]').value.trim();
            const tipoServicio = item.querySelector('[name*="[tipo_servicio]"]').value;
            const tipoFormulario = item.querySelector('[name*="[tipo_formulario]"]').value;
            
            if (!nombre) {
                alert(`El nombre del evaluado ${index + 1} es requerido.`);
                valid = false;
                return;
            }
            
            if (!apellidos) {
                alert(`Los apellidos del evaluado ${index + 1} son requeridos.`);
                valid = false;
                return;
            }
            
            if (!dpi || dpi.length !== 13) {
                alert(`El DPI del evaluado ${index + 1} debe tener exactamente 13 dígitos.`);
                valid = false;
                return;
            }
            
            if (!email) {
                alert(`El email del evaluado ${index + 1} es requerido.`);
                valid = false;
                return;
            }
            
            if (!tipoServicio) {
                alert(`El tipo de servicio del evaluado ${index + 1} es requerido.`);
                valid = false;
                return;
            }
            
            if (!tipoFormulario) {
                alert(`El tipo de formulario del evaluado ${index + 1} es requerido.`);
                valid = false;
                return;
            }
        });
        
        if (!valid) {
            e.preventDefault();
        } else {
            // Protección contra doble clic
            const btnGuardar = document.getElementById('btn-guardar-orden');
            const btnVerOrden = document.getElementById('btn-ver-orden');
            
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.querySelector('.btn-text').classList.add('d-none');
                btnGuardar.querySelector('.btn-loading').classList.remove('d-none');
            }
            
            if (btnVerOrden) {
                btnVerOrden.classList.add('disabled');
                btnVerOrden.style.pointerEvents = 'none';
            }
        }
    });

    actualizarBotonesQuitarEvaluado();
});
</script>
@include('admin.ordenes._js_reclutadores_empresa')

@endsection