/**
 * E2.8 — Regenera filas de formación académica según último nivel seleccionado.
 */
(function () {
    'use strict';

    function mapaVisibles() {
        if (window.formacionAcademicaVisibles && typeof window.formacionAcademicaVisibles === 'object') {
            return window.formacionAcademicaVisibles;
        }

        // Mismo mapa que HistorialAcademico::mapaNivelesVisibles() (por si el blade no lo inyectó).
        return {
            primaria: ['primaria'],
            basico: ['basico'],
            diversificado: ['diversificado'],
            tecnico: ['tecnico'],
            universitario: ['universitario'],
            postgrado: ['postgrado']
        };
    }

    function nivelesVisibles(ultimo) {
        const mapa = mapaVisibles();

        return Array.isArray(mapa[ultimo]) ? mapa[ultimo].slice() : [];
    }

    function collectRowsByNivel(wrapper) {
        const columnas = JSON.parse(wrapper.dataset.columnas || '[]');
        const byNivel = {};

        wrapper.querySelectorAll('.tabla-dinamica-row').forEach(function (row) {
            const nivelInput = row.querySelector('[name*="[nivel]"]');
            if (!nivelInput) {
                return;
            }

            const data = { nivel: nivelInput.value };
            columnas.forEach(function (col) {
                const input = row.querySelector('[name*="[' + col.key + ']"]');
                data[col.key] = input ? input.value : '';
            });

            if (data.nivel) {
                byNivel[data.nivel] = data;
            }
        });

        return byNivel;
    }

    function rebuildFormacion(ultimo) {
        const wrapper = document.querySelector('[data-formacion-academica]');
        if (!wrapper || !ultimo || ultimo === 'ninguno') {
            return;
        }

        const visibles = nivelesVisibles(ultimo);
        if (visibles.length === 0 || !window.TablaDinamica || !window.TablaDinamica.buildRow) {
            return;
        }

        const existing = collectRowsByNivel(wrapper);
        const name = wrapper.dataset.name;
        const columnas = JSON.parse(wrapper.dataset.columnas || '[]');
        const tbody = wrapper.querySelector('.tabla-dinamica-body');

        if (!tbody) {
            return;
        }

        const html = visibles.map(function (clave, index) {
            const fila = existing[clave] || { nivel: clave };
            fila.nivel = clave;

            return window.TablaDinamica.buildRow(name, index, columnas, '', fila, false);
        }).join('');

        tbody.innerHTML = html;
        window.TablaDinamica.reindex(wrapper);
        window.TablaDinamica.sync(wrapper);
    }

    function onNivelChange() {
        const select = document.getElementById('ultimo_nivel_academico');
        if (!select) {
            return;
        }

        rebuildFormacion(select.value);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('ultimo_nivel_academico');
        const seccion = document.getElementById('seccion_formacion_academica');

        if (select) {
            select.addEventListener('change', onNivelChange);

            if (select.value && select.value !== 'ninguno') {
                rebuildFormacion(select.value);
            }
        }

        if (seccion) {
            seccion.addEventListener('condicional:shown', onNivelChange);
        }
    });
})();
