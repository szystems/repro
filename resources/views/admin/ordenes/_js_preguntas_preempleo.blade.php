<script>
window.ReproPreguntasPreempleo = {
    porEmpresa: @json($preguntasPuestoPorEmpresa ?? []),
    empresaFija: @json(isset($orden) ? (string) $orden->empresa_id : ''),
    aplicar(root) {
        const empresaEl = document.getElementById('empresa_id');
        const empresaId = empresaEl && empresaEl.value
            ? String(empresaEl.value)
            : String(this.empresaFija || '');
        const nombre = this.porEmpresa[empresaId] || this.porEmpresa[Number(empresaId)] || '';
        (root || document).querySelectorAll('.juego-preguntas-wrap').forEach(function(wrap) {
            const select = wrap.querySelector('select');
            if (!select) {
                return;
            }
            const opcionPuesto = select.querySelector('option[value="puesto"]');
            if (!nombre) {
                wrap.classList.add('d-none');
                select.value = 'principal';
                select.disabled = true;
                return;
            }
            wrap.classList.remove('d-none');
            select.disabled = false;
            if (opcionPuesto) {
                opcionPuesto.textContent = 'Del puesto: ' + nombre;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const empresaEl = document.getElementById('empresa_id');
    if (empresaEl) {
        empresaEl.addEventListener('change', function() {
            window.ReproPreguntasPreempleo.aplicar(document);
        });
    }
    window.ReproPreguntasPreempleo.aplicar(document);
});
</script>
