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
        const datos = this.porEmpresa[empresaId] || this.porEmpresa[Number(empresaId)] || null;
        const nombrePuesto = datos && typeof datos === 'object' ? (datos.puesto || '') : (datos || '');
        const nombrePrincipal = datos && typeof datos === 'object' && datos.principal
            ? datos.principal
            : 'Preguntas generales';
        (root || document).querySelectorAll('.juego-preguntas-wrap').forEach(function(wrap) {
            const select = wrap.querySelector('select');
            if (!select) {
                return;
            }
            const opcionPrincipal = select.querySelector('option[value="principal"]');
            const opcionPuesto = select.querySelector('option[value="puesto"]');
            const fila = wrap.closest('.evaluado-item');
            const formulario = fila ? fila.querySelector('[name$="[tipo_formulario]"]') : null;
            const servicio = fila ? fila.querySelector('[name$="[tipo_servicio]"]') : null;
            const esPreempleo = !formulario || formulario.value === 'preempleo';
            const esPoliOvsa = !servicio || servicio.value === 'poligrafo' || servicio.value === 'vsa';
            if (!nombrePuesto || !esPreempleo || !esPoliOvsa) {
                wrap.classList.add('d-none');
                select.value = 'principal';
                select.disabled = true;
                return;
            }
            wrap.classList.remove('d-none');
            select.disabled = false;
            if (opcionPrincipal) {
                opcionPrincipal.textContent = nombrePrincipal;
            }
            if (opcionPuesto) {
                opcionPuesto.textContent = 'Del puesto: ' + nombrePuesto;
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
    document.addEventListener('change', function(event) {
        const name = event.target && event.target.name ? event.target.name : '';
        if (name.indexOf('[tipo_formulario]') === -1 && name.indexOf('[tipo_servicio]') === -1) {
            return;
        }
        const fila = event.target.closest('.evaluado-item');
        window.ReproPreguntasPreempleo.aplicar(fila || document);
    });
    window.ReproPreguntasPreempleo.aplicar(document);
});
</script>
