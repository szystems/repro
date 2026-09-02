{{-- E2.2 — Bloque padre o madre --}}
@props([
    'prefijo',
    'titulo',
    'respuestas' => [],
])

@php
    $val = fn (string $campo) => old($campo, $respuestas[$campo] ?? '');
@endphp

<div class="card mb-3 border-secondary">
    <div class="card-header py-2">
        <strong>{{ $titulo }}</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8">
                <div class="form-group mb-3">
                    <label for="{{ $prefijo }}_nombre" class="form-label">
                        Nombre completo de {{ strtolower($titulo) }} <span class="required">*</span>
                    </label>
                    <input type="text"
                           class="form-control @error($prefijo.'_nombre') is-invalid @enderror"
                           id="{{ $prefijo }}_nombre"
                           name="{{ $prefijo }}_nombre"
                           value="{{ $val($prefijo.'_nombre') }}"
                           required
                           maxlength="100">
                    @error($prefijo.'_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group mb-3">
                    <label for="{{ $prefijo }}_vive" class="form-label">
                        ¿Vive? <span class="required">*</span>
                    </label>
                    <select class="form-control @error($prefijo.'_vive') is-invalid @enderror"
                            id="{{ $prefijo }}_vive"
                            name="{{ $prefijo }}_vive"
                            required>
                        <option value="">Seleccione...</option>
                        <option value="si" {{ $val($prefijo.'_vive') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ $val($prefijo.'_vive') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                    @error($prefijo.'_vive')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <x-campo-condicional :trigger="$prefijo.'_vive'" show-when="si" :id="'detalle_'.$prefijo">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="{{ $prefijo }}_edad" class="form-label">Edad <span class="required">*</span></label>
                        <input type="number"
                               class="form-control @error($prefijo.'_edad') is-invalid @enderror"
                               id="{{ $prefijo }}_edad"
                               name="{{ $prefijo }}_edad"
                               value="{{ $val($prefijo.'_edad') }}"
                               min="1"
                               max="120"
                               data-condicional-required-trigger="{{ $prefijo }}_vive"
                               data-condicional-required-when="si">
                        @error($prefijo.'_edad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="{{ $prefijo }}_telefono" class="form-label">Teléfono</label>
                        <input type="tel"
                               class="form-control @error($prefijo.'_telefono') is-invalid @enderror"
                               id="{{ $prefijo }}_telefono"
                               name="{{ $prefijo }}_telefono"
                               value="{{ $val($prefijo.'_telefono') }}"
                               maxlength="15">
                        @error($prefijo.'_telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="{{ $prefijo }}_direccion" class="form-label">Dirección <span class="required">*</span></label>
                <textarea class="form-control @error($prefijo.'_direccion') is-invalid @enderror"
                          id="{{ $prefijo }}_direccion"
                          name="{{ $prefijo }}_direccion"
                          rows="2"
                          maxlength="500"
                          data-condicional-required-trigger="{{ $prefijo }}_vive"
                          data-condicional-required-when="si">{{ $val($prefijo.'_direccion') }}</textarea>
                @error($prefijo.'_direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="{{ $prefijo }}_ocupacion" class="form-label">Ocupación <span class="required">*</span></label>
                        <input type="text"
                               class="form-control @error($prefijo.'_ocupacion') is-invalid @enderror"
                               id="{{ $prefijo }}_ocupacion"
                               name="{{ $prefijo }}_ocupacion"
                               value="{{ $val($prefijo.'_ocupacion') }}"
                               maxlength="100"
                               data-condicional-required-trigger="{{ $prefijo }}_vive"
                               data-condicional-required-when="si">
                        @error($prefijo.'_ocupacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="{{ $prefijo }}_lugar_trabajo" class="form-label">Lugar de trabajo</label>
                        <input type="text"
                               class="form-control @error($prefijo.'_lugar_trabajo') is-invalid @enderror"
                               id="{{ $prefijo }}_lugar_trabajo"
                               name="{{ $prefijo }}_lugar_trabajo"
                               value="{{ $val($prefijo.'_lugar_trabajo') }}"
                               maxlength="150">
                        @error($prefijo.'_lugar_trabajo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </x-campo-condicional>
    </div>
</div>
