{{-- Sprint E §3.10 — asignación reclutador + proceso confidencial --}}
@php
    $mostrarCampos = (Auth::user()->role_as == 1 && (int) Auth::user()->principal === 1)
        || Auth::user()->role_as >= 2;
    $reclutadores = $reclutadores ?? collect();
@endphp

@if($mostrarCampos)
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">
            Reclutador asignado
            <small class="text-muted">(opcional)</small>
        </label>
        <select class="form-select @error('reclutador_id') is-invalid @enderror" name="reclutador_id" id="reclutador_id">
            <option value="">Sin asignar — visible según modo de la empresa</option>
            @foreach($reclutadores as $reclutador)
            <option value="{{ $reclutador->id }}"
                {{ (string) old('reclutador_id', $orden->reclutador_id ?? '') === (string) $reclutador->id ? 'selected' : '' }}>
                {{ $reclutador->name }}{{ (int) $reclutador->principal === 1 ? ' (gerente RRHH)' : '' }}
            </option>
            @endforeach
        </select>
        @error('reclutador_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">
            Define quién gestiona el proceso. Los demás reclutadores no lo verán si está marcado como confidencial.
            Elija primero la empresa para ver su personal.
        </small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label d-block">Visibilidad entre reclutadores</label>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input @error('confidencial') is-invalid @enderror"
                   type="checkbox"
                   role="switch"
                   name="confidencial"
                   id="confidencial"
                   value="1"
                   {{ old('confidencial', !empty($orden) && $orden->confidencial) ? 'checked' : '' }}>
            <label class="form-check-label" for="confidencial">
                Proceso <strong>confidencial</strong> (solo gerente RRHH y reclutador asignado)
            </label>
        </div>
        @error('confidencial')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
@endif
