@if(!($soloLectura ?? false) && \App\Support\AsistenteRedaccion::disponibleEn($slug))
    <button type="button"
            class="btn btn-sm btn-outline-primary mt-2 btn-asistente-redaccion"
            data-seccion="{{ $slug }}"
            data-titulo="{{ $titulo }}">
        Generar redacción
    </button>
@endif
