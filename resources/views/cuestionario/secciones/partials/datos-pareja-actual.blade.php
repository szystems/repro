{{-- E2.3 — Pareja actual --}}
@props(['respuestas' => []])

@php
    use App\Support\InformacionFamiliarPareja;
    $val = fn (string $campo) => old($campo, $respuestas[$campo] ?? '');
@endphp

<h6 class="text-primary mb-3 mt-2"><i class="fas fa-heart"></i> Pareja actual</h6>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label for="vive_con_pareja" class="form-label">
                ¿Tiene pareja actual? <span class="required">*</span>
            </label>
            <select class="form-control @error('vive_con_pareja') is-invalid @enderror"
                    id="vive_con_pareja"
                    name="vive_con_pareja"
                    required>
                <option value="">Seleccione...</option>
                <option value="si" {{ $val('vive_con_pareja') === 'si' ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ $val('vive_con_pareja') === 'no' ? 'selected' : '' }}>No</option>
            </select>
            @error('vive_con_pareja')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<x-campo-condicional trigger="vive_con_pareja" show-when="si" id="seccion_pareja_actual">
    <div class="card border-secondary mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="pareja_tipo_relacion" class="form-label">
                            Tipo de relación <span class="required">*</span>
                        </label>
                        <select class="form-control @error('pareja_tipo_relacion') is-invalid @enderror"
                                id="pareja_tipo_relacion"
                                name="pareja_tipo_relacion"
                                data-condicional-required-trigger="vive_con_pareja"
                                data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            @foreach(InformacionFamiliarPareja::TIPOS_RELACION as $clave => $etiqueta)
                                <option value="{{ $clave }}" {{ $val('pareja_tipo_relacion') === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('pareja_tipo_relacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="pareja_nombre" class="form-label">
                            Nombre completo <span class="required">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('pareja_nombre') is-invalid @enderror"
                               id="pareja_nombre"
                               name="pareja_nombre"
                               value="{{ $val('pareja_nombre') }}"
                               maxlength="100"
                               data-condicional-required-trigger="vive_con_pareja"
                               data-condicional-required-when="si">
                        @error('pareja_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="pareja_edad" class="form-label">Edad <span class="required">*</span></label>
                        <input type="number"
                               class="form-control @error('pareja_edad') is-invalid @enderror"
                               id="pareja_edad"
                               name="pareja_edad"
                               value="{{ $val('pareja_edad') }}"
                               min="16"
                               max="120"
                               data-condicional-required-trigger="vive_con_pareja"
                               data-condicional-required-when="si">
                        @error('pareja_edad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="pareja_telefono" class="form-label">Teléfono <span class="required">*</span></label>
                        <input type="tel"
                               class="form-control @error('pareja_telefono') is-invalid @enderror"
                               id="pareja_telefono"
                               name="pareja_telefono"
                               value="{{ $val('pareja_telefono') }}"
                               maxlength="15"
                               data-condicional-required-trigger="vive_con_pareja"
                               data-condicional-required-when="si">
                        @error('pareja_telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="pareja_direccion" class="form-label">Dirección <span class="required">*</span></label>
                <textarea class="form-control @error('pareja_direccion') is-invalid @enderror"
                          id="pareja_direccion"
                          name="pareja_direccion"
                          rows="2"
                          maxlength="500"
                          placeholder="Indique la dirección de residencia de su pareja"
                          data-condicional-required-trigger="vive_con_pareja"
                          data-condicional-required-when="si">{{ $val('pareja_direccion') }}</textarea>
                @error('pareja_direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="pareja_ocupacion" class="form-label">Ocupación <span class="required">*</span></label>
                        <input type="text"
                               class="form-control @error('pareja_ocupacion') is-invalid @enderror"
                               id="pareja_ocupacion"
                               name="pareja_ocupacion"
                               value="{{ $val('pareja_ocupacion') }}"
                               maxlength="100"
                               data-condicional-required-trigger="vive_con_pareja"
                               data-condicional-required-when="si">
                        @error('pareja_ocupacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="pareja_lugar_trabajo" class="form-label">Lugar de trabajo</label>
                        <input type="text"
                               class="form-control @error('pareja_lugar_trabajo') is-invalid @enderror"
                               id="pareja_lugar_trabajo"
                               name="pareja_lugar_trabajo"
                               value="{{ $val('pareja_lugar_trabajo') }}"
                               maxlength="150">
                        @error('pareja_lugar_trabajo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="pareja_trabaja" class="form-label">¿Trabaja? <span class="required">*</span></label>
                        <select class="form-control @error('pareja_trabaja') is-invalid @enderror"
                                id="pareja_trabaja"
                                name="pareja_trabaja"
                                data-condicional-required-trigger="vive_con_pareja"
                                data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            <option value="si" {{ $val('pareja_trabaja') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ $val('pareja_trabaja') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                        @error('pareja_trabaja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="pareja_tiempo_relacion" class="form-label">Tiempo de relación <span class="required">*</span></label>
                        <input type="text"
                               class="form-control @error('pareja_tiempo_relacion') is-invalid @enderror"
                               id="pareja_tiempo_relacion"
                               name="pareja_tiempo_relacion"
                               value="{{ $val('pareja_tiempo_relacion') }}"
                               maxlength="100"
                               placeholder="Ej: 5 años"
                               data-condicional-required-trigger="vive_con_pareja"
                               data-condicional-required-when="si">
                        @error('pareja_tiempo_relacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="pareja_calidad_relacion" class="form-label">Calidad de relación <span class="required">*</span></label>
                        <select class="form-control @error('pareja_calidad_relacion') is-invalid @enderror"
                                id="pareja_calidad_relacion"
                                name="pareja_calidad_relacion"
                                data-condicional-required-trigger="vive_con_pareja"
                                data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            @foreach(InformacionFamiliarPareja::CALIDAD_RELACION as $clave => $etiqueta)
                                <option value="{{ $clave }}" {{ $val('pareja_calidad_relacion') === $clave ? 'selected' : '' }}>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('pareja_calidad_relacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-campo-condicional>

<hr class="my-4">
