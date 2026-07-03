{{-- E2.6 — Exparejas --}}
@props(['respuestas' => []])
@php
    use App\Support\InformacionFamiliarExparejas;
    $val = fn (string $c) => old($c, $respuestas[$c] ?? '');
@endphp

<hr class="my-4">
<h6 class="text-primary mb-3"><i class="fas fa-user-minus"></i> Exparejas</h6>

<div class="form-group">
    <label for="tuvo_matrimonio_union_hijos" class="form-label">
        ¿Ha tenido matrimonio, unión libre o hijos en común con expareja? <span class="required">*</span>
    </label>
    <select class="form-control @error('tuvo_matrimonio_union_hijos') is-invalid @enderror"
            id="tuvo_matrimonio_union_hijos" name="tuvo_matrimonio_union_hijos" required>
        <option value="">Seleccione...</option>
        <option value="si" {{ $val('tuvo_matrimonio_union_hijos') === 'si' ? 'selected' : '' }}>Sí</option>
        <option value="no" {{ $val('tuvo_matrimonio_union_hijos') === 'no' ? 'selected' : '' }}>No</option>
    </select>
    @error('tuvo_matrimonio_union_hijos')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<x-campo-condicional trigger="tuvo_matrimonio_union_hijos" show-when="si" id="seccion_expareja">
    <div class="card border-secondary mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expareja_nombre" class="form-label">Nombre expareja <span class="required">*</span></label>
                        <input type="text" class="form-control @error('expareja_nombre') is-invalid @enderror"
                               id="expareja_nombre" name="expareja_nombre" value="{{ $val('expareja_nombre') }}"
                               data-condicional-required-trigger="tuvo_matrimonio_union_hijos" data-condicional-required-when="si">
                        @error('expareja_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expareja_tipo_relacion" class="form-label">Tipo relación <span class="required">*</span></label>
                        <select class="form-control @error('expareja_tipo_relacion') is-invalid @enderror"
                                id="expareja_tipo_relacion" name="expareja_tipo_relacion"
                                data-condicional-required-trigger="tuvo_matrimonio_union_hijos" data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            @foreach(InformacionFamiliarExparejas::TIPOS_RELACION as $k => $et)
                                <option value="{{ $k }}" {{ $val('expareja_tipo_relacion') === $k ? 'selected' : '' }}>{{ $et }}</option>
                            @endforeach
                        </select>
                        @error('expareja_tipo_relacion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expareja_tiempo_relacion" class="form-label">Tiempo de relación <span class="required">*</span></label>
                        <input type="text" class="form-control" id="expareja_tiempo_relacion" name="expareja_tiempo_relacion"
                               value="{{ $val('expareja_tiempo_relacion') }}"
                               data-condicional-required-trigger="tuvo_matrimonio_union_hijos" data-condicional-required-when="si">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expareja_hijos_comun" class="form-label">¿Hijos en común? <span class="required">*</span></label>
                        <select class="form-control" id="expareja_hijos_comun" name="expareja_hijos_comun"
                                data-condicional-required-trigger="tuvo_matrimonio_union_hijos" data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            <option value="si" {{ $val('expareja_hijos_comun') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ $val('expareja_hijos_comun') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>
            <x-campo-condicional trigger="expareja_hijos_comun" show-when="si">
                <div class="form-group">
                    <label for="expareja_cantidad_hijos" class="form-label">Cantidad de hijos en común</label>
                    <input type="number" class="form-control" id="expareja_cantidad_hijos" name="expareja_cantidad_hijos"
                           value="{{ $val('expareja_cantidad_hijos') }}" min="1" max="20">
                </div>
            </x-campo-condicional>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expareja_problemas_legales" class="form-label">¿Problemas legales? <span class="required">*</span></label>
                        <select class="form-control" id="expareja_problemas_legales" name="expareja_problemas_legales"
                                data-condicional-required-trigger="tuvo_matrimonio_union_hijos" data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            <option value="si" {{ $val('expareja_problemas_legales') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ $val('expareja_problemas_legales') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="expareja_apoyo_economico" class="form-label">¿Apoyo económico? <span class="required">*</span></label>
                        <select class="form-control" id="expareja_apoyo_economico" name="expareja_apoyo_economico"
                                data-condicional-required-trigger="tuvo_matrimonio_union_hijos" data-condicional-required-when="si">
                            <option value="">Seleccione...</option>
                            <option value="si" {{ $val('expareja_apoyo_economico') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ $val('expareja_apoyo_economico') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>
            <x-campo-condicional trigger="expareja_problemas_legales" show-when="si">
                <div class="form-group">
                    <label for="expareja_detalle_problemas" class="form-label">Detalle problemas legales</label>
                    <textarea class="form-control" id="expareja_detalle_problemas" name="expareja_detalle_problemas" rows="2">{{ $val('expareja_detalle_problemas') }}</textarea>
                </div>
            </x-campo-condicional>
            <div class="form-group mb-0">
                <label for="expareja_detalle_apoyo" class="form-label">Detalle apoyo económico (si aplica)</label>
                <textarea class="form-control" id="expareja_detalle_apoyo" name="expareja_detalle_apoyo" rows="2">{{ $val('expareja_detalle_apoyo') }}</textarea>
            </div>
        </div>
    </div>
</x-campo-condicional>
