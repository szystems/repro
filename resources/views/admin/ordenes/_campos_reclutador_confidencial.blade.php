{{-- Sprint E §3.10 — asignación reclutador + proceso confidencial --}}
@php
    $userCampos = Auth::user();
    $puedeAsignarReclutador = $userCampos->role_as >= 2 || (int) $userCampos->principal === 1;
    $puedeMarcarConfidencial = $userCampos->role_as >= 2
        || (int) $userCampos->principal === 1
        || ((int) $userCampos->role_as === 1 && (empty($orden) || (int) ($orden->creado_por ?? 0) === (int) $userCampos->id));
    $reclutadores = $reclutadores ?? collect();
@endphp

@if($puedeAsignarReclutador || $puedeMarcarConfidencial)
<div class="row">
    @if($puedeAsignarReclutador)
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
            Quién da seguimiento y quién recibe el correo de resultados / informe, aunque el proceso no sea confidencial.
            Los demás reclutadores siguen viendo la orden si no está marcada como confidencial.
            Elija primero la empresa para ver su personal.
        </small>
    </div>
    @endif

    @if($puedeMarcarConfidencial)
    <div class="{{ $puedeAsignarReclutador ? 'col-md-6' : 'col-md-12' }} mb-3">
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
                Proceso <strong>confidencial</strong> (solo gerente, creador y reclutador asignado lo ven en SIGOR; no cambia el destinatario del correo)
            </label>
        </div>
        @error('confidencial')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    @endif
</div>
@endif
