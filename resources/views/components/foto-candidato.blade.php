@props(['fotoUrl' => null, 'requerido' => true])

{{-- Estilos inline: @push('styles') no llega al <head> (content se rinde después del stack). --}}
@once
<style>
    .foto-candidato-preview {
        border: 2px solid var(--repro-yellow, #ffb000);
        border-radius: 8px;
    }
    .foto-candidato-box {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 0.75rem;
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    }
    .foto-candidato-group.is-invalid .foto-candidato-box {
        border-color: #dc3545;
        background-color: rgba(220, 53, 69, 0.08);
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.2);
    }
    .foto-candidato-group.is-invalid .foto-candidato-preview-wrap:not(.d-none) {
        outline: 2px solid #dc3545;
        border-radius: 8px;
    }
    .foto-candidato-group.is-invalid [data-foto-trigger="camera"],
    .foto-candidato-group.is-invalid [data-foto-trigger="upload"] {
        border-color: #dc3545;
        color: #dc3545;
    }
    .foto-candidato-group.is-invalid [data-foto-error] {
        display: block !important;
        color: #dc3545;
        font-weight: 600;
        margin-top: 0.35rem;
    }
    #fotoWebcamModal .modal-body {
        background: #111;
        text-align: center;
    }
    #fotoWebcamVideo {
        width: 100%;
        max-height: 420px;
        object-fit: cover;
        border-radius: 8px;
        background: #000;
        transform: scaleX(-1);
    }
    #fotoWebcamError {
        display: none;
    }
</style>
@endonce

<div class="form-group foto-candidato-group @error('foto_candidato') is-invalid @enderror" data-foto-candidato @unless($requerido) data-foto-opcional="1" @endunless>
    <label class="form-label">
        Fotografía del candidato @if($requerido)<span class="required">*</span>@endif
    </label>
    <p class="form-text mb-3">Tome una fotografía de medio cuerpo con la cámara web o del celular, o suba una imagen reciente (JPG, PNG o WEBP, máx. 5 MB).</p>

    <div class="foto-candidato-box" data-foto-box>
        <div class="foto-candidato-preview-wrap mb-3 {{ ($fotoUrl ?? null) ? '' : 'd-none' }}" data-foto-preview-wrap>
            <img src="{{ $fotoUrl ?? '' }}"
                 alt="Vista previa foto candidato"
                 class="foto-candidato-preview img-thumbnail"
                 data-foto-preview
                 style="max-width: 240px; max-height: 320px; object-fit: cover;">
        </div>

        @if($fotoUrl ?? null)
            <input type="hidden" name="foto_candidato_existente" value="1" data-foto-existente>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-2" data-foto-actions>
            <button type="button" class="btn btn-outline-primary btn-sm" data-foto-trigger="camera">
                <i class="bi bi-camera"></i> Tomar foto
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-foto-trigger="upload">
                <i class="bi bi-upload"></i> Subir archivo
            </button>
        </div>
    </div>

    <input type="file"
           id="foto_candidato"
           name="foto_candidato"
           class="d-none @error('foto_candidato') is-invalid @enderror"
           accept="image/jpeg,image/png,image/webp,image/jpg"
           data-foto-input
           aria-describedby="foto_candidato_error">

    <div id="foto_candidato_error"
         class="invalid-feedback @error('foto_candidato') d-block @enderror"
         data-foto-error
         @if($errors->has('foto_candidato')) data-server-error="1" @endif
         @unless($errors->has('foto_candidato')) style="display: none;" @endunless>
        {{ $errors->first('foto_candidato') ?: 'Debe tomar o subir su fotografía para continuar.' }}
    </div>
</div>

@once
@push('scripts')
<div class="modal fade" id="fotoWebcamModal" tabindex="-1" aria-labelledby="fotoWebcamModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fotoWebcamModalLabel">
                    <i class="bi bi-camera"></i> Tomar fotografía
                </h5>
                <button type="button" class="btn-close" data-foto-webcam-close aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="fotoWebcamError" class="alert alert-warning mb-3"></div>
                <div id="fotoWebcamLoading" class="text-white-50 py-4">
                    <div class="spinner-border text-light mb-2" role="status">
                        <span class="visually-hidden">Cargando…</span>
                    </div>
                    <p class="mt-2 mb-0 small">Conectando con la cámara…</p>
                </div>
                <video id="fotoWebcamVideo" autoplay playsinline muted class="d-none"></video>
                <p class="text-white-50 small mt-2 mb-0">Centre su rostro en el recuadro y pulse Capturar.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-foto-webcam-close>Cancelar</button>
                <button type="button" class="btn btn-primary" id="fotoWebcamCapture" disabled>
                    <i class="bi bi-record-circle"></i> Capturar
                </button>
            </div>
        </div>
    </div>
</div>
@endpush
@endonce
