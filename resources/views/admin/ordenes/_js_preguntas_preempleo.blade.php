<script>
window.ReproPreguntasPreempleo = {
    porEmpresa: @json($preguntasPuestoPorEmpresa ?? []),
    requerimientos: @json($requerimientosReproPorEmpresa ?? []),
    empresaFija: @json(isset($orden) ? (string) $orden->empresa_id : ''),
    mostrarRequerimientos() {
        const caja = document.getElementById('aviso-requerimientos-empresa-orden');
        if (!caja) {
            return;
        }
        const empresaEl = document.getElementById('empresa_id');
        const empresaId = empresaEl && empresaEl.value
            ? String(empresaEl.value)
            : String(this.empresaFija || '');
        const texto = this.requerimientos[empresaId] || this.requerimientos[Number(empresaId)] || '';
        caja.replaceChildren();
        if (!texto) {
            caja.classList.add('d-none');
            return;
        }
        const titulo = document.createElement('strong');
        titulo.textContent = 'Requerimientos de la empresa (solo REPRO)';
        const cuerpo = document.createElement('div');
        cuerpo.className = 'mt-1';
        cuerpo.style.whiteSpace = 'pre-wrap';
        cuerpo.textContent = texto;
        caja.append(titulo, cuerpo);
        caja.classList.remove('d-none');
    },
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
        this.mostrarRequerimientos();
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
