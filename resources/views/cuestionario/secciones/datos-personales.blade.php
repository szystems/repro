{{-- Sección 1: Datos Personales --}}

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información personal básica y datos de contacto</strong>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="nombres_completos" class="form-label">
                Nombres Completos <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('nombres_completos') is-invalid @enderror" 
                   id="nombres_completos" 
                   name="nombres_completos" 
                   value="{{ old('nombres_completos', $respuestasExistentes['nombres_completos'] ?? $evaluado->nombre) }}"
                   required
                   maxlength="100">
            @error('nombres_completos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="apellidos_completos" class="form-label">
                Apellidos Completos <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('apellidos_completos') is-invalid @enderror" 
                   id="apellidos_completos" 
                   name="apellidos_completos" 
                   value="{{ old('apellidos_completos', $respuestasExistentes['apellidos_completos'] ?? $evaluado->apellidos) }}"
                   required
                   maxlength="100">
            @error('apellidos_completos')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="dpi" class="form-label">
                DPI <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('dpi') is-invalid @enderror" 
                   id="dpi" 
                   name="dpi" 
                   value="{{ old('dpi', $respuestasExistentes['dpi'] ?? $evaluado->dpi) }}"
                   required
                   maxlength="13"
                   pattern="[0-9]{13}"
                   readonly>
            <div class="form-text">Este campo no puede ser modificado</div>
            @error('dpi')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group">
            <label for="fecha_nacimiento" class="form-label">
                Fecha de Nacimiento <span class="required">*</span>
            </label>
            <input type="date" 
                   class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                   id="fecha_nacimiento" 
                   name="fecha_nacimiento" 
                   value="{{ old('fecha_nacimiento', $respuestasExistentes['fecha_nacimiento'] ?? '') }}"
                   required
                   max="{{ date('Y-m-d', strtotime('-18 years')) }}">
            @error('fecha_nacimiento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group">
            <label for="edad" class="form-label">
                Edad
            </label>
            <input type="number" 
                   class="form-control" 
                   id="edad" 
                   name="edad" 
                   readonly>
            <div class="form-text">Se calcula automáticamente</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="genero" class="form-label">
                Género <span class="required">*</span>
            </label>
            <select class="form-control @error('genero') is-invalid @enderror" 
                    id="genero" 
                    name="genero" 
                    required>
                <option value="">Seleccione...</option>
                <option value="masculino" {{ old('genero', $respuestasExistentes['genero'] ?? '') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                <option value="femenino" {{ old('genero', $respuestasExistentes['genero'] ?? '') == 'femenino' ? 'selected' : '' }}>Femenino</option>
            </select>
            @error('genero')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="estado_civil" class="form-label">
                Estado Civil <span class="required">*</span>
            </label>
            <select class="form-control @error('estado_civil') is-invalid @enderror" 
                    id="estado_civil" 
                    name="estado_civil" 
                    required>
                <option value="">Seleccione...</option>
                <option value="soltero" {{ old('estado_civil', $respuestasExistentes['estado_civil'] ?? '') == 'soltero' ? 'selected' : '' }}>Soltero(a)</option>
                <option value="casado" {{ old('estado_civil', $respuestasExistentes['estado_civil'] ?? '') == 'casado' ? 'selected' : '' }}>Casado(a)</option>
                <option value="union_libre" {{ old('estado_civil', $respuestasExistentes['estado_civil'] ?? '') == 'union_libre' ? 'selected' : '' }}>Unión Libre</option>
                <option value="divorciado" {{ old('estado_civil', $respuestasExistentes['estado_civil'] ?? '') == 'divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
                <option value="viudo" {{ old('estado_civil', $respuestasExistentes['estado_civil'] ?? '') == 'viudo' ? 'selected' : '' }}>Viudo(a)</option>
            </select>
            @error('estado_civil')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="nacionalidad" class="form-label">
                Nacionalidad <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('nacionalidad') is-invalid @enderror" 
                   id="nacionalidad" 
                   name="nacionalidad" 
                   value="{{ old('nacionalidad', $respuestasExistentes['nacionalidad'] ?? 'Guatemalteca') }}"
                   required
                   maxlength="50">
            @error('nacionalidad')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="lugar_nacimiento" class="form-label">
                Lugar de Nacimiento <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('lugar_nacimiento') is-invalid @enderror" 
                   id="lugar_nacimiento" 
                   name="lugar_nacimiento" 
                   value="{{ old('lugar_nacimiento', $respuestasExistentes['lugar_nacimiento'] ?? '') }}"
                   required
                   maxlength="100"
                   placeholder="Ciudad, Departamento, País">
            @error('lugar_nacimiento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="telefono_personal" class="form-label">
                Teléfono Personal <span class="required">*</span>
            </label>
            <input type="tel" 
                   class="form-control @error('telefono_personal') is-invalid @enderror" 
                   id="telefono_personal" 
                   name="telefono_personal" 
                   value="{{ old('telefono_personal', $respuestasExistentes['telefono_personal'] ?? $evaluado->celular) }}"
                   required
                   maxlength="15"
                   pattern="[0-9+\-\s()]+"
                   placeholder="Ej: 5555-1234 o +502 5555-1234">
            @error('telefono_personal')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="telefono_alternativo" class="form-label">
                Teléfono Alternativo
            </label>
            <input type="tel" 
                   class="form-control @error('telefono_alternativo') is-invalid @enderror" 
                   id="telefono_alternativo" 
                   name="telefono_alternativo" 
                   value="{{ old('telefono_alternativo', $respuestasExistentes['telefono_alternativo'] ?? $evaluado->telefono) }}"
                   maxlength="15"
                   pattern="[0-9+\-\s()]+"
                   placeholder="Ej: 2222-3333 (opcional)">
            @error('telefono_alternativo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="email_personal" class="form-label">
        Correo Electrónico Personal <span class="required">*</span>
    </label>
    <input type="email" 
           class="form-control @error('email_personal') is-invalid @enderror" 
           id="email_personal" 
           name="email_personal" 
           value="{{ old('email_personal', $respuestasExistentes['email_personal'] ?? $evaluado->email) }}"
           required
           maxlength="100">
    @error('email_personal')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="direccion_residencia" class="form-label">
        Dirección de Residencia Actual <span class="required">*</span>
    </label>
    <textarea class="form-control @error('direccion_residencia') is-invalid @enderror" 
              id="direccion_residencia" 
              name="direccion_residencia" 
              rows="3"
              required
              maxlength="500"
              placeholder="Indique la dirección completa donde vive actualmente">{{ old('direccion_residencia', $respuestasExistentes['direccion_residencia'] ?? '') }}</textarea>
    @error('direccion_residencia')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="municipio" class="form-label">
                Municipio <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('municipio') is-invalid @enderror" 
                   id="municipio" 
                   name="municipio" 
                   value="{{ old('municipio', $respuestasExistentes['municipio'] ?? '') }}"
                   required
                   maxlength="50">
            @error('municipio')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group">
            <label for="departamento" class="form-label">
                Departamento <span class="required">*</span>
            </label>
            <select class="form-control @error('departamento') is-invalid @enderror" 
                    id="departamento" 
                    name="departamento" 
                    required>
                <option value="">Seleccione...</option>
                <option value="Alta Verapaz" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Alta Verapaz' ? 'selected' : '' }}>Alta Verapaz</option>
                <option value="Baja Verapaz" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Baja Verapaz' ? 'selected' : '' }}>Baja Verapaz</option>
                <option value="Chimaltenango" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Chimaltenango' ? 'selected' : '' }}>Chimaltenango</option>
                <option value="Chiquimula" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Chiquimula' ? 'selected' : '' }}>Chiquimula</option>
                <option value="El Progreso" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'El Progreso' ? 'selected' : '' }}>El Progreso</option>
                <option value="Escuintla" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Escuintla' ? 'selected' : '' }}>Escuintla</option>
                <option value="Guatemala" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Guatemala' ? 'selected' : '' }}>Guatemala</option>
                <option value="Huehuetenango" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Huehuetenango' ? 'selected' : '' }}>Huehuetenango</option>
                <option value="Izabal" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Izabal' ? 'selected' : '' }}>Izabal</option>
                <option value="Jalapa" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Jalapa' ? 'selected' : '' }}>Jalapa</option>
                <option value="Jutiapa" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Jutiapa' ? 'selected' : '' }}>Jutiapa</option>
                <option value="Petén" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Petén' ? 'selected' : '' }}>Petén</option>
                <option value="Quetzaltenango" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Quetzaltenango' ? 'selected' : '' }}>Quetzaltenango</option>
                <option value="Quiché" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Quiché' ? 'selected' : '' }}>Quiché</option>
                <option value="Retalhuleu" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Retalhuleu' ? 'selected' : '' }}>Retalhuleu</option>
                <option value="Sacatepéquez" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Sacatepéquez' ? 'selected' : '' }}>Sacatepéquez</option>
                <option value="San Marcos" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'San Marcos' ? 'selected' : '' }}>San Marcos</option>
                <option value="Santa Rosa" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Santa Rosa' ? 'selected' : '' }}>Santa Rosa</option>
                <option value="Sololá" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Sololá' ? 'selected' : '' }}>Sololá</option>
                <option value="Suchitepéquez" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Suchitepéquez' ? 'selected' : '' }}>Suchitepéquez</option>
                <option value="Totonicapán" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Totonicapán' ? 'selected' : '' }}>Totonicapán</option>
                <option value="Zacapa" {{ old('departamento', $respuestasExistentes['departamento'] ?? '') == 'Zacapa' ? 'selected' : '' }}>Zacapa</option>
            </select>
            @error('departamento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="nivel_educativo" class="form-label">
                Nivel Educativo <span class="required">*</span>
            </label>
            <select class="form-control @error('nivel_educativo') is-invalid @enderror" 
                    id="nivel_educativo" 
                    name="nivel_educativo" 
                    required>
                <option value="">Seleccione...</option>
                <option value="primaria_incompleta" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'primaria_incompleta' ? 'selected' : '' }}>Primaria Incompleta</option>
                <option value="primaria_completa" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'primaria_completa' ? 'selected' : '' }}>Primaria Completa</option>
                <option value="basicos_incompletos" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'basicos_incompletos' ? 'selected' : '' }}>Básicos Incompletos</option>
                <option value="basicos_completos" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'basicos_completos' ? 'selected' : '' }}>Básicos Completos</option>
                <option value="diversificado_incompleto" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'diversificado_incompleto' ? 'selected' : '' }}>Diversificado Incompleto</option>
                <option value="diversificado_completo" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'diversificado_completo' ? 'selected' : '' }}>Diversificado Completo</option>
                <option value="universidad_incompleta" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'universidad_incompleta' ? 'selected' : '' }}>Universidad Incompleta</option>
                <option value="universidad_completa" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'universidad_completa' ? 'selected' : '' }}>Universidad Completa</option>
                <option value="posgrado" {{ old('nivel_educativo', $respuestasExistentes['nivel_educativo'] ?? '') == 'posgrado' ? 'selected' : '' }}>Posgrado</option>
            </select>
            @error('nivel_educativo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="form-group">
            <label for="profesion_oficio" class="form-label">
                Profesión u Oficio <span class="required">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('profesion_oficio') is-invalid @enderror" 
                   id="profesion_oficio" 
                   name="profesion_oficio" 
                   value="{{ old('profesion_oficio', $respuestasExistentes['profesion_oficio'] ?? '') }}"
                   required
                   maxlength="100"
                   placeholder="Ej: Contador, Técnico en Computación, etc.">
            @error('profesion_oficio')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaNacimiento = document.getElementById('fecha_nacimiento');
    const edad = document.getElementById('edad');
    
    // Calcular edad automáticamente
    function calcularEdad() {
        if (fechaNacimiento.value) {
            const nacimiento = new Date(fechaNacimiento.value);
            const hoy = new Date();
            let edadCalculada = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                edadCalculada--;
            }
            
            edad.value = edadCalculada;
        } else {
            edad.value = '';
        }
    }
    
    // Event listener para calcular edad
    fechaNacimiento.addEventListener('change', calcularEdad);
    
    // Formatear DPI
    const dpi = document.getElementById('dpi');
    dpi.addEventListener('input', function() {
        // Remover cualquier carácter que no sea número
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limitar a 13 dígitos
        if (this.value.length > 13) {
            this.value = this.value.slice(0, 13);
        }
    });
    
    // Formatear teléfonos
    const telefonos = document.querySelectorAll('input[type="tel"]');
    telefonos.forEach(function(telefono) {
        telefono.addEventListener('input', function() {
            // Permitir solo números, espacios, guiones, paréntesis y signo +
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    });
    
    // Validar edad mínima (18 años)
    fechaNacimiento.addEventListener('change', function() {
        const nacimiento = new Date(this.value);
        const hace18Anos = new Date();
        hace18Anos.setFullYear(hace18Anos.getFullYear() - 18);
        
        if (nacimiento > hace18Anos) {
            alert('Debe ser mayor de 18 años para completar este formulario.');
            this.value = '';
            edad.value = '';
        } else {
            calcularEdad();
        }
    });
    
    // Calcular edad al cargar si ya hay fecha
    calcularEdad();
});
</script>
@endpush