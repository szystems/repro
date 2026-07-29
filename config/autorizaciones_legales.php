<?php

/** * Plantillas oficiales de autorización legal (G5 — doc cliente jul-2026). * Fuente: docs/repro/AUTORIZACIÓN PARA EVALUACIÓN (1).docx * Infornet: solo procesos pre-empleo (polígrafo, VSA, socioeconómico). */
return [
    'infornet' => <<<'HTML'
<h5 class="text-center mb-3">AUTORIZACIÓN PARA CONSULTA EN CENTRALES DE RIESGO (INFORNET)</h5>
<p>Yo, <strong>:nombre_completo:</strong>, identificado(a) con documento de identificación número <strong>:dpi:</strong>, autorizo voluntariamente que la información recopilada y/o proporcionada por entidades públicas o privadas y la generada de relaciones contractuales, crediticias o comerciales, sea reportada a entidades que prestan servicios de información, centrales de riesgo o burós de crédito para ser tratada, almacenada o transferida; y autorizo expresamente a las entidades que prestan servicios de información, centrales de riesgo y burós de crédito a recopilar, difundir o comercializar reportes o estudios que contengan información sobre mi persona.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,

    'plantillas' => [
        'poligrafo_preempleo' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN (POLÍGRAFO PREEMPLEO)',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice la siguiente evaluación:</p>
<p>Prueba de Poligráfica – Preempleo</p>
<p>He sido informado(a) de forma clara sobre la naturaleza y objetivos de esta evaluación y autorizo que el proceso pueda incluir:</p>
<p>Entrevista de seguridad orientada a conocer información sobre mi historial personal y laboral, situación económica, entorno familiar y social, cumplimiento de normas y otros aspectos vinculados a los fines de la evaluación solicitada.</p>
<p>Verificación de experiencia laboral, mediante consultas a empleadores anteriores, referencias laborales, planillas y otras fuentes oficiales que permitan validar la información proporcionada.</p>
<p>Verificación de documentación presentada.</p>
<p>Consulta de antecedentes judiciales, policiales y registros relacionados.</p>
<p>Consulta de historial crediticio.</p>
<p>Validación de información a través de referencias personales y familiares.</p>
<p>Toma de fotografías personales de perfil y de tatuajes visibles cuando corresponda.</p>
<p>Registros de grafotecnia y dactiloscopia cuando sean solicitados por la empresa.</p>
<p><strong>Declaro que:</strong></p><ul>
<li>Participo de forma libre y voluntaria, sin coacción alguna.</li>
<p>Entiendo y autorizo que durante la evaluación poligráfica se me colocarán sensores e instrumentos de medición fisiológica que forman parte integral del equipo de evaluación y son necesarios para el adecuado desarrollo de la prueba.</p>
<li>Me encuentro en condiciones físicas y mentales aptas para realizar la evaluación y que no presento ningún malestar actual que pueda afectar mi participación en el proceso.</li>
<li>Me comprometo a mantener una conducta respetuosa durante el proceso de evaluación y a proporcionar información y documentación veraz, precisa y completa. Entiendo que cualquier comportamiento inapropiado, así como la falsedad u omisión de información relevante, podrá afectar el desarrollo de la evaluación y resultado.</li>
<li>Autorizo la recopilación, almacenamiento, consulta y verificación de mis datos personales para fines de evaluación.</li>
</ul>
<p>Entiendo que esta evaluación es únicamente una herramienta de apoyo para la toma de decisiones de la empresa solicitante y no garantiza contratación.</p>
</ul>
<p>Entiendo que los resultados serán tratados de forma confidencial y entregados exclusivamente a la empresa :nombre_completo:.</p>
<p>Libero de responsabilidad a REPRO, a sus colaboradores, a la empresa solicitante :empresa: y a las personas o entidades que proporcionen información dentro del marco del presente proceso de evaluación.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
        'vsa_preempleo' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN (VSA PREEMPLEO)',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice la siguiente evaluación:</p>
<p>Análisis de Estrés de Voz – Preempleo</p>
<p>He sido informado(a) de forma clara sobre la naturaleza y objetivos de esta evaluación y autorizo que el proceso pueda incluir:</p>
<p>Entrevista de seguridad orientada a conocer información sobre mi historial personal y laboral, situación económica, entorno familiar y social, cumplimiento de normas y otros aspectos vinculados a los fines de la evaluación solicitada.</p>
<p>Verificación de experiencia laboral, mediante consultas a empleadores anteriores, referencias laborales, planillas y otras fuentes oficiales que permitan validar la información proporcionada.</p>
<p>Verificación de documentación presentada.</p>
<p>Consulta de antecedentes judiciales, policiales y registros relacionados.</p>
<p>Consulta de historial crediticio.</p>
<p>Validación de información a través de referencias personales y familiares.</p>
<p>Toma de fotografías personales de perfil y de tatuajes visibles cuando corresponda.</p>
<p>Registros de grafotecnia y dactiloscopia cuando sean solicitados por la empresa.</p>
<p><strong>Declaro que:</strong></p><ul>
<li>Participo de forma libre y voluntaria, sin coacción alguna.</li>
<li>Entiendo que durante el proceso mi voz será: registrada, graficada y analizada mediante herramientas tecnológicas CVSA III como parte del procedimiento normal de evaluación.</li>
<li>Me encuentro en condiciones físicas y mentales aptas para realizar la evaluación y que no presento ningún malestar actual que pueda afectar mi participación en el proceso.</li>
<li>Me comprometo a mantener una conducta respetuosa durante el proceso de evaluación y a proporcionar información y documentación veraz, precisa y completa. Entiendo que cualquier comportamiento inapropiado, así como la falsedad u omisión de información relevante, podrá afectar el desarrollo de la evaluación y resultado.</li>
<li>Autorizo la recopilación, almacenamiento, consulta y verificación de mis datos personales para fines de evaluación.</li>
</ul>
<p>Entiendo que esta evaluación es únicamente una herramienta de apoyo para la toma de decisiones de la empresa solicitante y no garantiza contratación.</p>
</ul>
<p>Entiendo que los resultados serán tratados de forma confidencial y entregados exclusivamente a la empresa :nombre_completo:.</p>
<p>Libero de responsabilidad a REPRO, a sus colaboradores, a la empresa solicitante :empresa: y a las personas o entidades que proporcionen información dentro del marco del presente proceso de evaluación.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
        'poligrafo_periodica' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN (POLÍGRAFO PERIÓDICA)',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice la siguiente evaluación: Prueba de Polígrafo – Periódica</p>
<p><strong>Por motivo de:</strong> :motivo_hecho:</p>
<p><strong>Declaro que:</strong></p><ul>
<li>Participo de forma libre y voluntaria, sin coacción alguna.</li>
<li>Entiendo que esta evaluación tiene como propósito respaldar decisiones organizacionales relacionadas con la confianza, idoneidad y desempeño en el marco de los procesos internos de la empresa, y no constituye en sí misma un juicio definitivo sobre mi conducta o capacidad laboral.</li>
<li>Autorizo que el proceso pueda incluir: Una entrevista de seguridad orientada a recopilar información relacionada con mi situación laboral, cumplimiento de políticas internas, confiabilidad y otros aspectos vinculados con los objetivos de la evaluación. Toma de fotografías personales de perfil y de tatuajes visibles cuando corresponda.</li>
<p>Entiendo y autorizo que durante la evaluación poligráfica se me colocarán sensores e instrumentos de medición fisiológica que forman parte integral del equipo de evaluación y son necesarios para el adecuado desarrollo de la prueba.</p>
<p>Declaro que me encuentro en condiciones físicas y mentales aptas para realizar la evaluación y que no presento ningún malestar actual que pueda afectar mi participación en el proceso.</p>
</ul>
<p>Entiendo que los resultados serán entregados exclusivamente a la empresa solicitante, en este caso a través de su área de Recursos Humanos o personal designado, garantizando siempre la confidencialidad y el manejo ético de la información.</p>
<p>Declaro que la información que proporcionaré durante el proceso es veraz y completa. Reconozco que la falsedad, omisión o manipulación de información o documentos constituye una falta grave, lo cual puede afectar mi continuidad o participación en el proceso.</p>
<p>En virtud de la presente, absuelvo de toda responsabilidad legal, moral, laboral o administrativa a la empresa REPRO, así como a la empresa solicitante xxxx, por la aplicación de la prueba de polígrafo/VSA y el uso de sus resultados dentro del marco descrito. Reconozco que la prueba es una herramienta de apoyo para la toma de decisiones.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
        'vsa_periodica' => [
            'titulo' => 'AUTORIZACIÓN PARA EVALUACIÓN (VSA PERIÓDICA)',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice la siguiente evaluación: Análisis de Estrés de Voz – Periódica</p>
<p><strong>Por motivo de:</strong> :motivo_hecho:</p>
<p>Participo de forma libre y voluntaria, sin coacción alguna.</p>
<p>Entiendo que esta evaluación tiene como propósito respaldar decisiones organizacionales relacionadas con la confianza, idoneidad y desempeño en el marco de los procesos internos de la empresa, y no constituye en sí misma un juicio definitivo sobre mi conducta o capacidad laboral.</p>
<p>Autorizo que el proceso pueda incluir: Una entrevista de seguridad orientada a recopilar información relacionada con mi situación laboral, cumplimiento de políticas internas, confiabilidad y otros aspectos vinculados con los objetivos de la evaluación. Toma de fotografías personales de perfil y de tatuajes visibles cuando corresponda.</p>
<p>Entiendo que durante el proceso mi voz será: registrada, graficada y analizada mediante herramientas tecnológicas CVSA III como parte del procedimiento normal de evaluación.</p>
<p>Declaro que me encuentro en condiciones físicas y mentales aptas para realizar la evaluación y que no presento ningún malestar actual que pueda afectar mi participación en el proceso.</p>
<p>Entiendo que los resultados serán entregados exclusivamente a la empresa solicitante, en este caso a través de su área de Recursos Humanos o personal designado, garantizando siempre la confidencialidad y el manejo ético de la información.</p>
<p>Declaro que la información que proporcionaré durante el proceso es veraz y completa. Reconozco que la falsedad, omisión o manipulación de información o documentos constituye una falta grave, lo cual puede afectar mi continuidad o participación en el proceso.</p>
<p>En virtud de la presente, absuelvo de toda responsabilidad legal, moral, laboral o administrativa a la empresa REPRO, así como a la empresa solicitante xxxx, por la aplicación de la prueba de polígrafo/VSA y el uso de sus resultados dentro del marco descrito. Reconozco que la prueba es una herramienta de apoyo para la toma de decisiones.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
        'poligrafo_especifica' => [
            'titulo' => 'AUTORIZACIÓN PARA PRUEBA DE POLÍGRAFO ESPECÍFICA',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice la siguiente evaluación: Prueba de Polígrafo- Específica</p>
<p>Como parte de una investigación interna específica solicitada por la empresa :empresa:. La cual tiene como objetivo apoyar el esclarecimiento de: _______________________________________________</p>
<p>:motivo_hecho:</p>
<p><strong>Declaro que:</strong></p><ul>
<li>Participo de forma libre y voluntaria, sin coacción alguna.</li>
<li>Autorizo que el proceso pueda incluir: Una entrevista de seguridad donde abordarán temas relacionados exclusivamente con el caso que se investiga, así como información personal relevante para el análisis de confiabilidad y contexto del hecho.</li>
<p>Entiendo y autorizo que durante la evaluación poligráfica se me colocarán sensores e instrumentos de medición fisiológica que forman parte integral del equipo de evaluación y son necesarios para el adecuado desarrollo de la prueba, también toma de fotografías personales de perfil y de tatuajes visibles cuando corresponda.</p>
<li>Me encuentro en condiciones físicas y mentales aptas para realizar la evaluación y que no presento ningún malestar actual que pueda afectar mi participación en el proceso. .</li>
<li>Me comprometo a responder con veracidad y colaborar plenamente durante el proceso.</li>
</ul>
<p>Entiendo que esta evaluación no implica un juicio ni una sanción automática, sino que forma parte de un proceso de investigación que considera múltiples elementos.</p>
</ul>
<p>Autorizo a REPRO y a la empresa solicitante a utilizar los resultados de esta prueba exclusivamente para fines internos relacionados con la investigación del caso.</p>
<p>Declaro que la información que proporcionaré durante la prueba de polígrafo, relacionada con la presente investigación, será veraz y completa. Reconozco que cualquier falsedad, omisión o manipulación de información o documentos podrá influir directamente en el resultado de la evaluación.</p>
<p>Me comprometo a mantener una conducta respetuosa durante el proceso de evaluación. Entiendo que cualquier comportamiento inapropiado, así como la falsedad u omisión de información relevante, podrá afectar el desarrollo de la evaluación y resultado.</p>
<p>Libero de toda responsabilidad legal y administrativa a REPRO, sus técnicos, a la empresa solicitante xxxx, y a cualquier persona relacionada con la aplicación e interpretación de esta evaluación.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
        'vsa_especifica' => [
            'titulo' => 'AUTORIZACIÓN PARA VSA ESPECÍFICA',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice la siguiente evaluación: ANÁLISIS DE ESTRÉS DE VOZ- Específica</p>
<p>Como parte de una investigación interna específica solicitada por la empresa :empresa:. La cual tiene como objetivo apoyar el esclarecimiento de: _____________________________________________________</p>
<p>:motivo_hecho:_________</p>
<p><strong>Declaro que:</strong></p><ul>
<li>Participo de forma libre y voluntaria, sin coacción alguna.</li>
<li>Autorizo que el proceso pueda incluir: Una entrevista de seguridad donde abordarán temas relacionados exclusivamente con el caso que se investiga, así como información personal relevante para el análisis de confiabilidad y contexto del hecho, también toma de fotografías personales de perfil y de tatuajes visibles cuando corresponda.</li>
<li>Entiendo que durante el proceso mi voz será: registrada, graficada y analizada mediante herramientas tecnológicas CVSA III como parte del procedimiento normal de evaluación</li>
<li>Me encuentro en condiciones físicas y mentales aptas para realizar la evaluación y que no presento ningún malestar actual que pueda afectar mi participación en el proceso.</li>
<li>Me comprometo a responder con veracidad y colaborar plenamente durante el proceso.</li>
</ul>
<p>Entiendo que esta evaluación no implica un juicio ni una sanción automática, sino que forma parte de un proceso de investigación que considera múltiples elementos.</p>
</ul>
<p>Autorizo a REPRO y a la empresa solicitante a utilizar los resultados de esta prueba exclusivamente para fines internos relacionados con la investigación del caso.</p>
<p>Declaro que la información que proporcionaré durante la prueba de polígrafo, relacionada con la presente investigación, será veraz y completa. Reconozco que cualquier falsedad, omisión o manipulación de información o documentos podrá influir directamente en el resultado de la evaluación.</p>
<p>Me comprometo a mantener una conducta respetuosa durante el proceso de evaluación. Entiendo que cualquier comportamiento inapropiado, así como la falsedad u omisión de información relevante, podrá afectar el desarrollo de la evaluación y resultado.</p>
<p>Libero de toda responsabilidad legal y administrativa a REPRO, sus técnicos, a la empresa solicitante xxxx, y a cualquier persona relacionada con la aplicación e interpretación de esta evaluación.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
        'socioeconomico_preempleo' => [
            'titulo' => 'AUTORIZACIÓN DE ESTUDIO SOCIOECONÓMICO PREEMPLEO',
            'cuerpo' => <<<'HTML'
<p>Yo, :nombre_completo:, identificado(a) con DPI número :dpi:, por medio de la presente autorizo libre y voluntariamente a REPRO para que realice el siguiente proceso:</p>
<p><strong>ESTUDIO SOCIOECONÓMICO – PREEMPLEO</strong></p>
<p>He sido informado(a) de forma clara sobre la naturaleza y objetivos de esta evaluación y autorizo que el proceso pueda incluir:</p>
<p>Entrevista de seguridad orientada a conocer información sobre mi historial personal y laboral, situación económica, entorno familiar y social, cumplimiento de normas y otros aspectos vinculados a los fines de la evaluación solicitada.</p>
<p>Verificación de experiencia laboral, mediante consultas a empleadores anteriores, referencias laborales, planillas y otras fuentes oficiales que permitan validar la información proporcionada.</p>
<p>Verificación de documentación presentada.</p>
<p>Consulta de antecedentes judiciales, policiales y registros relacionados.</p>
<p>Consulta de historial crediticio.</p>
<p>Validación de información a través de referencias personales y familiares.</p>
<p>Toma de fotografías de personales de perfil y de tatuajes visibles cuando corresponda.</p>
<p>Visita domiciliaria para la verificación de información relevante del estudio, la cual podrá incluir el ingreso a la vivienda con autorización previa, registro fotográfico del inmueble y entrevistas breves con familiares o vecinos, bajo estrictos criterios de confidencialidad, profesionalismo y respeto.</p>
<p>Registros de grafotecnia y dactiloscopia cuando sean solicitados por la empresa.</p>
<p><strong>Declaro que:</strong></p><ul>
<li>Participo de forma libre y voluntaria, sin coacción alguna.</li>
<li>Me encuentro en pleno uso de mis facultades físicas y mentales, y que no tengo impedimentos que limiten mi participación en el proceso.</li>
<li>Me comprometo a mantener una conducta respetuosa durante el proceso de evaluación y a proporcionar información y documentación veraz, precisa y completa. Entiendo que cualquier comportamiento inapropiado, así como la falsedad u omisión de información relevante, podrá afectar el desarrollo del proceso.</li>
<li>Autorizo la recopilación, almacenamiento, consulta y verificación de mis datos personales para fines de evaluación.</li>
<li>Entiendo que este proceso es únicamente una herramienta de apoyo para la toma de decisiones de la empresa solicitante y no garantiza contratación.</li>
</ul>
<p>Entiendo que los resultados serán tratados de forma confidencial y entregados exclusivamente a la empresa :nombre_completo:.</p>
</ul>
<p>Libero de responsabilidad a REPRO, a sus colaboradores, a la empresa solicitante :empresa: y a las personas o entidades que proporcionen información dentro del marco del presente proceso de evaluación.</p>
<p>Autorizo el uso de medios y firmas electrónicas como constancia de mi aceptación.</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
<p><strong>Fecha:</strong> :fecha: &nbsp; <strong>Lugar:</strong> :lugar:</p>
HTML,
        ],
    ],
];
