/**
 * Fase F — E1.5: captura/subida de foto del candidato con vista previa.
 * PC: webcam vía getUserMedia + modal. Móvil: misma webcam o fallback capture nativo.
 */
(function () {
    'use strict';

    let webcamStream = null;
    let activeGroup = null;
    let modalInstance = null;

    function getModal() {
        return document.getElementById('fotoWebcamModal');
    }

    function getVideo() {
        return document.getElementById('fotoWebcamVideo');
    }

    function getErrorBox() {
        return document.getElementById('fotoWebcamError');
    }

    function getCaptureBtn() {
        return document.getElementById('fotoWebcamCapture');
    }

    function getLoadingEl() {
        return document.getElementById('fotoWebcamLoading');
    }

    function setWebcamLoading(loading) {
        const loadingEl = getLoadingEl();
        const video = getVideo();
        if (loadingEl) {
            loadingEl.classList.toggle('d-none', !loading);
        }
        if (video) {
            video.classList.toggle('d-none', loading);
        }
    }

    function stopWebcam() {
        if (webcamStream) {
            webcamStream.getTracks().forEach(function (track) {
                track.stop();
            });
            webcamStream = null;
        }
        const video = getVideo();
        if (video) {
            video.srcObject = null;
        }
    }

    function showWebcamError(message) {
        const box = getErrorBox();
        if (box) {
            box.textContent = message;
            box.style.display = 'block';
        }
    }

    function hideWebcamError() {
        const box = getErrorBox();
        if (box) {
            box.style.display = 'none';
            box.textContent = '';
        }
    }

    function assignFileToInput(input, file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function supportsGetUserMedia() {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    }

    /**
     * Intenta abrir la cámara con restricciones progresivamente más simples.
     * facingMode:'user' suele fallar en webcams de escritorio (OverconstrainedError).
     */
    function requestCameraStream() {
        const attempts = [
            { video: true, audio: false },
            { video: { width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: { facingMode: { ideal: 'user' } }, audio: false },
            { video: { facingMode: 'user' }, audio: false },
        ];

        function tryAt(index) {
            if (index >= attempts.length) {
                return Promise.reject(Object.assign(new Error('Sin cámara disponible'), { name: 'NotFoundError' }));
            }
            return navigator.mediaDevices.getUserMedia(attempts[index]).catch(function (err) {
                if (err.name === 'OverconstrainedError' || err.name === 'NotFoundError') {
                    return tryAt(index + 1);
                }
                throw err;
            });
        }

        return tryAt(0);
    }

    function describeCameraError(err) {
        console.error('Webcam error:', err.name, err.message, err);

        switch (err.name) {
            case 'NotFoundError':
            case 'DevicesNotFoundError':
                return 'No se detectó cámara web. Use "Subir archivo" o conecte una webcam.';
            case 'NotAllowedError':
            case 'PermissionDeniedError':
                return 'Permiso de cámara denegado. En la barra de direcciones del navegador, permita el acceso a la cámara para este sitio.';
            case 'NotReadableError':
            case 'TrackStartError':
                return 'La cámara está en uso por otra aplicación (Teams, Zoom, etc.). Ciérrela e intente de nuevo.';
            case 'OverconstrainedError':
                return 'La cámara no soporta la configuración solicitada. Intente de nuevo o use "Subir archivo".';
            case 'SecurityError':
                return 'Acceso bloqueado: abra el formulario en http://localhost:8000 (no use una IP ni HTTP sin localhost).';
            case 'AbortError':
                return 'Acceso a la cámara cancelado. Pulse "Tomar foto" nuevamente.';
            default:
                return 'No se pudo acceder a la cámara (' + (err.name || 'error') + '). Use "Subir archivo" si persiste.';
        }
    }

    function ensureModalVisible() {
        const modalEl = getModal();
        const video = getVideo();
        const captureBtn = getCaptureBtn();

        if (!modalEl || !video || !window.bootstrap) {
            return false;
        }

        hideWebcamError();
        setWebcamLoading(true);
        if (captureBtn) {
            captureBtn.disabled = true;
        }

        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalEl);
        }

        modalInstance.show();
        return true;
    }

    function attachStreamToVideo(stream) {
        const video = getVideo();
        const captureBtn = getCaptureBtn();

        webcamStream = stream;
        video.srcObject = stream;

        video.onloadedmetadata = function () {
            video.play().catch(function () { /* autoplay ok en modal */ });
            setWebcamLoading(false);
            video.classList.remove('d-none');
            if (captureBtn) {
                captureBtn.disabled = false;
            }
        };
    }

    /**
     * Debe llamarse directamente desde el clic del usuario (gesto activo).
     */
    function startCameraCapture(group) {
        if (!supportsGetUserMedia()) {
            return false;
        }

        if (!window.isSecureContext) {
            ensureModalVisible();
            showWebcamError('La cámara solo funciona en contexto seguro. Use http://localhost:8000');
            activeGroup = group;
            return true;
        }

        activeGroup = group;

        if (!ensureModalVisible()) {
            return false;
        }

        // Llamar getUserMedia en el mismo tick del clic (requerido por Chrome/Edge)
        requestCameraStream()
            .then(function (stream) {
                attachStreamToVideo(stream);
            })
            .catch(function (err) {
                setWebcamLoading(false);
                showWebcamError(describeCameraError(err));
            });

        return true;
    }

    function captureFromWebcam(group) {
        const video = getVideo();
        const input = group.querySelector('[data-foto-input]');

        if (!video || !input || !video.videoWidth) {
            showWebcamError('Espere a que la cámara inicie antes de capturar.');
            return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function (blob) {
            if (!blob) {
                showWebcamError('No se pudo capturar la imagen. Intente de nuevo.');
                return;
            }
            const file = new File([blob], 'foto-candidato.jpg', { type: 'image/jpeg' });
            assignFileToInput(input, file);
            stopWebcam();
            if (modalInstance) {
                modalInstance.hide();
            }
        }, 'image/jpeg', 0.92);
    }

    function initWebcamModalControls() {
        const modalEl = getModal();
        if (!modalEl || modalEl.dataset.webcamBound) {
            return;
        }
        modalEl.dataset.webcamBound = '1';

        modalEl.querySelectorAll('[data-foto-webcam-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                stopWebcam();
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        });

        const captureBtn = getCaptureBtn();
        if (captureBtn) {
            captureBtn.addEventListener('click', function () {
                if (activeGroup) {
                    captureFromWebcam(activeGroup);
                }
            });
        }

        modalEl.addEventListener('hidden.bs.modal', stopWebcam);
    }

    function initFotoCandidato(group) {
        const input = group.querySelector('[data-foto-input]');
        const preview = group.querySelector('[data-foto-preview]');
        const previewWrap = group.querySelector('[data-foto-preview-wrap]');
        const existenteInput = group.querySelector('[data-foto-existente]');
        const form = group.closest('form');

        if (!input) return;

        function showPreviewFromFile(file) {
            if (!file || !preview || !previewWrap) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                previewWrap.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        function clearExistenteFlag() {
            if (existenteInput) {
                existenteInput.remove();
            }
        }

        group.querySelectorAll('[data-foto-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const mode = btn.getAttribute('data-foto-trigger');

                if (mode === 'camera') {
                    stopWebcam();
                    if (startCameraCapture(group)) {
                        return;
                    }
                    input.setAttribute('capture', 'user');
                    input.click();
                    return;
                }

                input.removeAttribute('capture');
                input.click();
            });
        });

        input.addEventListener('change', function () {
            if (input.files && input.files.length > 0) {
                showPreviewFromFile(input.files[0]);
                clearExistenteFlag();
                group.classList.remove('is-invalid');
            }
        });

        form.addEventListener('submit', function (e) {
            if (window.CamposCondicionales && typeof window.CamposCondicionales.syncAll === 'function') {
                window.CamposCondicionales.syncAll();
            }

            const hasFile = input.files && input.files.length > 0;
            const hasExistente = form.querySelector('[name="foto_candidato_existente"]');
            if (!hasFile && !hasExistente) {
                e.preventDefault();
                group.classList.add('is-invalid');
                if (window.cuestionarioHelpers) {
                    cuestionarioHelpers.hideLoading();
                    cuestionarioHelpers.showAlert('Debe tomar o subir su fotografía para continuar.', 'warning');
                }
                group.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initWebcamModalControls();
        document.querySelectorAll('[data-foto-candidato]').forEach(initFotoCandidato);
    });
})();
