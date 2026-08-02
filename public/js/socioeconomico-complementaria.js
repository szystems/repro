(function () {
    'use strict';

    function parseNum(value) {
        var n = parseFloat(String(value).replace(/,/g, ''));
        return isNaN(n) ? 0 : n;
    }

    function formatQ(amount) {
        return amount.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcularWrapper(outer) {
        var fieldKey = outer.getAttribute('data-total-field') || 'valor';
        var displayId = outer.getAttribute('data-display-id');
        var hiddenId = outer.getAttribute('data-hidden-id');
        var wrapper = outer.querySelector('[data-tabla-dinamica]');
        var total = 0;

        if (wrapper) {
            wrapper.querySelectorAll('tbody tr').forEach(function (row) {
                var input = row.querySelector('[name*="[' + fieldKey + ']"]');
                if (input) {
                    total += parseNum(input.value);
                }
            });
        } else if (outer.id === 'presupuesto_fijo_wrapper') {
            outer.querySelectorAll('.presupuesto-monto').forEach(function (input) {
                total += parseNum(input.value);
            });
        } else {
            return;
        }

        var display = document.getElementById(displayId);
        var hidden = document.getElementById(hiddenId);
        if (display) display.value = formatQ(total);
        if (hidden) hidden.value = total.toFixed(2);
    }

    function bindTotales() {
        document.querySelectorAll('.socio-total-wrapper').forEach(function (outer) {
            var recalc = function () { recalcularWrapper(outer); };
            outer.addEventListener('input', recalc);
            outer.addEventListener('change', recalc);
            document.addEventListener('tabla-dinamica:changed', recalc);
            recalc();
        });
    }

    function addLaborRefRow(wrapper, data) {
        var addBtn = wrapper.querySelector('.tabla-dinamica-add');
        if (addBtn) addBtn.click();
        var row = wrapper.querySelector('.tabla-dinamica-body .tabla-dinamica-row:last-child');
        if (!row) return;
        Object.keys(data).forEach(function (key) {
            var input = row.querySelector('[name$="[' + key + ']"]');
            if (input) input.value = data[key] || '';
        });
    }

    function importarEmpleos() {
        var btn = document.getElementById('btnImportarEmpleos');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var empleos;
            try {
                empleos = JSON.parse(btn.getAttribute('data-empleos') || '[]');
            } catch (e) {
                empleos = [];
            }
            if (!empleos.length) return;

            var wrapper = document.querySelector('[data-tabla-dinamica][data-name="referencias_laborales"]');
            if (!wrapper) return;

            empleos.forEach(function (emp) {
                addLaborRefRow(wrapper, {
                    empresa: emp.empresa || '',
                    contacto: emp.jefe_inmediato || emp.contacto_rrhh || '',
                    telefono: emp.contacto_rrhh || '',
                    puesto: emp.puesto || '',
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindTotales();
        importarEmpleos();
    });
})();
