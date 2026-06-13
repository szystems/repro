{{-- Partial compartido entre create y edit --}}

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $sede->nombre ?? '') }}" required maxlength="191"
               placeholder="Ej: Sede Central, Sede Zona 10">
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">Teléfono</label>
        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $sede->telefono ?? '') }}" maxlength="30"
               placeholder="2xxx-xxxx">
        @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold">WhatsApp</label>
        <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror"
               value="{{ old('whatsapp', $sede->whatsapp ?? '') }}" maxlength="30"
               placeholder="502 5xxx-xxxx">
        <div class="form-text">Número con código de país para enlace WhatsApp</div>
        @error('whatsapp')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label fw-semibold">Dirección</label>
        <textarea name="direccion" rows="2" class="form-control @error('direccion') is-invalid @enderror"
                  placeholder="Dirección completa de la sede">{{ old('direccion', $sede->direccion ?? '') }}</textarea>
        @error('direccion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label fw-semibold">Enlace Google Maps</label>
        <input type="url" name="enlace_maps" class="form-control @error('enlace_maps') is-invalid @enderror"
               value="{{ old('enlace_maps', $sede->enlace_maps ?? '') }}" maxlength="500"
               placeholder="https://maps.google.com/...">
        @error('enlace_maps')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-label fw-semibold">Capacidad</label>
        <input type="number" name="capacidad" min="1" max="999"
               class="form-control @error('capacidad') is-invalid @enderror"
               value="{{ old('capacidad', $sede->capacidad ?? 1) }}"
               title="Máximo de evaluaciones simultáneas">
        <div class="form-text">Evaluaciones simultáneas</div>
        @error('capacidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-label fw-semibold">Estado de Sede</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $sede->estado ?? 1) == 1 ? 'selected' : '' }}>Activa</option>
            <option value="0" {{ old('estado', $sede->estado ?? 1) == 0 ? 'selected' : '' }}>Inactiva</option>
        </select>
        @error('estado')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label fw-semibold">Notas internas</label>
        <textarea name="notas" rows="2" class="form-control @error('notas') is-invalid @enderror"
                  placeholder="Observaciones internas (solo visibles para REPRO)">{{ old('notas', $sede->notas ?? '') }}</textarea>
        @error('notas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
