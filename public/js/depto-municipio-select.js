/**
 * Fase F — E1.4: selects dependientes Departamento → Municipio (GT).
 * Precarga catálogo desde data-catalogo en el contenedor (JSON embebido).
 */
(function () {
    'use strict';

    const OTRO_LABEL = 'Otro (extranjero)';

    function findDepartamento(catalogo, nombre) {
        if (!nombre) return null;
        const normalizado = nombre.trim().toLowerCase();
        return catalogo.find(function (d) {
            return d.nombre.trim().toLowerCase() === normalizado;
        }) || null;
    }

    function findMunicipio(depto, nombre) {
        if (!depto || !nombre) return null;
        const normalizado = nombre.trim().toLowerCase();
        return depto.municipios.find(function (m) {
            return m.nombre.trim().toLowerCase() === normalizado;
        }) || null;
    }

    function isExtranjero(departamentoNombre, otroValue) {
        if (!departamentoNombre) return false;
        if (departamentoNombre === otroValue) return true;
        return departamentoNombre.trim().toLowerCase() === OTRO_LABEL.toLowerCase();
    }

    function syncHidden(selectEl, hiddenEl, labelFromOption) {
        if (!selectEl || !hiddenEl) return;
        const option = selectEl.options[selectEl.selectedIndex];
        hiddenEl.value = labelFromOption || (option ? option.text : '');
    }

    function initGroup(group) {
        let catalogo;
        try {
            catalogo = JSON.parse(group.dataset.catalogo || '[]');
        } catch (e) {
            console.error('Catálogo GT inválido', e);
            return;
        }

        const otroValue = group.dataset.otroValue || '__otro_extranjero__';
        const selectedDepto = group.dataset.selectedDepartamento || '';
        const selectedMuni = group.dataset.selectedMunicipio || '';

        const deptoSelect = group.querySelector('[data-role="departamento"]');
        const muniSelect = group.querySelector('[data-role="municipio"]');
        const deptoHidden = group.querySelector('[name="departamento"], [id$="_hidden"][id*="departamento"]');
        const muniHidden = group.querySelector('[name="municipio"], [id$="_hidden"][id*="municipio"]');
        const extranjeroWrap = group.querySelector('.depto-municipio-extranjero-wrap');
        const muniWrap = group.querySelector('.depto-municipio-municipio-wrap');
        const deptoExtranjero = group.querySelector('.depto-municipio-departamento-extranjero');
        const muniExtranjero = group.querySelector('.depto-municipio-municipio-extranjero');

        if (!deptoSelect || !muniSelect) return;

        // Resolver hidden por id del select
        const deptoHiddenEl = group.querySelector('#' + deptoSelect.id + '_hidden');
        const muniHiddenEl = group.querySelector('#' + muniSelect.id + '_hidden');

        function fillDepartamentos() {
            deptoSelect.innerHTML = '<option value="">Seleccione...</option>';
            catalogo.forEach(function (d) {
                const opt = document.createElement('option');
                opt.value = d.codigo;
                opt.textContent = d.nombre;
                deptoSelect.appendChild(opt);
            });
            const otroOpt = document.createElement('option');
            otroOpt.value = otroValue;
            otroOpt.textContent = OTRO_LABEL;
            deptoSelect.appendChild(otroOpt);
        }

        function fillMunicipios(deptoCodigo) {
            muniSelect.innerHTML = '<option value="">Seleccione...</option>';
            const depto = catalogo.find(function (d) { return d.codigo === deptoCodigo; });
            if (!depto) return;
            depto.municipios.forEach(function (m) {
                const opt = document.createElement('option');
                opt.value = m.codigo;
                opt.textContent = m.nombre;
                muniSelect.appendChild(opt);
            });
        }

        function toggleExtranjero(show) {
            if (extranjeroWrap) {
                extranjeroWrap.classList.toggle('d-none', !show);
            }
            if (muniWrap) {
                muniWrap.classList.toggle('d-none', show);
            }
            if (deptoSelect) {
                deptoSelect.required = true;
            }
            if (muniSelect) {
                muniSelect.required = !show;
                muniSelect.disabled = show;
            }
            if (deptoExtranjero) {
                deptoExtranjero.required = show;
            }
            if (muniExtranjero) {
                muniExtranjero.required = show;
            }
        }

        function syncFromExtranjero() {
            if (deptoHiddenEl && deptoExtranjero) {
                deptoHiddenEl.value = deptoExtranjero.value.trim();
            }
            if (muniHiddenEl && muniExtranjero) {
                muniHiddenEl.value = muniExtranjero.value.trim();
            }
        }

        function onDepartamentoChange() {
            const val = deptoSelect.value;
            if (val === otroValue) {
                toggleExtranjero(true);
                if (deptoHiddenEl) deptoHiddenEl.value = deptoExtranjero ? deptoExtranjero.value : OTRO_LABEL;
                if (muniHiddenEl) muniHiddenEl.value = muniExtranjero ? muniExtranjero.value : '';
                return;
            }
            toggleExtranjero(false);
            fillMunicipios(val);
            syncHidden(deptoSelect, deptoHiddenEl);
            if (muniHiddenEl) muniHiddenEl.value = '';
        }

        function onMunicipioChange() {
            syncHidden(muniSelect, muniHiddenEl);
        }

        fillDepartamentos();

        // Restaurar valores guardados
        const deptoMatch = findDepartamento(catalogo, selectedDepto);
        if (isExtranjero(selectedDepto, otroValue) || (selectedDepto && !deptoMatch)) {
            deptoSelect.value = otroValue;
            toggleExtranjero(true);
            if (deptoExtranjero) {
                deptoExtranjero.value = deptoMatch ? '' : selectedDepto;
            }
            if (muniExtranjero) {
                muniExtranjero.value = selectedMuni;
            }
            syncFromExtranjero();
        } else if (deptoMatch) {
            deptoSelect.value = deptoMatch.codigo;
            fillMunicipios(deptoMatch.codigo);
            const muni = findMunicipio(deptoMatch, selectedMuni);
            if (muni) {
                muniSelect.value = muni.codigo;
            }
            syncHidden(deptoSelect, deptoHiddenEl);
            syncHidden(muniSelect, muniHiddenEl);
        }

        deptoSelect.addEventListener('change', onDepartamentoChange);
        muniSelect.addEventListener('change', onMunicipioChange);

        if (deptoExtranjero) {
            deptoExtranjero.addEventListener('input', syncFromExtranjero);
        }
        if (muniExtranjero) {
            muniExtranjero.addEventListener('input', syncFromExtranjero);
        }

        // Antes de enviar formulario, asegurar hidden actualizados
        const form = group.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                if (deptoSelect.value === otroValue) {
                    syncFromExtranjero();
                } else {
                    syncHidden(deptoSelect, deptoHiddenEl);
                    syncHidden(muniSelect, muniHiddenEl);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-depto-municipio]').forEach(initGroup);
    });
})();
