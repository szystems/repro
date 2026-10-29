@if(!($soloLectura ?? false) && \App\Support\AsistenteRedaccion::activo())
<div class="modal fade" id="asistenteRedaccionModal" tabindex="-1" aria-hidden="true"
     data-generar="{{ route('admin.cuestionarios.asistente-redaccion', $cuestionario) }}"
     data-aceptar="{{ route('admin.cuestionarios.asistente-redaccion.aceptar', $cuestionario) }}"
     data-csrf="{{ csrf_token() }}">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asistente de redacción — <span id="asistente-titulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Nada entra al informe hasta que acepte la redacción. Descartar deja el texto anterior como está.</p>
                <div class="mb-3">
                    <label class="form-label" for="asistente-modo">Qué desea hacer</label>
                    <select id="asistente-modo" class="form-select">
                        <option value="confirmada">Información confirmada, sin cambios</option>
                        <option value="observaciones">Agregar observaciones de la entrevista</option>
                    </select>
                </div>
                <div class="mb-3 d-none" id="asistente-observacion-grupo">
                    <label class="form-label" for="asistente-observacion">Observaciones de la entrevista</label>
                    <textarea id="asistente-observacion" class="form-control" rows="4" placeholder="Escriba o dicte solo la novedad…"></textarea>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="asistente-resto">
                        <label class="form-check-label" for="asistente-resto">El resto de la información fue confirmado sin cambios</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="asistente-dictar">Dictar en la nota</button>
                    <small class="text-muted d-block">El micrófono solo escribe en esta nota. No redacta el informe.</small>
                </div>
                <button type="button" class="btn btn-primary" id="asistente-generar">Generar</button>
                <div id="asistente-avisos" class="alert alert-warning mt-3 d-none"></div>
                <div id="asistente-error" class="alert alert-danger mt-3 d-none"></div>
                <div id="asistente-propuesta-grupo" class="mt-3 d-none">
                    <label class="form-label" for="asistente-propuesta">Vista previa. Puede editarla antes de aceptar.</label>
                    <textarea id="asistente-propuesta" class="form-control" rows="8"></textarea>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" class="btn btn-success" id="asistente-aceptar">Aceptar redacción</button>
                        <button type="button" class="btn btn-outline-primary" id="asistente-reescribir">Reescribir</button>
                        <button type="button" class="btn btn-outline-primary" id="asistente-otra">Otra versión</button>
                        <select id="asistente-estilo" class="form-select" style="max-width: 11rem;">
                            <option value="formal">Formal</option>
                            <option value="tecnica">Técnica</option>
                            <option value="breve">Breve</option>
                            <option value="detallada">Detallada</option>
                        </select>
                        <button type="button" class="btn btn-outline-primary" id="asistente-estilo-btn">Cambiar estilo</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Descartar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var raiz = document.getElementById('asistenteRedaccionModal');
    if (!raiz || typeof bootstrap === 'undefined') {
        return;
    }
    var modal = bootstrap.Modal.getOrCreateInstance(raiz);
    var seccionActual = '';
    var dictado = null;

    function campoNota() {
        if (seccionActual === 'word_recomendaciones') {
            return document.getElementById('word_recomendaciones');
        }
        return document.getElementById('word_bloque_' + seccionActual);
    }

    function mostrar(id, visible) {
        document.getElementById(id).classList.toggle('d-none', !visible);
    }

    document.getElementById('asistente-modo').addEventListener('change', function () {
        mostrar('asistente-observacion-grupo', this.value === 'observaciones');
    });

    document.querySelectorAll('.btn-asistente-redaccion').forEach(function (boton) {
        boton.addEventListener('click', function () {
            seccionActual = boton.getAttribute('data-seccion');
            document.getElementById('asistente-titulo').textContent = boton.getAttribute('data-titulo');
            document.getElementById('asistente-error').classList.add('d-none');
            document.getElementById('asistente-avisos').classList.add('d-none');
            document.getElementById('asistente-propuesta-grupo').classList.add('d-none');
            document.getElementById('asistente-propuesta').value = '';
            modal.show();
        });
    });

    function pedir(modo) {
        var error = document.getElementById('asistente-error');
        error.classList.add('d-none');
        var cuerpo = {
            seccion: seccionActual,
            modo: modo,
            observacion: document.getElementById('asistente-observacion').value,
            resto_sin_cambios: document.getElementById('asistente-resto').checked ? 1 : 0,
            texto_actual: document.getElementById('asistente-propuesta').value,
            estilo: document.getElementById('asistente-estilo').value
        };
        if (modo === 'reescribir' || modo === 'otra_version' || modo === 'cambiar_estilo') {
            var nota = campoNota();
            if (!cuerpo.texto_actual && nota) {
                cuerpo.texto_actual = nota.value;
            }
        }
        return fetch(raiz.getAttribute('data-generar'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': raiz.getAttribute('data-csrf'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(cuerpo)
        }).then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok) {
                    throw new Error(datos.message || 'No se pudo redactar.');
                }
                return datos;
            });
        }).then(function (datos) {
            document.getElementById('asistente-propuesta').value = datos.texto || '';
            mostrar('asistente-propuesta-grupo', true);
            var avisos = document.getElementById('asistente-avisos');
            if (datos.avisos && datos.avisos.length) {
                avisos.textContent = datos.avisos.join(' ');
                avisos.classList.remove('d-none');
            } else {
                avisos.classList.add('d-none');
            }
        }).catch(function (fallo) {
            error.textContent = fallo.message || 'No se pudo redactar.';
            error.classList.remove('d-none');
        });
    }

    document.getElementById('asistente-generar').addEventListener('click', function () {
        pedir(document.getElementById('asistente-modo').value);
    });
    document.getElementById('asistente-reescribir').addEventListener('click', function () { pedir('reescribir'); });
    document.getElementById('asistente-otra').addEventListener('click', function () { pedir('otra_version'); });
    document.getElementById('asistente-estilo-btn').addEventListener('click', function () { pedir('cambiar_estilo'); });

    document.getElementById('asistente-aceptar').addEventListener('click', function () {
        var texto = document.getElementById('asistente-propuesta').value;
        var error = document.getElementById('asistente-error');
        fetch(raiz.getAttribute('data-aceptar'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': raiz.getAttribute('data-csrf'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ seccion: seccionActual, texto: texto })
        }).then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok) {
                    throw new Error(datos.message || 'No se pudo guardar.');
                }
                return datos;
            });
        }).then(function (datos) {
            var nota = campoNota();
            if (nota) {
                nota.value = datos.texto;
            }
            modal.hide();
        }).catch(function (fallo) {
            error.textContent = fallo.message || 'No se pudo guardar.';
            error.classList.remove('d-none');
        });
    });

    document.getElementById('asistente-dictar').addEventListener('click', function () {
        var Reconocimiento = window.SpeechRecognition || window.webkitSpeechRecognition;
        var boton = this;
        if (!Reconocimiento) {
            boton.disabled = true;
            boton.textContent = 'Este navegador no dicta';
            return;
        }
        if (dictado) {
            dictado.stop();
            dictado = null;
            boton.textContent = 'Dictar en la nota';
            return;
        }
        dictado = new Reconocimiento();
        dictado.lang = 'es-GT';
        dictado.continuous = true;
        dictado.onresult = function (evento) {
            var nota = document.getElementById('asistente-observacion');
            var pieza = evento.results[evento.results.length - 1][0].transcript;
            nota.value = (nota.value ? nota.value.replace(/\s+$/, '') + ' ' : '') + pieza.trim();
        };
        dictado.onend = function () {
            dictado = null;
            boton.textContent = 'Dictar en la nota';
        };
        boton.textContent = 'Detener dictado';
        dictado.start();
    });
})();
</script>
@endpush
@endif
