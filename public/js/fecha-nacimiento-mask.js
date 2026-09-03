/**
 * Máscara dd/mm/aaaa para fecha de nacimiento — mantiene las diagonales visibles.
 * El backend normaliza a ISO (DatosPersonalesCampos::normalizarFechaNacimiento).
 */
(function () {
    'use strict';

    function soloDigitos(valor) {
        return String(valor || '').replace(/\D/g, '').slice(0, 8);
    }

    function formatearDigitos(digitos) {
        if (digitos.length <= 2) {
            return digitos;
        }

        if (digitos.length <= 4) {
            return digitos.slice(0, 2) + '/' + digitos.slice(2);
        }

        return digitos.slice(0, 2) + '/' + digitos.slice(2, 4) + '/' + digitos.slice(4);
    }

    function aplicarMascara(input) {
        const digitos = soloDigitos(input.value);
        const cursorAlFinal = input.selectionStart === input.value.length;

        input.value = formatearDigitos(digitos);

        if (cursorAlFinal) {
            input.setSelectionRange(input.value.length, input.value.length);
        }
    }

    function inicializar(input) {
        if (!input || input.dataset.fechaNacimientoMask === '1') {
            return;
        }

        input.dataset.fechaNacimientoMask = '1';
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('autocomplete', 'bday');
        input.setAttribute('placeholder', 'dd/mm/aaaa');
        input.setAttribute('maxlength', '10');

        if (input.value) {
            aplicarMascara(input);
        }

        input.addEventListener('input', function () {
            aplicarMascara(input);
        });

        input.addEventListener('paste', function () {
            window.requestAnimationFrame(function () {
                aplicarMascara(input);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-fecha-nacimiento]').forEach(inicializar);
    });

    window.FechaNacimientoMask = {
        init: inicializar,
        format: formatearDigitos,
    };
})();
