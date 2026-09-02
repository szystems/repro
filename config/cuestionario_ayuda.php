<?php

/**
 * Ayuda para candidatos (formulario público por token).
 */
return [
    'titulo' => 'Ayuda — Cuestionario REPRO',

    'intro' => 'Guía rápida para completar su formulario de evaluación sin contratiempos.',

    'secciones' => [
        [
            'titulo' => 'Antes de empezar',
            'puntos' => [
                'Use el enlace completo que le enviaron (debe comenzar con reproappv2.szystems.com/cuestionario/…).',
                'Tenga a mano su DPI y datos de contacto actualizados.',
                'Prefiera completar desde celular con buena conexión a internet.',
                'El enlace tiene vigencia limitada; si expiró, pida uno nuevo a REPRO o a la empresa.',
            ],
        ],
        [
            'titulo' => 'Verificación de identidad',
            'puntos' => [
                'Ingrese su DPI de 13 dígitos exactamente como aparece en su documento.',
                'Si el sistema no lo reconoce, verifique que no haya espacios ni guiones.',
            ],
        ],
        [
            'titulo' => 'Completar el formulario',
            'puntos' => [
                'Avance sección por sección; use «Guardar Borrador» si necesita pausar.',
                'Los campos con asterisco (*) son obligatorios.',
                'Si una pregunta no aplica, escriba «N/A».',
                'Fecha de nacimiento: use formato dd/mm/aaaa (ej. 27/12/1994).',
                'En la sección 1 deberá tomar o subir una foto de medio cuerpo.',
            ],
        ],
        [
            'titulo' => 'Problemas frecuentes',
            'puntos' => [
                '«Enlace no válido»: la URL está incompleta o el enlace fue desactivado.',
                '«Enlace vencido»: solicite a REPRO o a su reclutador que habiliten uno nuevo.',
                'No puede entrar otra persona por usted; el formulario es personal e intransferible.',
            ],
        ],
    ],

    'contacto' => 'Si persiste el problema, contacte a la empresa que lo evalúa o a REPRO por WhatsApp.',
];
