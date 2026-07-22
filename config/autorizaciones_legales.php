<?php

/**
 * Plantillas de autorización legal por servicio + tipo de formulario (Fase A).
 * Textos actuales: borrador funcional — NO copia literal del cliente.
 * Pendiente A.6: swap con 7 autorizaciones + Infornet definitivos de REPRO.
 * Pedir al entregar versión de pruebas + repetir en informe final (ver CONTEXTO_AGENTES.md / PROGRESS.md).
 */
return [
    'infornet' => <<<'HTML'
<h5 class="text-center mb-3">AUTORIZACIÓN PARA CONSULTA EN INFORNET</h5>
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con documento de identificación número <strong>:dpi:</strong>, por medio de la presente autorizo de manera expresa, libre e informada a <strong>REPRO Guatemala</strong> y/o a la empresa solicitante <strong>:empresa:</strong>, para consultar mi historial laboral, referencias e información relacionada en la base de datos <strong>INFORNET</strong> y demás fuentes autorizadas por la legislación guatemalteca.</p>
<p>Declaro que esta autorización se otorga exclusivamente para fines de evaluación de confiabilidad en el marco del proceso de <strong>:tipo_evaluacion:</strong> contratado por la empresa solicitante.</p>
<p>Entiendo que la información obtenida será tratada de forma confidencial y utilizada únicamente para los fines descritos.</p>
HTML,

    'plantillas' => [
        'poligrafo_preempleo' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN POLIGRÁFICA — PRE-EMPLEO',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, por medio de la presente autorizo libre, expresa e informadamente a <strong>REPRO Guatemala</strong> para que realice una <strong>evaluación poligráfica de pre-empleo</strong> solicitada por la empresa <strong>:empresa:</strong>, para el puesto de <strong>:puesto:</strong>.</p>
<p>Declaro que:</p>
<ol>
<li>Participo de manera <strong>voluntaria</strong> en este proceso.</li>
<li>He sido informado(a) sobre la naturaleza, alcance y procedimiento de la evaluación poligráfica.</li>
<li>Autorizo la recopilación, almacenamiento y tratamiento de mis datos personales exclusivamente para los fines de esta evaluación.</li>
<li>Entiendo que los resultados serán compartidos con la empresa solicitante indicada.</li>
<li>La información que proporcionaré es verídica según mi mejor conocimiento.</li>
<li>Autorizo el uso de firma electrónica como constancia de mi aceptación.</li>
</ol>
<div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
<strong>Consentimiento adicional — evaluación poligráfica:</strong>
<p class="mb-0 mt-1">Autorizo que se me realice una evaluación mediante polígrafo (detector de verdad). Declaro que me encuentro en pleno uso de mis facultades mentales, no me encuentro bajo efectos de sustancias que alteren mi estado de conciencia y no tengo impedimento médico conocido para realizar este examen.</p>
</div>
HTML,
        ],
        'poligrafo_periodica' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN POLIGRÁFICA — PERIÓDICA',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, autorizo libre y voluntariamente a <strong>REPRO Guatemala</strong> para realizar una <strong>evaluación poligráfica periódica</strong> solicitada por la empresa <strong>:empresa:</strong>.</p>
<p><strong>Motivo de la evaluación:</strong> :motivo_hecho:</p>
<p>Declaro que participo de manera voluntaria, he sido informado(a) del procedimiento, autorizo el tratamiento de mis datos para este fin y acepto que los resultados serán compartidos con la empresa solicitante.</p>
<div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
<strong>Consentimiento adicional — evaluación poligráfica:</strong>
<p class="mb-0 mt-1">Autorizo la evaluación mediante polígrafo. Declaro pleno uso de facultades mentales y ausencia de impedimentos médicos conocidos para el examen.</p>
</div>
HTML,
        ],
        'poligrafo_especifica' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN POLIGRÁFICA — ESPECÍFICA',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, autorizo libre y voluntariamente a <strong>REPRO Guatemala</strong> para realizar una <strong>evaluación poligráfica específica</strong> solicitada por la empresa <strong>:empresa:</strong>, relacionada con el siguiente hecho o situación a investigar:</p>
