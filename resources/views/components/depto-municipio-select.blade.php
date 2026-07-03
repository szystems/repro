@if(!empty($catalogoGt))
<div class="depto-municipio-group"
     data-depto-municipio
     data-prefix="{{ $prefix ?? '' }}"
     data-catalogo='@json($catalogoGt)'
     data-otro-value="{{ \App\Support\GuatemalaCatalogo::OTRO_EXTRANJERO }}"
     data-selected-departamento="{{ $departamentoSeleccionado ?? '' }}"
     data-selected-municipio="{{ $municipioSeleccionado ?? '' }}">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="{{ $idDepartamento ?? 'departamento' }}" class="form-label">
                    {{ $labelDepartamento ?? 'Departamento' }} <span class="required">*</span>
                </label>
                <select class="form-control depto-municipio-departamento @error($nameDepartamento ?? 'departamento') is-invalid @enderror"
                        id="{{ $idDepartamento ?? 'departamento' }}"
                        data-role="departamento"
                        required>
                    <option value="">Seleccione...</option>
                </select>
                <input type="hidden"
                       name="{{ $nameDepartamento ?? 'departamento' }}"
                       id="{{ ($idDepartamento ?? 'departamento') }}_hidden"
                       value="{{ old($nameDepartamento ?? 'departamento', $departamentoSeleccionado ?? '') }}">
                @error($nameDepartamento ?? 'departamento')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group depto-municipio-municipio-wrap">
                <label for="{{ $idMunicipio ?? 'municipio' }}" class="form-label">
                    {{ $labelMunicipio ?? 'Municipio' }} <span class="required">*</span>
                </label>
                <select class="form-control depto-municipio-municipio @error($nameMunicipio ?? 'municipio') is-invalid @enderror"
                        id="{{ $idMunicipio ?? 'municipio' }}"
                        data-role="municipio"
                        required>
                    <option value="">Seleccione departamento primero...</option>
                </select>
                <input type="hidden"
                       name="{{ $nameMunicipio ?? 'municipio' }}"
                       id="{{ ($idMunicipio ?? 'municipio') }}_hidden"
                       value="{{ old($nameMunicipio ?? 'municipio', $municipioSeleccionado ?? '') }}">
                @error($nameMunicipio ?? 'municipio')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    <div class="row depto-municipio-extranjero-wrap d-none">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="{{ ($idDepartamento ?? 'departamento') }}_extranjero" class="form-label">
                    Departamento / Estado (extranjero) <span class="required">*</span>
                </label>
                <input type="text"
                       class="form-control depto-municipio-departamento-extranjero"
                       id="{{ ($idDepartamento ?? 'departamento') }}_extranjero"
                       maxlength="100"
                       placeholder="Ej: California, CDMX">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="{{ ($idMunicipio ?? 'municipio') }}_extranjero" class="form-label">
                    Ciudad / Municipio (extranjero) <span class="required">*</span>
                </label>
                <input type="text"
                       class="form-control depto-municipio-municipio-extranjero"
                       id="{{ ($idMunicipio ?? 'municipio') }}_extranjero"
                       maxlength="100"
                       placeholder="Ej: Los Angeles, Guadalajara">
            </div>
        </div>
    </div>
</div>
@endif
