@php
    $encendido = filter_var(
        old('correos_automaticos', ($empresa?->recibeCorreosAutomaticos() ?? true) ? '1' : '0'),
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
</div>
