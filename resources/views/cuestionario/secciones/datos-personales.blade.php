{{-- E2.1 — Sección 1 Pre-empleo: Datos generales (21 campos) --}}

@php
    use App\Support\CuestionarioPrecarga;
    use App\Support\DatosPersonalesCampos;
    $prec = $precarga ?? [];
    $resp = $respuestasExistentes ?? [];
    $val = fn (string $campo, mixed $fallback = '') => CuestionarioPrecarga::valorParaCampo($campo, $prec, $resp, $fallback);
    $tipoId = old('tipo_identificacion', $val('tipo_identificacion', 'dpi'));
@endphp

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Datos generales del candidato</strong>
    <div class="small mt-1 mb-0">Los datos de la orden aparecen precargados. Puede corregir teléfono, correo y dirección si es necesario.</div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label text-muted small">Empresa solicitante</label>
        <input type="text" class="form-control bg-light" value="{{ $val('empresa_solicitante') }}" readonly tabindex="-1">
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted small">Agencia / región</label>
        <input type="text" class="form-control bg-light" value="{{ $val('agencia_region') }}" readonly tabindex="-1">
    </div>
    <div class="col-md-4">
        <label class="form-label text-muted small">Puesto a evaluar</label>
        <input type="text" class="form-control bg-light" value="{{ $val('puesto_evaluar', 'No especificado') }}" readonly tabindex="-1">
    </div>
</div>

