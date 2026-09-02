@once
@push('scripts')
<script>
(function () {
    function asignarArchivo(inputId, file) {
        const target = document.getElementById(inputId);
        if (!target || !file) { return; }
        const dt = new DataTransfer();
        dt.items.add(file);
        target.files = dt.files;
        target.dispatchEvent(new Event('change', { bubbles: true }));
    }

    document.addEventListener('click', function (e) {
        const camBtn = e.target.closest('.btn-tomar-foto');
        if (!camBtn) { return; }
        const cam = document.createElement('input');
        cam.type = 'file';
        cam.accept = 'image/*';
        cam.setAttribute('capture', 'environment');
        cam.addEventListener('change', function () {
            if (cam.files.length) {
                asignarArchivo(camBtn.dataset.target, cam.files[0]);
            }
        });
        cam.click();
    });

    document.addEventListener('paste', function (e) {
        const zona = e.target.closest('.zona-pegar-papeleria');
        if (!zona) { return; }
        if (e.target.matches('input[type="text"], textarea')) { return; }

        const items = e.clipboardData && e.clipboardData.items ? Array.from(e.clipboardData.items) : [];
        const imagen = items.find(function (item) { return item.type && item.type.indexOf('image/') === 0; });
        if (!imagen) { return; }

        const file = imagen.getAsFile();
        if (!file) { return; }
        e.preventDefault();
        asignarArchivo(zona.dataset.fileInput, file);
    });
})();
</script>
@endpush
@endonce
