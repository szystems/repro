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
        }
    });

    window.TablaDinamica = {
        syncAll: syncAllWrappers,
        sync: syncWrapperFields,
        reindex: reindexWrapper,
        buildRow: buildTableRow,
        removeEmptyRowsAll: removeEmptyRowsAll,
    };
})();
