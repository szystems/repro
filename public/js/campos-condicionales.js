/**
 * Fase F — E1.2: campos condicionales reutilizables (data-attributes + JS).
 *
 * Uso en Blade:
 *   <x-campo-condicional trigger="tiene_hijos" show-when="si" id="seccion_hijos">...</x-campo-condicional>
 *
 * O directamente:
 *   <div data-condicional data-condicional-trigger="tiene_hijos" data-condicional-show-when="si" class="d-none">
 */
(function () {
    'use strict';

    function parseValues(str) {
        return (str || '').split(',').map(function (value) {
            return value.trim();
        }).filter(Boolean);
    }

    function findTriggerElements(trigger) {
        const byId = document.getElementById(trigger);
        if (byId && byId.name) {
            if (byId.type === 'radio') {
                return Array.from(document.querySelectorAll('[name="' + byId.name + '"]'));
            }
            return [byId];
        }

        const byName = document.querySelectorAll('[name="' + trigger + '"]');
        if (byName.length > 0) {
            return Array.from(byName);
        }

        if (byId) {
            return [byId];
        }

        return [];
    }

    function getTriggerValue(trigger) {
        const elements = findTriggerElements(trigger);
        if (elements.length === 0) {
            return '';
        }

        if (elements[0].type === 'radio') {
            const checked = elements.find(function (el) {
                return el.checked;
            });
            return checked ? checked.value : '';
        }

        return elements[0].value || '';
    }

    function shouldShow(container) {
        const trigger = container.dataset.condicionalTrigger;
        if (!trigger) {
            return true;
        }

        const value = getTriggerValue(trigger);
        const showWhen = parseValues(container.dataset.condicionalShowWhen);
        const hideWhen = parseValues(container.dataset.condicionalHideWhen);

        if (showWhen.length > 0) {
            return showWhen.includes(value);
        }

        if (hideWhen.length > 0) {
            return !hideWhen.includes(value);
        }

        return true;
    }

    function clearField(el) {
        if (el.type === 'checkbox' || el.type === 'radio') {
            el.checked = false;
            return;
        }

        if (el.type === 'button' || el.classList.contains('tabla-dinamica-add')) {
            return;
        }

        if (el.tagName === 'SELECT') {
            el.selectedIndex = 0;
            return;
        }

        el.value = '';
    }

    function clearContainer(container) {
        container.querySelectorAll('input, select, textarea').forEach(clearField);
    }

    function syncFieldState(el, hidden) {
        if (el.type === 'button' || el.classList.contains('tabla-dinamica-add')) {
            el.disabled = hidden;
            return;
        }

        el.disabled = hidden;

        if (hidden && el.required) {
            el.dataset.preserveRequired = '1';
            el.required = false;
        } else if (!hidden && el.dataset.preserveRequired === '1') {
            el.required = true;
        }
    }

    function syncConditionalRequiredFields() {
        document.querySelectorAll('[data-condicional-required-trigger]').forEach(function (el) {
            const value = getTriggerValue(el.dataset.condicionalRequiredTrigger);
            const when = parseValues(el.dataset.condicionalRequiredWhen);
            el.required = when.includes(value);
        });
    }

    function syncContainer(container) {
        const wasVisible = !container.classList.contains('d-none');
        const visible = shouldShow(container);

        if (wasVisible && !visible && container.dataset.condicionalClearOnHide === '1') {
            clearContainer(container);
        }

        container.classList.toggle('d-none', !visible);

        container.querySelectorAll('input, select, textarea, button').forEach(function (el) {
            syncFieldState(el, !visible);
        });

        if (!wasVisible && visible) {
            container.dispatchEvent(new CustomEvent('condicional:shown', { bubbles: true }));
        }
    }

    function syncAll() {
        document.querySelectorAll('[data-condicional]').forEach(syncContainer);
        syncConditionalRequiredFields();

        if (window.TablaDinamica && typeof window.TablaDinamica.syncAll === 'function') {
            window.TablaDinamica.syncAll();
        }
    }

    function bindTriggers() {
        const triggers = new Set();

        document.querySelectorAll('[data-condicional]').forEach(function (container) {
            if (container.dataset.condicionalTrigger) {
                triggers.add(container.dataset.condicionalTrigger);
            }
        });

        document.querySelectorAll('[data-condicional-required-trigger]').forEach(function (el) {
            triggers.add(el.dataset.condicionalRequiredTrigger);
        });

        triggers.forEach(function (trigger) {
            findTriggerElements(trigger).forEach(function (el) {
                el.addEventListener('change', syncAll);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindTriggers();
        syncAll();

        document.addEventListener('mousedown', function (event) {
            if (event.target.closest('#cuestionarioForm [type="submit"]')) {
                syncAll();
            }
        }, true);

        document.addEventListener('touchstart', function (event) {
            if (event.target.closest('#cuestionarioForm [type="submit"]')) {
                syncAll();
            }
        }, true);
    });

    window.CamposCondicionales = {
        syncAll: syncAll,
    };
})();
