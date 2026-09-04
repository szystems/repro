<script>
document.addEventListener('DOMContentLoaded', function () {
    const empresa = document.getElementById('empresa_id');
    const sel = document.getElementById('reclutador_id');
    if (!empresa || !sel) {
        return;
    }

    const urlBase = @json(route('ordenes.reclutadores'));

    function cargarReclutadores(empresaId, selectedId) {
        sel.innerHTML = '<option value="">Sin asignar — visible según modo de la empresa</option>';
        if (!empresaId) {
            return;
        }

        fetch(urlBase + '?empresa_id=' + encodeURIComponent(empresaId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (lista) {
                (lista || []).forEach(function (u) {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name + (u.principal ? ' (gerente RRHH)' : '');
                    if (String(selectedId) === String(u.id)) {
                        opt.selected = true;
                    }
                    sel.appendChild(opt);
                });
            })
            .catch(function () {});
    }

    empresa.addEventListener('change', function () {
        cargarReclutadores(this.value, '');
    });
});
</script>
