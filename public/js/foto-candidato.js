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

    let cropModal = null;
    let cropGroup = null;
    let cropSource = null;
    let cropNatural = { w: 0, h: 0 };
    let cropZoom = 1;
    let cropOffset = { x: 0, y: 0 };
    let cropDrag = null;

    function getCropModal() {
        return document.getElementById('fotoRecorteModal');
    }

    function getCropStage() {
        return document.getElementById('fotoRecorteStage');
    }

    function getCropImageEl() {
        return document.getElementById('fotoRecorteImg');
    }

    function showCropError(message) {
        const box = document.getElementById('fotoRecorteError');
        if (!box) {
            return;
        }
        box.textContent = message;
        box.style.display = 'block';
    }

    function hideCropError() {
        const box = document.getElementById('fotoRecorteError');
        if (!box) {
            return;
        }
        box.style.display = 'none';
        box.textContent = '';
    }

    function cropFrameSize() {
        const stage = getCropStage();
        return {
            w: stage ? stage.clientWidth : 0,
            h: stage ? stage.clientHeight : 0,
        };
    }

    function cropCoverScale() {
        const frame = cropFrameSize();
        if (!cropNatural.w || !cropNatural.h || !frame.w || !frame.h) {
            return 1;
        }
        return Math.max(frame.w / cropNatural.w, frame.h / cropNatural.h);
    }

    function clampCropOffset() {
        const frame = cropFrameSize();
        const scale = cropCoverScale() * cropZoom;
        const imgW = cropNatural.w * scale;
        const imgH = cropNatural.h * scale;
        const minX = Math.min(0, frame.w - imgW);
        const minY = Math.min(0, frame.h - imgH);
        cropOffset.x = Math.min(0, Math.max(minX, cropOffset.x));
        cropOffset.y = Math.min(0, Math.max(minY, cropOffset.y));
    }

    function paintCrop() {
        const img = getCropImageEl();
        if (!img || !cropNatural.w) {
            return;
        }
        const scale = cropCoverScale() * cropZoom;
        clampCropOffset();
        img.style.width = (cropNatural.w * scale) + 'px';
        img.style.height = (cropNatural.h * scale) + 'px';
        img.style.left = cropOffset.x + 'px';
        img.style.top = cropOffset.y + 'px';
    }

    function setCropZoom(nextZoom, keepCenter) {
        const frame = cropFrameSize();
        const oldScale = cropCoverScale() * cropZoom;
        const zoom = Math.min(3, Math.max(1, nextZoom));
        let centerX = cropNatural.w / 2;
        let centerY = cropNatural.h / 2;
        if (keepCenter && oldScale > 0 && frame.w) {
            centerX = (frame.w / 2 - cropOffset.x) / oldScale;
            centerY = (frame.h / 2 - cropOffset.y) / oldScale;
        }
        cropZoom = zoom;
        const scale = cropCoverScale() * cropZoom;
        cropOffset.x = frame.w / 2 - centerX * scale;
        cropOffset.y = frame.h / 2 - centerY * scale;
        const slider = document.getElementById('fotoRecorteZoom');
        if (slider) {
            slider.value = String(cropZoom);
        }
        paintCrop();
    }

    function resetCropView() {
        cropZoom = 1;
        const frame = cropFrameSize();
        const scale = cropCoverScale();
        cropOffset.x = (frame.w - cropNatural.w * scale) / 2;
        cropOffset.y = (frame.h - cropNatural.h * scale) / 2;
        const slider = document.getElementById('fotoRecorteZoom');
        if (slider) {
            slider.value = '1';
        }
        paintCrop();
    }

    function applyCrop() {
        const input = cropGroup ? cropGroup.querySelector('[data-foto-input]') : null;
        const frame = cropFrameSize();
        if (!input || !cropSource || !frame.w || !cropNatural.w) {
            showCropError('Espere a que la foto cargue antes de recortar.');
            return;
        }

        const scale = cropCoverScale() * cropZoom;
        const sourceX = -cropOffset.x / scale;
        const sourceY = -cropOffset.y / scale;
        const sourceW = frame.w / scale;
        const sourceH = frame.h / scale;
        const outputH = 1200;
        const outputW = Math.round(outputH * 3 / 4);
        const canvas = document.createElement('canvas');
        canvas.width = outputW;
        canvas.height = outputH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(cropSource, sourceX, sourceY, sourceW, sourceH, 0, 0, outputW, outputH);

        canvas.toBlob(function (blob) {
            if (!blob) {
                showCropError('No se pudo recortar la imagen. Intente de nuevo.');
                return;
            }
            const file = new File([blob], 'foto-candidato.jpg', { type: 'image/jpeg' });
            assignFileToInput(input, file);
            if (cropModal) {
                cropModal.hide();
            }
        }, 'image/jpeg', 0.92);
    }

    function openCrop(group) {
        const preview = group.querySelector('[data-foto-preview]');
        const modalEl = getCropModal();
        const img = getCropImageEl();
        if (!preview || !preview.getAttribute('src') || !modalEl || !img || !window.bootstrap) {
            return;
        }

        cropGroup = group;
        cropSource = null;
        hideCropError();
        cropNatural = { w: 0, h: 0 };
        img.style.width = '0px';
        img.removeAttribute('src');

        if (!cropModal) {
            cropModal = new bootstrap.Modal(modalEl);
        }
        cropModal.show();

        const loader = new Image();
        loader.onload = function () {
            cropSource = loader;
            cropNatural = { w: loader.naturalWidth, h: loader.naturalHeight };
            img.src = loader.src;
            whenCropFrameReady(resetCropView);
        };
        loader.onerror = function () {
            showCropError('No se pudo abrir la foto para recortar. Tómela o súbala de nuevo.');
        };
        loader.src = preview.src;
    }

    function whenCropFrameReady(callback) {
        let left = 30;
        function attempt() {
            const frame = cropFrameSize();
            if ((frame.w > 0 && frame.h > 0) || left <= 0) {
                callback();
                return;
            }
            left -= 1;
            window.requestAnimationFrame(attempt);
        }
        attempt();
    }

    function initCropModalControls() {
        const modalEl = getCropModal();
        const stage = getCropStage();
        if (!modalEl || !stage || modalEl.dataset.cropBound) {
            return;
        }
        modalEl.dataset.cropBound = '1';

        modalEl.querySelectorAll('[data-foto-recorte-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (cropModal) {
                    cropModal.hide();
                }
            });
        });

        const applyBtn = document.getElementById('fotoRecorteAplicar');
        if (applyBtn) {
            applyBtn.addEventListener('click', applyCrop);
        }

        const slider = document.getElementById('fotoRecorteZoom');
        if (slider) {
            slider.addEventListener('input', function () {
                setCropZoom(Number(slider.value), true);
            });
        }

        stage.addEventListener('pointerdown', function (event) {
            if (!cropNatural.w) {
                return;
            }
            cropDrag = {
                id: event.pointerId,
                x: event.clientX,
                y: event.clientY,
                ox: cropOffset.x,
                oy: cropOffset.y,
            };
            stage.classList.add('is-dragging');
            stage.setPointerCapture(event.pointerId);
        });

        stage.addEventListener('pointermove', function (event) {
            if (!cropDrag || cropDrag.id !== event.pointerId) {
                return;
            }
            cropOffset.x = cropDrag.ox + (event.clientX - cropDrag.x);
            cropOffset.y = cropDrag.oy + (event.clientY - cropDrag.y);
            paintCrop();
        });

        function endDrag(event) {
            if (!cropDrag || (event && cropDrag.id !== event.pointerId)) {
                return;
            }
            cropDrag = null;
            stage.classList.remove('is-dragging');
        }

        stage.addEventListener('pointerup', endDrag);
        stage.addEventListener('pointercancel', endDrag);
    }

    function initFotoCandidato(group) {
        const input = group.querySelector('[data-foto-input]');
        const preview = group.querySelector('[data-foto-preview]');
        const previewWrap = group.querySelector('[data-foto-preview-wrap]');
        const existenteInput = group.querySelector('[data-foto-existente]');
        const form = group.closest('form');

        if (!input) return;

        function setCropEnabled(enabled) {
            const btn = group.querySelector('[data-foto-crop]');
            if (btn) {
                btn.disabled = !enabled;
            }
        }

        function showPreviewFromFile(file) {
            if (!file || !preview || !previewWrap) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                previewWrap.classList.remove('d-none');
                setCropEnabled(true);
            };
            reader.readAsDataURL(file);
        }

        setCropEnabled(previewWrap && !previewWrap.classList.contains('d-none') && !!(preview && preview.getAttribute('src')));

        function clearExistenteFlag() {
            if (existenteInput) {
                existenteInput.remove();
            }
        }

        group.querySelectorAll('[data-foto-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const mode = btn.getAttribute('data-foto-trigger');

                if (mode === 'crop') {
                    openCrop(group);
                    return;
                }

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

        function setFotoErrorVisible(visible, message) {
            const errorEl = group.querySelector('[data-foto-error]');
            if (!errorEl) {
                return;
            }
            if (visible) {
                if (message) {
                    errorEl.textContent = message;
                }
                errorEl.style.display = 'block';
                errorEl.classList.add('d-block');
            } else {
                errorEl.style.display = 'none';
                errorEl.classList.remove('d-block');
            }
        }

        input.addEventListener('change', function () {
            if (input.files && input.files.length > 0) {
                showPreviewFromFile(input.files[0]);
                clearExistenteFlag();
                group.classList.remove('is-invalid');
                setFotoErrorVisible(false);
            }
        });

        form.addEventListener('submit', function (e) {
            if (window.CamposCondicionales && typeof window.CamposCondicionales.syncAll === 'function') {
                window.CamposCondicionales.syncAll();
            }

            if (group.hasAttribute('data-foto-opcional')) {
                return;
            }

            const hasFile = input.files && input.files.length > 0;
            const hasExistente = form.querySelector('[name="foto_candidato_existente"]');
            if (!hasFile && !hasExistente) {
                e.preventDefault();
                group.classList.add('is-invalid');
                setFotoErrorVisible(true, 'Debe tomar, subir o pegar su fotografía para continuar.');
                if (window.cuestionarioHelpers) {
                    cuestionarioHelpers.hideLoading();
                    cuestionarioHelpers.showAlert('Debe tomar, subir o pegar su fotografía para continuar.', 'warning');
                }
                group.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    document.addEventListener('paste', function (e) {
        const zona = e.target.closest('[data-foto-candidato], .zona-pegar-foto, .foto-candidato-box');
        if (!zona) {
            return;
        }
        if (e.target.matches && e.target.matches('input[type="text"], textarea')) {
            return;
        }

        const group = zona.closest('[data-foto-candidato]') || document.querySelector('[data-foto-candidato]');
        const input = group ? group.querySelector('[data-foto-input]') : null;
        if (!input) {
            return;
        }

        const items = e.clipboardData && e.clipboardData.items ? Array.from(e.clipboardData.items) : [];
        const imagen = items.find(function (item) { return item.type && item.type.indexOf('image/') === 0; });
        if (!imagen) {
            return;
        }

        const file = imagen.getAsFile();
        if (!file) {
            return;
        }

        e.preventDefault();
        assignFileToInput(input, file);
    });

    document.addEventListener('DOMContentLoaded', function () {
        initWebcamModalControls();
        initCropModalControls();
        document.querySelectorAll('[data-foto-candidato]').forEach(initFotoCandidato);
    });
})();
