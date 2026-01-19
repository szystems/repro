{{-- Partial para editar Sección 1: Datos Generales --}}
<div class="section-edit-content">
    <h6 class="text-primary mb-3">
        <i class="bi bi-person"></i> Datos Generales del Evaluado
    </h6>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.nombre') is-invalid @enderror" 
                       id="nombre" 
                       name="seccion_{{ $seccion }}[nombre]" 
                       value="{{ old('seccion_' . $seccion . '.nombre', $respuestas['nombre'] ?? '') }}"
                       required>
                @error('seccion_' . $seccion . '.nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.apellidos') is-invalid @enderror" 
                       id="apellidos" 
                       name="seccion_{{ $seccion }}[apellidos]" 
                       value="{{ old('seccion_' . $seccion . '.apellidos', $respuestas['apellidos'] ?? '') }}"
                       required>
                @error('seccion_' . $seccion . '.apellidos')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="dpi" class="form-label">DPI <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.dpi') is-invalid @enderror" 
                       id="dpi" 
                       name="seccion_{{ $seccion }}[dpi]" 
                       value="{{ old('seccion_' . $seccion . '.dpi', $respuestas['dpi'] ?? '') }}"
                       maxlength="13"
                       pattern="[0-9]{13}"
                       required>
                @error('seccion_' . $seccion . '.dpi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                <input type="date" 
                       class="form-control @error('seccion_' . $seccion . '.fecha_nacimiento') is-invalid @enderror" 
                       id="fecha_nacimiento" 
                       name="seccion_{{ $seccion }}[fecha_nacimiento]" 
                       value="{{ old('seccion_' . $seccion . '.fecha_nacimiento', $respuestas['fecha_nacimiento'] ?? '') }}">
                @error('seccion_' . $seccion . '.fecha_nacimiento')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="genero" class="form-label">Género</label>
                <select class="form-control @error('seccion_' . $seccion . '.genero') is-invalid @enderror" 
                        id="genero" 
                        name="seccion_{{ $seccion }}[genero]">
                    <option value="">Seleccione...</option>
                    <option value="masculino" {{ old('seccion_' . $seccion . '.genero', $respuestas['genero'] ?? '') == 'masculino' ? 'selected' : '' }}>
                        Masculino
                    </option>
                    <option value="femenino" {{ old('seccion_' . $seccion . '.genero', $respuestas['genero'] ?? '') == 'femenino' ? 'selected' : '' }}>
                        Femenino
                    </option>
                    <option value="otro" {{ old('seccion_' . $seccion . '.genero', $respuestas['genero'] ?? '') == 'otro' ? 'selected' : '' }}>
                        Otro
                    </option>
                </select>
                @error('seccion_' . $seccion . '.genero')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="estado_civil" class="form-label">Estado Civil</label>
                <select class="form-control @error('seccion_' . $seccion . '.estado_civil') is-invalid @enderror" 
                        id="estado_civil" 
                        name="seccion_{{ $seccion }}[estado_civil]">
                    <option value="">Seleccione...</option>
                    <option value="soltero" {{ old('seccion_' . $seccion . '.estado_civil', $respuestas['estado_civil'] ?? '') == 'soltero' ? 'selected' : '' }}>
                        Soltero/a
                    </option>
                    <option value="casado" {{ old('seccion_' . $seccion . '.estado_civil', $respuestas['estado_civil'] ?? '') == 'casado' ? 'selected' : '' }}>
                        Casado/a
                    </option>
                    <option value="divorciado" {{ old('seccion_' . $seccion . '.estado_civil', $respuestas['estado_civil'] ?? '') == 'divorciado' ? 'selected' : '' }}>
                        Divorciado/a
                    </option>
                    <option value="viudo" {{ old('seccion_' . $seccion . '.estado_civil', $respuestas['estado_civil'] ?? '') == 'viudo' ? 'selected' : '' }}>
                        Viudo/a
                    </option>
                    <option value="union_libre" {{ old('seccion_' . $seccion . '.estado_civil', $respuestas['estado_civil'] ?? '') == 'union_libre' ? 'selected' : '' }}>
                        Unión Libre
                    </option>
                </select>
                @error('seccion_' . $seccion . '.estado_civil')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" 
                       class="form-control @error('seccion_' . $seccion . '.email') is-invalid @enderror" 
                       id="email" 
                       name="seccion_{{ $seccion }}[email]" 
                       value="{{ old('seccion_' . $seccion . '.email', $respuestas['email'] ?? '') }}">
                @error('seccion_' . $seccion . '.email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="tel" 
                       class="form-control @error('seccion_' . $seccion . '.telefono') is-invalid @enderror" 
                       id="telefono" 
                       name="seccion_{{ $seccion }}[telefono]" 
                       value="{{ old('seccion_' . $seccion . '.telefono', $respuestas['telefono'] ?? '') }}">
                @error('seccion_' . $seccion . '.telefono')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="direccion" class="form-label">Dirección</label>
                <textarea class="form-control @error('seccion_' . $seccion . '.direccion') is-invalid @enderror" 
                          id="direccion" 
                          name="seccion_{{ $seccion }}[direccion]" 
                          rows="3">{{ old('seccion_' . $seccion . '.direccion', $respuestas['direccion'] ?? '') }}</textarea>
                @error('seccion_' . $seccion . '.direccion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="departamento" class="form-label">Departamento</label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.departamento') is-invalid @enderror" 
                       id="departamento" 
                       name="seccion_{{ $seccion }}[departamento]" 
                       value="{{ old('seccion_' . $seccion . '.departamento', $respuestas['departamento'] ?? '') }}">
                @error('seccion_' . $seccion . '.departamento')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="municipio" class="form-label">Municipio</label>
                <input type="text" 
                       class="form-control @error('seccion_' . $seccion . '.municipio') is-invalid @enderror" 
                       id="municipio" 
                       name="seccion_{{ $seccion }}[municipio]" 
                       value="{{ old('seccion_' . $seccion . '.municipio', $respuestas['municipio'] ?? '') }}">
                @error('seccion_' . $seccion . '.municipio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12">
            <div class="form-group">
                <label for="observaciones_personales" class="form-label">Observaciones Personales</label>
                <textarea class="form-control @error('seccion_' . $seccion . '.observaciones_personales') is-invalid @enderror" 
                          id="observaciones_personales" 
                          name="seccion_{{ $seccion }}[observaciones_personales]" 
                          rows="3"
                          placeholder="Información adicional relevante...">{{ old('seccion_' . $seccion . '.observaciones_personales', $respuestas['observaciones_personales'] ?? '') }}</textarea>
                @error('seccion_' . $seccion . '.observaciones_personales')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>