<x-foto-candidato :foto-url="$fotoCandidatoUrl ?? null" />

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="nombres_completos" class="form-label">Nombres completos <span class="required">*</span></label>
            <input type="text"
                   class="form-control @error('nombres_completos') is-invalid @enderror"
                   id="nombres_completos"
                   name="nombres_completos"
                   value="{{ old('nombres_completos', $val('nombres_completos')) }}"
                   required
                   maxlength="100">
            @error('nombres_completos')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label for="apellidos_completos" class="form-label">Apellidos completos <span class="required">*</span></label>
            <input type="text"
                   class="form-control @error('apellidos_completos') is-invalid @enderror"
                   id="apellidos_completos"
                   name="apellidos_completos"
                   value="{{ old('apellidos_completos', $val('apellidos_completos')) }}"
                   required
                   maxlength="100">
            @error('apellidos_completos')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="tipo_identificacion" class="form-label">Tipo de identificación <span class="required">*</span></label>
            <select class="form-control @error('tipo_identificacion') is-invalid @enderror"
                    id="tipo_identificacion"
                    name="tipo_identificacion"
                    required>
                <option value="">Seleccione...</option>
                @foreach(DatosPersonalesCampos::TIPOS_IDENTIFICACION as $clave => $etiqueta)
                    <option value="{{ $clave }}" {{ $tipoId === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('tipo_identificacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-8">
        <div class="form-group">
            <label for="dpi" class="form-label">Número de identificación <span class="required">*</span></label>
            <input type="text"
                   class="form-control @error('dpi') is-invalid @enderror"
                   id="dpi"
                   name="dpi"
                   value="{{ old('dpi', $val('dpi', $evaluado->dpi)) }}"
                   required
                   maxlength="30">
            <div class="form-text" id="dpi_ayuda">Con DPI, este número coincide con el registrado en su orden.</div>
            @error('dpi')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento <span class="required">*</span></label>
            <input type="date"
                   class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                   id="fecha_nacimiento"
                   name="fecha_nacimiento"
                   value="{{ old('fecha_nacimiento', $resp['fecha_nacimiento'] ?? '') }}"
                   required
                   max="{{ date('Y-m-d', strtotime('-18 years')) }}">
            @error('fecha_nacimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="edad" class="form-label">Edad</label>
            <input type="number" class="form-control" id="edad" name="edad" value="{{ old('edad', $resp['edad'] ?? '') }}" readonly tabindex="-1">
            <div class="form-text">Se calcula automáticamente</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="nacionalidad" class="form-label">Nacionalidad <span class="required">*</span></label>
            <input type="text"
                   class="form-control @error('nacionalidad') is-invalid @enderror"
                   id="nacionalidad"
                   name="nacionalidad"
                   value="{{ old('nacionalidad', $resp['nacionalidad'] ?? 'Guatemala') }}"
                   required
                   maxlength="100">
            @error('nacionalidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="estado_civil" class="form-label">Estado civil <span class="required">*</span></label>
            <select class="form-control @error('estado_civil') is-invalid @enderror" id="estado_civil" name="estado_civil" required>
                <option value="">Seleccione...</option>
                @foreach(['soltero' => 'Soltero(a)', 'casado' => 'Casado(a)', 'union_libre' => 'Unión libre', 'divorciado' => 'Divorciado(a)', 'viudo' => 'Viudo(a)'] as $v => $l)
                    <option value="{{ $v }}" {{ old('estado_civil', $resp['estado_civil'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            @error('estado_civil')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label for="email_personal" class="form-label">Correo electrónico <span class="required">*</span></label>
            <input type="email"
                   class="form-control @error('email_personal') is-invalid @enderror"
                   id="email_personal"
                   name="email_personal"
                   value="{{ old('email_personal', $val('email_personal')) }}"
                   required
                   maxlength="100">
            @error('email_personal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="telefono_personal" class="form-label">Teléfono personal <span class="required">*</span></label>
            <input type="tel"
                   class="form-control @error('telefono_personal') is-invalid @enderror"
                   id="telefono_personal"
                   name="telefono_personal"
                   value="{{ old('telefono_personal', $val('telefono_personal')) }}"
                   required
                   maxlength="15"
                   pattern="[0-9+\-\s()]+">
            @error('telefono_personal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label for="telefono_alternativo" class="form-label">Teléfono de emergencia</label>
            <input type="tel"
                   class="form-control @error('telefono_alternativo') is-invalid @enderror"
                   id="telefono_alternativo"
                   name="telefono_alternativo"
                   value="{{ old('telefono_alternativo', $val('telefono_alternativo')) }}"
                   maxlength="15"
                   pattern="[0-9+\-\s()]+"
                   placeholder="Contacto en caso de emergencia">
            @error('telefono_alternativo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<h6 class="text-secondary mt-3 mb-2">Lugar de nacimiento</h6>
<div class="row">
    <div class="col-12">
        <x-depto-municipio-select
            :catalogo-gt="$catalogoGt ?? []"
            :departamento-seleccionado="old('departamento_nacimiento', $resp['departamento_nacimiento'] ?? '')"
            :municipio-seleccionado="old('municipio_nacimiento', $resp['municipio_nacimiento'] ?? '')"
            name-departamento="departamento_nacimiento"
            name-municipio="municipio_nacimiento"
            id-departamento="departamento_nacimiento"
            id-municipio="municipio_nacimiento"
            label-departamento="Departamento de nacimiento"
            label-municipio="Municipio de nacimiento"
        />
    </div>
</div>

<div class="form-group mt-3">
    <label for="direccion_residencia" class="form-label">Dirección de residencia actual <span class="required">*</span></label>
    <textarea class="form-control @error('direccion_residencia') is-invalid @enderror"
              id="direccion_residencia"
              name="direccion_residencia"
              rows="3"
              required
              maxlength="500"
              placeholder="Indique la dirección completa donde vive actualmente">{{ old('direccion_residencia', $val('direccion_residencia')) }}</textarea>
    @error('direccion_residencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<h6 class="text-secondary mt-3 mb-2">Lugar de residencia</h6>
<div class="row">
    <div class="col-12">
        <x-depto-municipio-select
            :catalogo-gt="$catalogoGt ?? []"
            :departamento-seleccionado="old('departamento', $resp['departamento'] ?? '')"
            :municipio-seleccionado="old('municipio', $resp['municipio'] ?? '')"
            label-departamento="Departamento de residencia"
            label-municipio="Municipio de residencia"
        />
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-4">
        <div class="form-group">
            <label for="igss" class="form-label">No. IGSS</label>
            <input type="text"
                   class="form-control @error('igss') is-invalid @enderror"
                   id="igss"
                   name="igss"
                   value="{{ old('igss', $resp['igss'] ?? '') }}"
                   maxlength="30"
                   placeholder="Opcional">
            @error('igss')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="nit" class="form-label">NIT</label>
            <input type="text"
                   class="form-control @error('nit') is-invalid @enderror"
                   id="nit"
                   name="nit"
                   value="{{ old('nit', $resp['nit'] ?? '') }}"
                   maxlength="20"
                   placeholder="Opcional">
            @error('nit')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label for="licencia_conducir" class="form-label">Licencia de conducir <span class="required">*</span></label>
            <select class="form-control @error('licencia_conducir') is-invalid @enderror"
                    id="licencia_conducir"
                    name="licencia_conducir"
                    required>
                <option value="">Seleccione...</option>
                @foreach(DatosPersonalesCampos::LICENCIA_CONDUCIR as $clave => $etiqueta)
                    <option value="{{ $clave }}" {{ old('licencia_conducir', $resp['licencia_conducir'] ?? '') === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('licencia_conducir')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/depto-municipio-select.js') }}"></script>
<script src="{{ asset('js/foto-candidato.js') }}?v={{ filemtime(public_path('js/foto-candidato.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaNacimiento = document.getElementById('fecha_nacimiento');
    const edad = document.getElementById('edad');
    const tipoIdentificacion = document.getElementById('tipo_identificacion');
    const dpi = document.getElementById('dpi');
    const dpiAyuda = document.getElementById('dpi_ayuda');
    const dpiOrden = @json($evaluado->dpi ?? '');

    function calcularEdad() {
        if (!fechaNacimiento.value) {
            edad.value = '';
            return;
        }
        const nacimiento = new Date(fechaNacimiento.value);
        const hoy = new Date();
        let edadCalculada = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edadCalculada--;
        }
        edad.value = edadCalculada;
    }

    function actualizarCampoIdentificacion() {
        const esDpi = tipoIdentificacion.value === 'dpi';
        dpi.readOnly = esDpi;
        dpi.maxLength = esDpi ? 13 : 30;
        if (esDpi) {
            dpi.value = dpiOrden;
            dpiAyuda.textContent = 'Con DPI, este número coincide con el registrado en su orden.';
        } else {
            dpiAyuda.textContent = 'Ingrese el número de pasaporte o documento extranjero.';
        }
    }

    fechaNacimiento.addEventListener('change', function() {
        const nacimiento = new Date(this.value);
        const hace18Anos = new Date();
        hace18Anos.setFullYear(hace18Anos.getFullYear() - 18);
        if (this.value && nacimiento > hace18Anos) {
            alert('Debe ser mayor de 18 años para completar este formulario.');
            this.value = '';
            edad.value = '';
        } else {
            calcularEdad();
        }
    });

    tipoIdentificacion.addEventListener('change', actualizarCampoIdentificacion);

    dpi.addEventListener('input', function() {
        if (tipoIdentificacion.value === 'dpi') {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
        }
    });

    document.querySelectorAll('input[type="tel"]').forEach(function(telefono) {
        telefono.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    });

    actualizarCampoIdentificacion();
    calcularEdad();
});
</script>
@endpush
