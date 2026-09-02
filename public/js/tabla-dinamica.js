/**
 * Fase F — E1.1: tablas dinámicas (agregar/eliminar filas).
 */
(function () {
    'use strict';

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    const MESES_FECHAS = [
        ['01', 'Enero'], ['02', 'Febrero'], ['03', 'Marzo'], ['04', 'Abril'],
        ['05', 'Mayo'], ['06', 'Junio'], ['07', 'Julio'], ['08', 'Agosto'],
        ['09', 'Septiembre'], ['10', 'Octubre'], ['11', 'Noviembre'], ['12', 'Diciembre'],
    ];

    const ANIOS_ATRAS_FECHAS = 70;

    /** Sufijos que viajan en el formulario para un rango de fechas (incluye nombres antiguos). */
    const SUFIJOS_DATE_RANGE = [
        '_inicio_mes', '_inicio_anio', '_fin_mes', '_fin_anio', '_actual', '_inicio', '_fin',
    ];

    function opcionesMes() {
        let html = '<option value="">Mes</option>';
        MESES_FECHAS.forEach(function (mes) {
            html += '<option value="' + mes[0] + '">' + mes[1] + '</option>';
        });
        return html;
    }

    function opcionesAnio() {
        const actual = new Date().getFullYear();
        let html = '<option value="">Año</option>';
        for (let anio = actual; anio >= actual - ANIOS_ATRAS_FECHAS; anio--) {
            html += '<option value="' + anio + '">' + anio + '</option>';
        }
        return html;
    }

    function buildDateRangeExtremo(name, index, key, sufijo, etiqueta, marcador, isRequired) {
        const mesName = name + '[' + index + '][' + key + sufijo + '_mes]';
        const anioName = name + '[' + index + '][' + key + sufijo + '_anio]';
        const req = isRequired ? ' required' : '';
        const label = escapeHtml(etiqueta);

        let html = '<div class="fechas-laboradas-extremo mb-1">';
        html += '<label class="form-label form-label-sm mb-0 text-muted">' + label + '</label>';
        html += '<div class="fechas-laboradas-selects">';
        html += '<select class="form-control form-control-sm" name="' + mesName + '" aria-label="' + label + ' — mes" data-fechas-' + marcador + req + '>' + opcionesMes() + '</select>';
        html += '<select class="form-control form-control-sm" name="' + anioName + '" aria-label="' + label + ' — año" data-fechas-' + marcador + req + '>' + opcionesAnio() + '</select>';
        html += '</div></div>';

        return html;
    }

    function buildDateRangeField(name, index, col, storedValue, isRequired) {
        const key = col.key;
        const actualName = name + '[' + index + '][' + key + '_actual]';
        const actualId = (name + '_' + index + '_' + key + '_actual').replace(/[^\w-]/g, '_');

        let html = '<div class="fechas-laboradas-range" data-fechas-laboradas-range>';
        html += buildDateRangeExtremo(name, index, key, '_inicio', 'Desde (mes y año)', 'inicio', isRequired);
        html += buildDateRangeExtremo(name, index, key, '_fin', 'Hasta (mes y año)', 'fin', isRequired);
        html += '<div class="form-check form-check-sm mt-1">';
        html += '<input class="form-check-input" type="checkbox" id="' + actualId + '" name="' + actualName + '" value="1" data-fechas-actual>';
        html += '<label class="form-check-label small" for="' + actualId + '">Sigue laborando</label>';
        html += '</div></div>';

        return html;
    }

    /**
     * «Sigue laborando» manda sobre el estado de los selectores «Hasta».
     * Debe ejecutarse DESPUÉS de syncWrapperFields/syncFieldState, que reponen
     * disabled/required de forma genérica y bloqueaban el envío del formulario.
     */
    function syncFechasLaboradasRange(wrap) {
        const checkbox = wrap.querySelector('[data-fechas-actual]');
        const inicios = wrap.querySelectorAll('[data-fechas-inicio]');
        const fines = wrap.querySelectorAll('[data-fechas-fin]');

        if (!checkbox || inicios.length === 0 || fines.length === 0) {
            return;
        }

        // En secciones ocultas manda el sincronizador de campos condicionales.
        if (isInsideHiddenContainer(wrap)) {
            return;
        }

        const inicioRequerido = Array.prototype.some.call(inicios, function (el) {
            return el.required || el.dataset.preserveRequired === '1';
        });

        Array.prototype.forEach.call(fines, function (el) {
            if (checkbox.checked) {
                el.value = '';
                el.disabled = true;
                el.required = false;
                delete el.dataset.preserveRequired;
                return;
            }

            el.disabled = false;
            el.required = inicioRequerido;
        });
    }

    function prepararFechasLaboradasParaEnvio(root) {
        (root || document).querySelectorAll('[data-fechas-laboradas-range]').forEach(function (wrap) {
            if (isInsideHiddenContainer(wrap)) {
                return;
            }

            syncFechasLaboradasRange(wrap);

            wrap.querySelectorAll('[data-fechas-inicio], [data-fechas-fin]').forEach(function (el) {
                el.disabled = false;
            });
        });
    }

    function initFechasLaboradasRanges(root) {
        (root || document).querySelectorAll('[data-fechas-laboradas-range]').forEach(syncFechasLaboradasRange);
    }

    function reindexDateRangeField(row, name, index, col) {
        SUFIJOS_DATE_RANGE.forEach(function (suffix) {
            row.querySelectorAll('[name$="[' + col.key + suffix + ']"]').forEach(function (input) {
                input.name = name + '[' + index + '][' + col.key + suffix + ']';
            });
        });
    }

    function isDateRangeEmpty(row, col) {
        const campos = row.querySelectorAll(
            '[name$="[' + col.key + '_inicio_mes]"],' +
            '[name$="[' + col.key + '_inicio_anio]"],' +
            '[name$="[' + col.key + '_inicio]"]'
        );

        return Array.prototype.every.call(campos, function (el) {
            return !String(el.value || '').trim();
        });
    }

    function buildField(name, index, col, value) {
        const fieldName = name + '[' + index + '][' + col.key + ']';
        const required = col.required ? ' required' : '';
        const maxAttr = col.max ? ' maxlength="' + col.max + '"' : '';
        let html = '';

        if (col.readonly && col.options) {
            const label = col.options[value] || value || '';
            html += '<input type="hidden" name="' + fieldName + '" value="' + escapeHtml(value || '') + '">';
            html += '<span class="form-control-plaintext form-control-sm py-0">' + escapeHtml(label) + '</span>';
            return html;
        }

        if (col.type === 'select') {
            html += '<select class="form-control form-control-sm" name="' + fieldName + '"' + required + '>';
            html += '<option value="">Seleccione...</option>';
            Object.keys(col.options || {}).forEach(function (optVal) {
                const selected = String(value) === String(optVal) ? ' selected' : '';
                html += '<option value="' + escapeHtml(optVal) + '"' + selected + '>' + escapeHtml(col.options[optVal]) + '</option>';
            });
            html += '</select>';
        } else if (col.type === 'date_range') {
            html += buildDateRangeField(name, index, col, value, !!col.required);
        } else if (col.type === 'digits') {
            html += '<input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control form-control-sm tabla-dinamica-input-digits" name="' + fieldName + '" value="' + escapeHtml(value || '') + '"' + maxAttr + required + '>';
        } else {
            const type = col.type || 'text';
            let extra = maxAttr;
            if (type === 'number') {
                if (col.min !== undefined) extra += ' min="' + col.min + '"';
                if (col.max !== undefined) extra += ' max="' + col.max + '"';
            }
            html += '<input type="' + type + '" class="form-control form-control-sm" name="' + fieldName + '" value="' + escapeHtml(value || '') + '"' + extra + required + '>';
        }

        return html;
    }

    function buildTableRow(name, index, columnas, textoEliminar, filaData, permitirEliminar) {
        const fila = filaData || {};
        let cells = '';

        columnas.forEach(function (col) {
            const label = col.label + (col.required ? ' *' : '');
            const value = fila[col.key] || '';
            cells += '<td data-label="' + escapeHtml(label) + '">' + buildField(name, index, col, value) + '</td>';
        });

        if (permitirEliminar !== false) {
            cells += '<td class="text-center tabla-dinamica-actions" data-label=""><button type="button" class="btn btn-outline-danger btn-sm tabla-dinamica-remove" title="' + escapeHtml(textoEliminar) + '"><i class="fas fa-trash-alt"></i></button></td>';
        }

        return '<tr class="tabla-dinamica-row" data-index="' + index + '">' + cells + '</tr>';
    }

    function isInsideHiddenContainer(el) {
        let node = el;

        while (node && node !== document.body) {
            if (node.classList && node.classList.contains('d-none')) {
                return true;
            }
            node = node.parentElement;
        }

        return false;
    }

    /**
     * Deshabilita campos dentro de secciones ocultas (p. ej. hijos cuando tiene_hijos = no)
     * para que no bloqueen el envío del formulario.
     */
    function syncWrapperFields(wrapper) {
        const hidden = isInsideHiddenContainer(wrapper);

        wrapper.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.type === 'button' || el.classList.contains('tabla-dinamica-add')) {
                return;
            }

            el.disabled = hidden;

            if (hidden && el.required) {
                el.dataset.preserveRequired = '1';
                el.required = false;
            } else if (!hidden && el.dataset.preserveRequired === '1') {
                el.required = true;
            }
        });

        const addBtn = wrapper.querySelector('.tabla-dinamica-add');
        if (addBtn) {
            addBtn.disabled = hidden;
        }

        // Último: «Sigue laborando» debe sobrescribir el estado genérico de arriba.
        initFechasLaboradasRanges(wrapper);
    }

    function syncAllWrappers() {
        document.querySelectorAll('[data-tabla-dinamica]').forEach(syncWrapperFields);
    }

    function notifyChanged(wrapper) {
        wrapper.dispatchEvent(new CustomEvent('tabla-dinamica:changed', { bubbles: true }));
    }

    function confirmRemove(callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar fila?',
                text: 'Los datos de esta fila se perderán.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
            }).then(function (result) {
                if (result.isConfirmed) {
                    callback();
                }
            });
            return;
        }

        if (window.confirm('¿Eliminar esta fila?')) {
            callback();
        }
    }

    function syncRemoveButtons(wrapper) {
        if (wrapper.dataset.permitirEliminar === '0') {
            return;
        }

        const minFilas = parseInt(wrapper.dataset.minFilas || '0', 10);
        const tbody = wrapper.querySelector('.tabla-dinamica-body');
        const rows = tbody ? tbody.querySelectorAll('.tabla-dinamica-row') : [];
        const canRemove = rows.length > minFilas;
        const textoEliminar = wrapper.dataset.textoEliminar || 'Eliminar';
        const tituloBloqueado = 'Mínimo ' + minFilas + ' fila(s) requerida(s)';

        rows.forEach(function (row) {
            const btn = row.querySelector('.tabla-dinamica-remove');
            if (!btn) {
                return;
            }

            btn.disabled = !canRemove;
            btn.title = canRemove ? textoEliminar : tituloBloqueado;
            btn.setAttribute('aria-disabled', canRemove ? 'false' : 'true');
        });
    }

    function removeEmptyRows(wrapper) {
        const columnas = JSON.parse(wrapper.dataset.columnas || '[]');
        const tbody = wrapper.querySelector('.tabla-dinamica-body');
        if (!tbody || columnas.length === 0) {
            return;
        }

        tbody.querySelectorAll('.tabla-dinamica-row').forEach(function (row) {
            const vacia = columnas.every(function (col) {
                if (col.type === 'date_range') {
                    return isDateRangeEmpty(row, col);
                }

                const input = row.querySelector('[name$="[' + col.key + ']"]');
                return !input || !String(input.value || '').trim();
            });

            if (vacia) {
                row.remove();
            }
        });

        reindexWrapper(wrapper);
        syncWrapperFields(wrapper);
    }

    function removeEmptyRowsAll() {
        document.querySelectorAll('[data-tabla-dinamica]').forEach(removeEmptyRows);
    }

    function reindexWrapper(wrapper) {
        const name = wrapper.dataset.name;
        const columnas = JSON.parse(wrapper.dataset.columnas || '[]');
        const tbody = wrapper.querySelector('.tabla-dinamica-body');
        const emptyMsg = wrapper.querySelector('.tabla-dinamica-empty');
        const tableRows = tbody ? Array.from(tbody.querySelectorAll('.tabla-dinamica-row')) : [];

        tableRows.forEach(function (row, index) {
            columnas.forEach(function (col) {
                if (col.type === 'date_range') {
                    reindexDateRangeField(row, name, index, col);
                    return;
                }

                const input = row.querySelector('[name$="[' + col.key + ']"]');
                if (input) {
                    input.name = name + '[' + index + '][' + col.key + ']';
                }
            });
            row.dataset.index = String(index);
        });

        if (emptyMsg) {
            emptyMsg.classList.toggle('d-none', tableRows.length > 0);
        }

        syncRemoveButtons(wrapper);
    }

    function addRow(wrapper) {
        if (wrapper.dataset.permitirAgregar === '0') {
            return;
        }

        const name = wrapper.dataset.name;
        const columnas = JSON.parse(wrapper.dataset.columnas || '[]');
        const textoEliminar = wrapper.dataset.textoEliminar || 'Eliminar';
        const tbody = wrapper.querySelector('.tabla-dinamica-body');
        const index = tbody ? tbody.querySelectorAll('.tabla-dinamica-row').length : 0;
        const permitirEliminar = wrapper.dataset.permitirEliminar !== '0';

        if (tbody) {
            tbody.insertAdjacentHTML('beforeend', buildTableRow(name, index, columnas, textoEliminar, {}, permitirEliminar));
        }

        reindexWrapper(wrapper);
        syncWrapperFields(wrapper);
        notifyChanged(wrapper);
    }

    function removeRow(wrapper, rowIndex) {
        if (wrapper.dataset.permitirEliminar === '0') {
            return;
        }

        const tbody = wrapper.querySelector('.tabla-dinamica-body');
        const minFilas = parseInt(wrapper.dataset.minFilas || '0', 10);
        const count = tbody ? tbody.querySelectorAll('.tabla-dinamica-row').length : 0;

        if (count <= minFilas) {
            return;
        }

        if (tbody) {
            const tableRow = tbody.querySelector('.tabla-dinamica-row[data-index="' + rowIndex + '"]');
            if (tableRow) {
                tableRow.remove();
            }
        }

        reindexWrapper(wrapper);
        syncWrapperFields(wrapper);
        notifyChanged(wrapper);
    }

    function sanitizeDigitsInput(input) {
        const cleaned = String(input.value || '').replace(/\D/g, '');
        if (cleaned !== input.value) {
            input.value = cleaned;
        }
    }

    function initWrapper(wrapper) {
        const addBtn = wrapper.querySelector('.tabla-dinamica-add');
        if (addBtn && wrapper.dataset.permitirAgregar !== '0') {
            addBtn.addEventListener('click', function () {
                addRow(wrapper);
            });
        }

        wrapper.addEventListener('click', function (event) {
            if (wrapper.dataset.permitirEliminar === '0') {
                return;
            }

            const btn = event.target.closest('.tabla-dinamica-remove');
            if (!btn || !wrapper.contains(btn)) {
                return;
            }
            if (btn.disabled) {
                return;
            }

            const row = btn.closest('.tabla-dinamica-row');
            if (!row) {
                return;
            }

            confirmRemove(function () {
                removeRow(wrapper, row.dataset.index);
            });
        });

        reindexWrapper(wrapper);
        syncWrapperFields(wrapper);
    }

    document.addEventListener('input', function (event) {
        if (event.target.classList && event.target.classList.contains('tabla-dinamica-input-digits')) {
            sanitizeDigitsInput(event.target);
        }
    });

    document.addEventListener('paste', function (event) {
        if (event.target.classList && event.target.classList.contains('tabla-dinamica-input-digits')) {
            window.requestAnimationFrame(function () {
                sanitizeDigitsInput(event.target);
            });
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches && event.target.matches('[data-fechas-actual]')) {
            const wrap = event.target.closest('[data-fechas-laboradas-range]');
            if (wrap) {
                syncFechasLaboradasRange(wrap);
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tabla-dinamica]').forEach(initWrapper);

        document.addEventListener('mousedown', function (event) {
            const submitBtn = event.target.closest('#cuestionarioForm [type="submit"]');
            if (submitBtn) {
                syncAllWrappers();
            }
        }, true);

        document.addEventListener('touchstart', function (event) {
            const submitBtn = event.target.closest('#cuestionarioForm [type="submit"]');
            if (submitBtn) {
                syncAllWrappers();
            }
        }, true);

        document.addEventListener('cuestionario:sync-fields', syncAllWrappers);

        document.addEventListener('condicional:shown', function (event) {
            if (!event.target) {
                return;
            }

            event.target.querySelectorAll('[data-tabla-dinamica]').forEach(syncWrapperFields);
        });

        const cuestionarioForm = document.getElementById('cuestionarioForm');
        if (cuestionarioForm) {
            cuestionarioForm.addEventListener('change', function () {
                window.requestAnimationFrame(syncAllWrappers);
            });

            cuestionarioForm.addEventListener('submit', function () {
                syncAllWrappers();
                prepararFechasLaboradasParaEnvio(cuestionarioForm);
            }, true);
        }
    });

    window.TablaDinamica = {
        syncAll: syncAllWrappers,
        sync: syncWrapperFields,
        reindex: reindexWrapper,
        buildRow: buildTableRow,
        removeEmptyRowsAll: removeEmptyRowsAll,
        prepararFechasLaboradasParaEnvio: prepararFechasLaboradasParaEnvio,
    };
})();
