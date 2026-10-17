{{-- Vista previa de papelería. En imágenes se puede ampliar sin descargar. --}}
<div class="modal fade" id="modalPreviewDoc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="modalPreviewDocTitulo">Vista previa</h6>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <div class="btn-group btn-group-sm d-none" id="previewZoomBar" role="group" aria-label="Ampliar imagen">
                        <button type="button" class="btn btn-outline-secondary" id="previewZoomOut" title="Alejar">−</button>
                        <button type="button" class="btn btn-outline-secondary" id="previewZoomReset" title="Ver la imagen completa">Ajustar</button>
                        <button type="button" class="btn btn-outline-secondary px-2" id="previewZoomEtiqueta" disabled>100%</button>
                        <button type="button" class="btn btn-outline-secondary" id="previewZoomIn" title="Acercar">+</button>
                    </div>
                    <a href="#" id="btnDescargarPreview" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-download"></i> Descargar
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="min-height: 500px; background: #f0f0f0;">
                <div id="previewContenedor" class="w-100 h-100 d-flex align-items-center justify-content-center" style="min-height: 500px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let escala = 1;
    let anchoAjustado = 0;
    let img = null;
    let lienzo = null;
    let arrastrando = false;
    let origenX = 0;
    let origenY = 0;
    let scrollX = 0;
    let scrollY = 0;

    function etiqueta() {
        return document.getElementById('previewZoomEtiqueta');
    }

    function mostrarBarra(visible) {
        const barra = document.getElementById('previewZoomBar');
        if (!barra) {
            return;
        }
        barra.classList.toggle('d-none', !visible);
    }

    function aplicar() {
        if (!img) {
            return;
        }
        if (escala <= 1.01) {
            escala = 1;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '80vh';
            img.style.width = '';
            img.style.height = '';
        } else {
            if (!anchoAjustado) {
                anchoAjustado = img.getBoundingClientRect().width || img.naturalWidth;
            }
            img.style.maxWidth = 'none';
            img.style.maxHeight = 'none';
            img.style.width = Math.round(anchoAjustado * escala) + 'px';
            img.style.height = 'auto';
        }
        const texto = etiqueta();
        if (texto) {
            texto.textContent = Math.round(escala * 100) + '%';
        }
    }

    function fijarEscala(valor) {
        escala = Math.min(6, Math.max(1, valor));
        aplicar();
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#previewZoomIn')) {
            fijarEscala(escala * 1.25);
            return;
        }
        if (e.target.closest('#previewZoomOut')) {
            fijarEscala(escala / 1.25);
            return;
        }
        if (e.target.closest('#previewZoomReset')) {
            anchoAjustado = 0;
            fijarEscala(1);
            return;
        }

        const btn = e.target.closest('.btn-preview-doc');
        if (!btn) {
            return;
        }

        const url = btn.dataset.url;
        const tipo = btn.dataset.tipo;
        const nombre = btn.dataset.nombre;
        escala = 1;
        anchoAjustado = 0;
        img = null;
        lienzo = null;

        document.getElementById('modalPreviewDocTitulo').textContent = nombre;
        document.getElementById('btnDescargarPreview').href = url.replace('/preview', '/download');
        mostrarBarra(tipo === 'imagen');

        const contenedor = document.getElementById('previewContenedor');
        contenedor.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>';

        if (tipo === 'imagen') {
            lienzo = document.createElement('div');
            lienzo.style.overflow = 'auto';
            lienzo.style.maxHeight = '80vh';
            lienzo.style.width = '100%';
            lienzo.style.minHeight = '500px';
            lienzo.style.cursor = 'zoom-in';
            img = document.createElement('img');
            img.alt = nombre || 'Papelería';
            img.draggable = false;
            img.style.display = 'block';
            img.style.margin = 'auto';
            img.onload = function () {
                contenedor.innerHTML = '';
                contenedor.appendChild(lienzo);
                lienzo.appendChild(img);
                aplicar();
            };
            img.onerror = function () {
                contenedor.innerHTML = '<p class="text-danger p-4">No se pudo cargar la imagen.</p>';
            };
            img.src = url;
        } else {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.width = '100%';
            iframe.style.height = '80vh';
            iframe.style.border = 'none';
            iframe.style.display = 'block';
            contenedor.innerHTML = '';
            contenedor.appendChild(iframe);
        }
    });

    document.addEventListener('mousedown', function (e) {
        if (!lienzo || !lienzo.contains(e.target) || escala <= 1 || e.button !== 0) {
            return;
        }
        arrastrando = true;
        origenX = e.clientX;
        origenY = e.clientY;
        scrollX = lienzo.scrollLeft;
        scrollY = lienzo.scrollTop;
        lienzo.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function (e) {
        if (!arrastrando || !lienzo) {
            return;
        }
        lienzo.scrollLeft = scrollX - (e.clientX - origenX);
        lienzo.scrollTop = scrollY - (e.clientY - origenY);
    });

    document.addEventListener('mouseup', function () {
        if (!arrastrando) {
            return;
        }
        arrastrando = false;
        if (lienzo) {
            lienzo.style.cursor = 'zoom-in';
        }
    });

    document.addEventListener('wheel', function (e) {
        if (!lienzo || !img || !lienzo.contains(e.target)) {
            return;
        }
        e.preventDefault();
        fijarEscala(escala * (e.deltaY < 0 ? 1.15 : (1 / 1.15)));
    }, { passive: false });
})();
</script>
@endpush
