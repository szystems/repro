{{-- Partial para editar Sección 2 --}}
@php
    $tipoFormulario = $tipoFormulario ?? 'preempleo';
    $nombreSeccion = $nombreSeccion ?? 'Información Familiar';
@endphp
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-people"></i> {{ $nombreSeccion }}
    </h6>
    
    @if(in_array($tipoFormulario, ['periodica']))
        {{-- Campos para tipo PERIÓDICA - Cambios Familiares --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="estado_civil_detalle" class="form-label">Estado Civil Actual</label>
                    <select class="form-control @error('respuestas.estado_civil_detalle') is-invalid @enderror" 
                            id="estado_civil_detalle" 
                            name="respuestas[estado_civil_detalle]">
                        <option value="">Seleccione...</option>
                        <option value="soltero" {{ ($respuestas['estado_civil_detalle'] ?? '') == 'soltero' ? 'selected' : '' }}>Soltero/a</option>
                        <option value="casado" {{ ($respuestas['estado_civil_detalle'] ?? '') == 'casado' ? 'selected' : '' }}>Casado/a</option>
                        <option value="union_libre" {{ ($respuestas['estado_civil_detalle'] ?? '') == 'union_libre' ? 'selected' : '' }}>Unión Libre</option>
                        <option value="divorciado" {{ ($respuestas['estado_civil_detalle'] ?? '') == 'divorciado' ? 'selected' : '' }}>Divorciado/a</option>
                        <option value="viudo" {{ ($respuestas['estado_civil_detalle'] ?? '') == 'viudo' ? 'selected' : '' }}>Viudo/a</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="vive_con_pareja" class="form-label">¿Vive con su pareja?</label>
                    <select class="form-control" id="vive_con_pareja" name="respuestas[vive_con_pareja]">
                        <option value="">Seleccione...</option>
                        <option value="si" {{ ($respuestas['vive_con_pareja'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($respuestas['vive_con_pareja'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="pareja_trabaja" class="form-label">¿Su pareja trabaja?</label>
                    <select class="form-control" id="pareja_trabaja" name="respuestas[pareja_trabaja]">
                        <option value="">Seleccione...</option>
                        <option value="si" {{ ($respuestas['pareja_trabaja'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($respuestas['pareja_trabaja'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tiene_hijos" class="form-label">¿Tiene hijos?</label>
                    <select class="form-control" id="tiene_hijos" name="respuestas[tiene_hijos]">
                        <option value="">Seleccione...</option>
                        <option value="si" {{ ($respuestas['tiene_hijos'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($respuestas['tiene_hijos'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="numero_hijos" class="form-label">Número de Hijos</label>
                    <input type="number" class="form-control" id="numero_hijos" 
                           name="respuestas[numero_hijos]" 
                           value="{{ $respuestas['numero_hijos'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="hijos_dependientes" class="form-label">Hijos Dependientes</label>
                    <input type="number" class="form-control" id="hijos_dependientes" 
                           name="respuestas[hijos_dependientes]" 
                           value="{{ $respuestas['hijos_dependientes'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="hijos_menores" class="form-label">Hijos Menores de Edad</label>
                    <input type="number" class="form-control" id="hijos_menores" 
                           name="respuestas[hijos_menores]" 
                           value="{{ $respuestas['hijos_menores'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="dependientes_economicos" class="form-label">Dependientes Económicos</label>
                    <input type="number" class="form-control" id="dependientes_economicos" 
                           name="respuestas[dependientes_economicos]" 
                           value="{{ $respuestas['dependientes_economicos'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="personas_hogar" class="form-label">Personas en el Hogar</label>
                    <input type="number" class="form-control" id="personas_hogar" 
                           name="respuestas[personas_hogar]" 
                           value="{{ $respuestas['personas_hogar'] ?? '' }}" min="1">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="personas_contribuyen_gastos" class="form-label">Personas que Contribuyen a Gastos</label>
                    <input type="number" class="form-control" id="personas_contribuyen_gastos" 
                           name="respuestas[personas_contribuyen_gastos]" 
                           value="{{ $respuestas['personas_contribuyen_gastos'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tipo_vivienda" class="form-label">Tipo de Vivienda</label>
                    <select class="form-control" id="tipo_vivienda" name="respuestas[tipo_vivienda]">
                        <option value="">Seleccione...</option>
                        <option value="propia" {{ ($respuestas['tipo_vivienda'] ?? '') == 'propia' ? 'selected' : '' }}>Propia</option>
                        <option value="alquilada" {{ ($respuestas['tipo_vivienda'] ?? '') == 'alquilada' ? 'selected' : '' }}>Alquilada</option>
                        <option value="familiar" {{ ($respuestas['tipo_vivienda'] ?? '') == 'familiar' ? 'selected' : '' }}>Familiar</option>
                        <option value="hipotecada" {{ ($respuestas['tipo_vivienda'] ?? '') == 'hipotecada' ? 'selected' : '' }}>Hipotecada</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="monto_alquiler" class="form-label">Monto Alquiler/Hipoteca (Q)</label>
                    <input type="number" class="form-control" id="monto_alquiler" 
                           name="respuestas[monto_alquiler]" 
                           value="{{ $respuestas['monto_alquiler'] ?? $respuestas['monto_hipoteca'] ?? '' }}" 
                           step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-12">
                <div class="form-group">
                    <label for="observaciones_familiares" class="form-label">Observaciones Familiares</label>
                    <textarea class="form-control" id="observaciones_familiares" 
                              name="respuestas[observaciones_familiares]" 
                              rows="3">{{ $respuestas['observaciones_familiares'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @else
        {{-- Campos para tipo PREEMPLEO y otros - Información Familiar estándar --}}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="estado_civil" class="form-label">Estado Civil</label>
                    <select class="form-control" id="estado_civil" name="respuestas[estado_civil]">
                        <option value="">Seleccione...</option>
                        <option value="soltero" {{ ($respuestas['estado_civil'] ?? '') == 'soltero' ? 'selected' : '' }}>Soltero/a</option>
                        <option value="casado" {{ ($respuestas['estado_civil'] ?? '') == 'casado' ? 'selected' : '' }}>Casado/a</option>
                        <option value="union_libre" {{ ($respuestas['union_libre'] ?? '') == 'union_libre' ? 'selected' : '' }}>Unión Libre</option>
                        <option value="divorciado" {{ ($respuestas['estado_civil'] ?? '') == 'divorciado' ? 'selected' : '' }}>Divorciado/a</option>
                        <option value="viudo" {{ ($respuestas['estado_civil'] ?? '') == 'viudo' ? 'selected' : '' }}>Viudo/a</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tiene_hijos" class="form-label">¿Tiene hijos?</label>
                    <select class="form-control" id="tiene_hijos" name="respuestas[tiene_hijos]">
                        <option value="">Seleccione...</option>
                        <option value="si" {{ ($respuestas['tiene_hijos'] ?? '') == 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($respuestas['tiene_hijos'] ?? '') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="numero_hijos" class="form-label">Número de Hijos</label>
                    <input type="number" class="form-control" id="numero_hijos" 
                           name="respuestas[numero_hijos]" 
                           value="{{ $respuestas['numero_hijos'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="dependientes_economicos" class="form-label">Dependientes Económicos</label>
                    <input type="number" class="form-control" id="dependientes_economicos" 
                           name="respuestas[dependientes_economicos]" 
                           value="{{ $respuestas['dependientes_economicos'] ?? '' }}" min="0">
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label for="personas_hogar" class="form-label">Personas en el Hogar</label>
                    <input type="number" class="form-control" id="personas_hogar" 
                           name="respuestas[personas_hogar]" 
                           value="{{ $respuestas['personas_hogar'] ?? '' }}" min="1">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tipo_vivienda" class="form-label">Tipo de Vivienda</label>
                    <select class="form-control" id="tipo_vivienda" name="respuestas[tipo_vivienda]">
                        <option value="">Seleccione...</option>
                        <option value="propia" {{ ($respuestas['tipo_vivienda'] ?? '') == 'propia' ? 'selected' : '' }}>Propia</option>
                        <option value="alquilada" {{ ($respuestas['tipo_vivienda'] ?? '') == 'alquilada' ? 'selected' : '' }}>Alquilada</option>
                        <option value="familiar" {{ ($respuestas['tipo_vivienda'] ?? '') == 'familiar' ? 'selected' : '' }}>Familiar</option>
                        <option value="hipotecada" {{ ($respuestas['tipo_vivienda'] ?? '') == 'hipotecada' ? 'selected' : '' }}>Hipotecada</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="monto_alquiler" class="form-label">Monto Alquiler/Hipoteca (Q)</label>
                    <input type="number" class="form-control" id="monto_alquiler" 
                           name="respuestas[monto_alquiler]" 
                           value="{{ $respuestas['monto_alquiler'] ?? '' }}" 
                           step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-12">
                <div class="form-group">
                    <label for="observaciones_familiares" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones_familiares" 
                              name="respuestas[observaciones_familiares]" 
                              rows="3">{{ $respuestas['observaciones_familiares'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @endif
</div>
