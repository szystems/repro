/**
 * Fase F — E1.3: autosave por sección (debounce, sin validación completa).
 */
(function () {
    'use strict';

    const DEBOUNCE_MS = 2500;

    function syncCamposAntesDeGuardar(form) {
        if (window.CamposCondicionales && typeof window.CamposCondicionales.syncAll === 'function') {
            window.CamposCondicionales.syncAll();
        }
        if (window.TablaDinamica && typeof window.TablaDinamica.syncAll === 'function') {
            window.TablaDinamica.syncAll();
        }
    }

    function buildFormData(form) {
        syncCamposAntesDeGuardar(form);
        return new FormData(form);
    }

    function initAutosave(form) {
        const url = form.dataset.autosaveUrl;
        const statusEl = document.getElementById('autosaveStatus');

        if (!url) {
            return;
        }

        let debounceTimer = null;
        let enCurso = false;
        let envioManual = false;
        let hayCambiosPendientes = false;

        function setStatus(texto, tipo) {
            if (!statusEl) {
                return;
            }
            statusEl.textContent = texto;
            statusEl.className = 'autosave-status small mt-1 text-' + (tipo || 'muted');
        }

        function marcarCambio() {
            if (envioManual) {
                return;
            }
            hayCambiosPendientes = true;
            setStatus('Cambios sin guardar…', 'muted');
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(guardar, DEBOUNCE_MS);
        }

        function guardar() {
            if (enCurso || envioManual) {
                return;
            }

            enCurso = true;
            setStatus('Guardando…', 'secondary');

            const formData = buildFormData(form);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf || '',
                },
                body: formData,
                credentials: 'same-origin',
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.data.success) {
                        hayCambiosPendientes = false;
                        const hora = new Date(result.data.saved_at || Date.now());
                        setStatus('Guardado automáticamente a las ' + hora.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                        }), 'success');
                        return;
                    }

                    if (result.status === 422) {
                        setStatus('Complete los campos con formato válido antes de continuar.', 'warning');
                        return;
                    }

                    setStatus('No se pudo guardar automáticamente.', 'warning');
                })
                .catch(function () {
                    setStatus('Sin conexión — reintentará al editar.', 'warning');
                })
                .finally(function () {
                    enCurso = false;
                });
        }

        form.addEventListener('input', marcarCambio);
        form.addEventListener('change', marcarCambio);

        form.addEventListener('submit', function () {
            envioManual = true;
            clearTimeout(debounceTimer);
            hayCambiosPendientes = false;
        });

        window.addEventListener('pagehide', function () {
            if (!hayCambiosPendientes || envioManual || enCurso) {
                return;
            }

            const formData = buildFormData(form);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrf) {
                formData.append('_token', csrf);
            }

            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, formData);
            }
        });

        setStatus('Los cambios se guardan automáticamente', 'muted');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('cuestionarioForm');
        if (form) {
            initAutosave(form);
        }
    });
})();
