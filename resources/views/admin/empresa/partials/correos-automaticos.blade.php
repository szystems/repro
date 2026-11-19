@php
    $encendido = filter_var(
        old('correos_automaticos', ($empresa?->recibeCorreosAutomaticos() ?? true) ? '1' : '0'),
        FILTER_VALIDATE_BOOLEAN
    );
    $comentarios = filter_var(
        old('correos_comentarios', ($empresa?->recibeCorreosComentarios() ?? false) ? '1' : '0'),
        FILTER_VALIDATE_BOOLEAN
    );
@endphp
<div class="col-md-12 mb-3">
    <input type="hidden" name="correos_automaticos" value="0">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="correos_automaticos" name="correos_automaticos" value="1" @checked($encendido)>
        <label class="form-check-label" for="correos_automaticos">Enviar correos automáticos a esta empresa</label>
    </div>
    <small class="text-muted d-block mt-1">
        Encendido: el candidato recibe enlace, recordatorio y cita, y la empresa recibe el correo de resultados.
        Apagado: esos correos no salen. Siguen la campana del portal, WhatsApp, la contraseña y el aviso a la sede de REPRO.
        Este interruptor no amplía el cupo del servicio de correo.
    </small>
    <div class="ms-4 mt-3">
        <input type="hidden" name="correos_comentarios" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="correos_comentarios" name="correos_comentarios" value="1" @checked($comentarios)>
            <label class="form-check-label" for="correos_comentarios">Avisar por correo cada comentario nuevo</label>
        </div>
        <small class="text-muted d-block mt-1">
            Apagado por defecto. Solo manda el correo, y solo si los correos automáticos están encendidos.
            La campana del portal llega igual a quienes pueden ver la orden, sin gastar cupo.
            El correo va al responsable (reclutador, quien creó la orden o el titular).
            Corregir un comentario ya guardado no manda otra campana ni otro correo.
        </small>
    </div>
</div>