<p><strong>Hecho / situación:</strong> :motivo_hecho:</p>
<p>Declaro que participo de manera voluntaria, he sido informado(a) del procedimiento, autorizo el tratamiento de mis datos para este fin y acepto que los resultados serán compartidos con la empresa solicitante.</p>
<div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
<strong>Consentimiento adicional — evaluación poligráfica:</strong>
<p class="mb-0 mt-1">Autorizo la evaluación mediante polígrafo. Declaro pleno uso de facultades mentales y ausencia de impedimentos médicos conocidos para el examen.</p>
</div>
HTML,
        ],
        'vsa_preempleo' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN VSA — PRE-EMPLEO',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, autorizo libre y voluntariamente a <strong>REPRO Guatemala</strong> para realizar una <strong>evaluación VSA (Voice Stress Analysis) de pre-empleo</strong> solicitada por la empresa <strong>:empresa:</strong>, para el puesto de <strong>:puesto:</strong>.</p>
<p>Declaro participación voluntaria, conocimiento del procedimiento, autorización del tratamiento de datos para este fin y aceptación de que los resultados serán compartidos con la empresa solicitante.</p>
<div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
<strong>Consentimiento adicional — evaluación VSA:</strong>
<p class="mb-0 mt-1">Autorizo la evaluación mediante análisis de estrés de voz (VSA). Declaro pleno uso de facultades mentales y ausencia de impedimentos médicos conocidos.</p>
</div>
HTML,
        ],
        'vsa_periodica' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN VSA — PERIÓDICA',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, autorizo a <strong>REPRO Guatemala</strong> para realizar una <strong>evaluación VSA periódica</strong> solicitada por <strong>:empresa:</strong>.</p>
<p><strong>Motivo de la evaluación:</strong> :motivo_hecho:</p>
<p>Declaro participación voluntaria, conocimiento del procedimiento y autorización del tratamiento de datos para este fin.</p>
<div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
<strong>Consentimiento adicional — evaluación VSA:</strong>
<p class="mb-0 mt-1">Autorizo la evaluación mediante análisis de estrés de voz (VSA).</p>
</div>
HTML,
        ],
        'vsa_especifica' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN VSA — ESPECÍFICA',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, autorizo a <strong>REPRO Guatemala</strong> para realizar una <strong>evaluación VSA específica</strong> solicitada por <strong>:empresa:</strong>, relacionada con:</p>
<p><strong>Hecho / situación:</strong> :motivo_hecho:</p>
<p>Declaro participación voluntaria, conocimiento del procedimiento y autorización del tratamiento de datos para este fin.</p>
<div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning rounded">
<strong>Consentimiento adicional — evaluación VSA:</strong>
<p class="mb-0 mt-1">Autorizo la evaluación mediante análisis de estrés de voz (VSA).</p>
</div>
HTML,
        ],
        'socioeconomico_preempleo' => [
            'titulo' => 'AUTORIZACIÓN PARA ESTUDIO SOCIOECONÓMICO — PRE-EMPLEO',
            'cuerpo' => <<<'HTML'
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con DPI número <strong>:dpi:</strong>, autorizo libre y voluntariamente a <strong>REPRO Guatemala</strong> para realizar un <strong>estudio socioeconómico de pre-empleo</strong> solicitado por la empresa <strong>:empresa:</strong>, para el puesto de <strong>:puesto:</strong>.</p>
<p>Declaro que:</p>
<ol>
<li>Participo de manera voluntaria en este proceso.</li>
<li>He sido informado(a) sobre el alcance del estudio socioeconómico.</li>
<li>Autorizo la recopilación, verificación y tratamiento de la información proporcionada y obtenida de fuentes autorizadas.</li>
<li>Entiendo que los resultados serán compartidos con la empresa solicitante.</li>
<li>La información que proporcionaré es verídica según mi mejor conocimiento.</li>
<li>Autorizo el uso de firma electrónica como constancia de mi aceptación.</li>
</ol>
HTML,
        ],
    ],
];
