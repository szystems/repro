# Análisis y planificación — Formularios de sistema + Informe Word (REPRO)

**Fecha:** 22 de junio de 2026
**Autor:** Szystems (análisis técnico)
**Origen:** Documentos enviados por REPRO el 21-jun-2026 vía WhatsApp
**Documentos analizados:**
1. `CREACIÓN FORMULARIOS DE SISTEMA.pdf` (46 páginas) — especificación completa de formularios.
2. `MOISES ESAU COYOY HERRERA - ALINOR.pdf` (11 páginas) — ejemplo de informe Polígrafo/VSA para el ítem Word.

> **Nota de la cliente (WhatsApp):** *"le envío el archivo de formularios, ya se incluye pruebas periódicas y específicas. Favor omitir el archivo enviado ayer porque realicé unos cambios."* → este documento (`CREACIÓN FORMULARIOS DE SISTEMA.pdf`) es el **vigente**. Se confirma estructura final de **5 secciones** para el formulario matriz, + 1 sección exclusiva para Socioeconómico.

---

## 1. Resumen ejecutivo (para decisión comercial)

La cliente entregó la **especificación funcional completa** de los formularios. **No es "agregar unos campos"**: es una **reingeniería del motor de cuestionarios** del sistema. Implica cambios estructurales que hoy el sistema no soporta:

| Capacidad nueva requerida | ¿Existe hoy? |
|---|---|
| Tablas dinámicas ilimitadas (hijos, hermanos, empleos, deudas, tatuajes, referencias, bienes…) | ❌ No |
| Campos condicionales (mostrar/ocultar según respuesta) | ⚠️ Muy parcial |
| Tabla académica autogenerada según nivel seleccionado | ❌ No |
| Campos de "uso interno del evaluador" separados de las respuestas del candidato | ❌ No |
| Generación automática de **tablas** desde respuestas hacia el informe final | ❌ No |
| Captura de **fotografía** del candidato + foto de tatuajes | ❌ No |
| Precarga de datos desde la orden con edición + trazabilidad de cambios | ⚠️ Parcial |
| Reutilización de datos entre servicios del mismo candidato | ❌ No (depende de 1B) |
| Catálogo Departamentos/Municipios de Guatemala con dependencia + "Otro (extranjero)" | ❌ No |
| 4 formularios derivados (Pre-empleo, Socioeconómico, Periódica, Específica) con reglas propias | ⚠️ Existen como nombres, pero contenido no coincide |

**⚠️ ACTUALIZACIÓN COMERCIAL (22-jun, decisión final):** Tras revisar los **formularios originales que la cliente entregó al inicio del proyecto (agosto 2025)** — `POLIGRAFO PRESENCIAL.pdf`, `SOCIOECONOMICO QUE LLENAN LOS CANDIDATOS.pdf`, `PERIODICO ESPECIFICO.pdf` — se confirmó que **el contenido completo (preguntas, tablas de familia/empleos/deudas, checklist de drogas, foto, etc.) ya estaba especificado desde el inicio**. El sistema en producción implementó solo una fracción (~70–90 campos). Por lo tanto:

- **Los formularios NO se cobran como desarrollo nuevo.** Eran parte del alcance del proyecto original (Q 22,000).
- **Completarlos = cierre del proyecto** y es lo que desbloquea el **saldo pendiente de Q 10,000** ("al finalizar").
- El documento de 46 páginas (jun-2026) es una **re-formalización/refinamiento** de esos formularios originales, con reglas y estructura más detalladas.
- Lo único genuinamente nuevo (automatizaciones avanzadas: espacios internos del evaluador con paso automático a informe, reutilización entre servicios) se absorbe como parte del cierre o se canaliza vía 1B (ya cotizado).

**Sigue siendo cobrable aparte (no cambia):** Informe **Word editable** (Q 1,600, aprobado), **1B Agregar servicio** (Q 5,200, diferido), **WhatsApp** (Q 3,800, pospuesto).

> Las referencias a "Fase F cobrable aparte / Q 14,500-16,000" en versiones anteriores de este documento quedan **anuladas**. Ver §7 actualizado.

> **Aclaración 5 vs 7 secciones (22-jun):** El número de secciones **NO es un driver de costo ni de arquitectura**. En el código las secciones son una lista de configuración (`Cuestionario::getSeccionesConfig()`); pasar de 5 a 7 es solo reagrupar campos entre pantallas del asistente (horas, no semanas). Los campos, tablas dinámicas, validaciones y reglas son **idénticos** con 5 o 7. El costo real de la Fase F está en el **motor** (tablas dinámicas, campos condicionales, campos internos del evaluador, generación de tablas hacia el informe, foto, catálogos), no en cómo se paginan las secciones. **Recomendación:** mantener las **5 secciones** del documento vigente (ya detallado campo por campo y más cómodo en celular), sin recargo por esa decisión.

**Lo que sí entra rápido (sin esperar esto):** las autorizaciones legales, Infornet, corrección de "Específica" actual, y el informe Word — son independientes de la reingeniería del cuestionario.

---

## 2. Documento 1 — Especificación de formularios (análisis)

### 2.1 Estructura general

- **Formulario base = Pre-empleo.** Sirve para **Polígrafo Pre-empleo** y **VSA Pre-empleo**.
- **Socioeconómico** = Pre-empleo + 1 sección exclusiva ("Información Socioeconómica Complementaria").
- **Periódica** y **Específica** = versiones adaptadas, reutilizando gran parte del base, enfocadas al empleo actual.
- **Pantalla inicial de instrucciones obligatoria** (texto fijo + botón "He leído las instrucciones y deseo continuar"). Sin aceptar, no inicia.

### 2.2 Matriz servicio / formulario / autorización

| Servicio | Formulario | Formulario que ve el candidato | N.º secciones | Autorización |
|---|---|---|---|---|
| Polígrafo | Pre-empleo | Pre-empleo completo | 5 | Poligráfica Pre-empleo |
| Polígrafo | Periódica | Periódico (empleo actual) | 5 | Poligráfica Periódica |
| Polígrafo | Específica | Específico (caso/hecho) | 5 | Poligráfica Específica |
| VSA | Pre-empleo | Pre-empleo completo | 5 | VSA Pre-empleo |
| VSA | Periódica | Periódico | 5 | VSA Periódica |
| VSA | Específica | Específico | 5 | VSA Específica |
| Socioeconómico | Pre-empleo | Pre-empleo + sección socioeconómica | 7→(5+1 secciones visibles) | Socioeconómico Pre-empleo |

> El texto de autorización **cambia** según servicio + tipo de formulario. Coincide con las 7 plantillas legales que ya nos enviaron (Fase A).

### 2.3 Las 5 secciones del formulario matriz (Pre-empleo)

Numeración visible para el candidato:

1. **Información Personal y Familiar** (fusiona datos generales + familia — como pidió la cliente)
2. **Información Académica y Laboral**
3. **Información Económica**
4. **Información de Salud y Hábitos Personales**
5. **Aspectos Complementarios**

#### Sección 1 — Datos generales + Información familiar
- **Datos generales (21 campos):** nombres, apellidos, empresa solicitante (solo lectura), agencia/región (solo lectura), tipo de identificación (DPI/Pasaporte/Doc. extranjero/Otro), No. identificación, nacionalidad (default Guatemala), teléfono, teléfono emergencia, fecha nacimiento (>18 años), **edad (autocalculada)**, **departamento/municipio de nacimiento** (catálogos GT dependientes + "Otro (extranjero)" → texto libre), dirección residencia, **departamento/municipio de residencia** (mismos catálogos), estado civil, correo, IGSS, NIT, licencia de conducir.
  - **Quitar campos actuales:** género, profesión, nivel educativo.
  - Precarga desde la orden, editable por candidato, con **trazabilidad de cambios**.
- **Información familiar:**
  - **Padres:** "¿con quién vive?" (selección múltiple), nombre padre/madre (obligatorio aunque no viva), "¿vive?" → condiciona edad, dirección, ocupación, lugar trabajo, teléfono.
  - **Pareja actual:** tipo de relación → campos condicionales (nombre, edad, teléfono, dirección, ocupación, lugar trabajo, tiempo, calidad relación).
  - **Hijos:** Sí/No → **tabla dinámica** (nombre, edad, vive con usted, ocupación/lugar trabajo, teléfono).
  - **Hermanos:** Sí/No → **tabla dinámica** (nombre, edad, dirección, ocupación/lugar trabajo, teléfono).
  - **Exparejas (subsección condicional):** solo si hubo matrimonio/unión libre o hijos en común. Campos sobre relación, hijos en común, problemas legales, apoyo económico.
  - El sistema debe **generar tablas de resumen familiar** (padres, pareja, hijos, hermanos, expareja) editables por el evaluador para el informe.

#### Sección 2 — Académica y Laboral
- **Académica:** "último nivel académico" → **tabla autogenerada** mostrando ese nivel y los inferiores relevantes (reglas específicas, ver doc). Campos por nivel: estado (completo/incompleto/en curso), carrera/especialidad, institución, año, "¿posee documento de respaldo?" (obligatorio por nivel).
- **Laboral:** "¿posee experiencia previa?" → habilita **tabla dinámica de historial laboral** (empresa, puesto, fechas ingreso/salida, último salario, motivo retiro, jefe inmediato, RRHH, ¿constancia?) + cuadro de observaciones texto libre.
- **Preguntas complementarias laborales (19 preguntas de texto largo)** — preguntas de integridad/honestidad. **Regla clave:** estas respuestas **NO se trasladan automáticamente al informe**; quedan para análisis interno del evaluador. Requiere **espacio interno del evaluador** por sección.

#### Sección 3 — Económica
- **Obligaciones financieras:** "¿posee deudas?" → **tabla dinámica** (entidad, monto, saldo, cuota, motivo, antigüedad, estatus, atraso) + observaciones libre.
- **Situación económica general (≈21 campos):** tipo vivienda, alquiler, dependientes, ingresos adicionales, propiedades, vehículos, pretensión salarial, gastos, fiador, problemas bancarios, demandas por deudas, SAT, etc. (muchos condicionales "describa si Sí").
- **Regla:** situación económica general **no** se traslada auto al informe; campo interno del evaluador.

#### Sección 4 — Salud y Hábitos Personales
- **Estado de salud (18 campos):** preocupaciones, estado salud, atención psicológica/psiquiátrica, situación emocional, ideación de daño, tipo de sangre, peso, estatura, deporte, tratamiento médico, hospitalizaciones, ausencias por enfermedad.
- **Tatuajes y perforaciones:** Sí/No → **tablas dinámicas** (tatuajes: ubicación, tamaño, descripción, tiempo, visibilidad con uniforme, significado; perforaciones: ubicación, visibilidad, fecha).
- **Hábitos personales:** tiempo libre, alcohol (frecuencia, último consumo, excesos, problemas laborales), tabaco, juegos de azar.
- **Sustancias de uso recreativo:** texto introductorio + **checklist de sustancias** (selección múltiple; "Ninguna" excluyente) + preguntas complementarias.
- **Regla:** toda esta sección es **interna**, no se traslada auto al informe; campo interno del evaluador.

#### Sección 5 — Aspectos Complementarios
- **Aspecto judicial (≈12 preguntas de texto libre obligatorias):** antecedentes, detenciones, demandas, armas, hurtos por monto, falsificación, familiares en actividades ilícitas, zona de riesgo.
- **Información complementaria (7 preguntas):** sindicato, familiar en la empresa, cómo se enteró del empleo, metas, cualidades, redes sociales. → **Sí se convierte en tabla en el informe.**
- **Documentos adjuntos:** conservar título "Documentos Adjuntos"; agregar lista sugerida (identificación, antecedentes penales/policiales, constancias estudios, licencia). Máx **10 MB** por archivo. Para Socioeconómico se agrega constancias laborales y recibo de luz.
- **Mensaje "Información Importante"** final (confidencialidad, proceso, papelería pendiente, contacto, resultados). Pre-empleo lleva "papelería pendiente"; Periódica/Específica **no**.
- **Extras Pre-empleo:** captura de **fotografía** (aparece en informe) + cuadro final "Si desea agregar alguna información…".

### 2.4 Formulario Socioeconómico
- Reutiliza las 5 secciones del matriz + **1 sección exclusiva: "Información Socioeconómica Complementaria"** con:
  - **Referencias** familiares (mín. 2), personales (mín. 2), vecinales (mín. 1), laborales (autocompletadas del historial si es posible).
  - **Bienes y pertenencias** (tabla con total autocalculado).
  - **Presupuesto personal** (tabla de gastos con total autocalculado).
  - **Información de vivienda** (tiempo residencia, tipo, propietario, alquiler, habitantes, referencias de ubicación, zona de riesgo, direcciones anteriores).
- Reglas de qué tablas aparecen o no en el informe final (ej.: referencias vecinales y de vivienda **no** van al informe; familiares y personales **sí**).
- Reutilización de datos del matriz: **"cuando ya tengamos la función de agregar servicio a la orden"** → depende de **1B**.

### 2.5 Formulario Periódico
- Base del Pre-empleo, enfocado a empleo actual. 5 secciones.
- Datos generales: **omitir IGSS y NIT**. Familiar: **omitir hermanos**. Aspectos: **omitir Aspectos Complementarios** (solo aspectos judiciales).
- **Sección laboral propia** (tabla simplificada + **26 preguntas** específicas de evaluación periódica).
- Captura de fotografía también.
- Documentos: solo DPI obligatorio (no papelería extra). Mensaje sin "papelería pendiente".

### 2.6 Formulario Específico
- Base de la **Periódica**.
- Académica: solo **último grado** (omitir historial completo).
- Laboral: pregunta 1 obligatoria con **espacio amplio para describir el caso/hecho** (circunstancias, fechas, personas).
- Documentos: solo **DPI obligatorio**.

### 2.7 Conceptos transversales (aplican a todo el motor)
1. **Guardado automático/parcial** para no perder datos.
2. **Responsive real** (muchos llenan desde celular).
3. **Tablas dinámicas** con agregar/eliminar ilimitado, almacenamiento estructurado.
4. **Campos internos del evaluador** por sección, separados de las respuestas del candidato.
5. **Generación automática de tablas** desde respuestas estructuradas hacia el informe (editables por evaluador).
6. **Reutilización** de datos del candidato en evaluaciones futuras (actualizar solo lo cambiado).
7. **Diseño extensible**: agregar campos/secciones/servicios futuros sin rehacer todo.
8. Catálogos **Departamentos/Municipios** de Guatemala dependientes + opción extranjero.

---

## 3. Documento 2 — Informe ejemplo (para el ítem Word)

Ejemplo real: **Polígrafo Pre-empleo, "NO APROBADO"**. Estructura del informe que REPRO entrega a la empresa:

1. **Encabezado de confidencialidad** (texto fijo).
2. **Cuadro de datos del proceso:** proceso, fecha, nombre, puesto, empresa, agencia, DPI, teléfono, lugar/fecha nacimiento, edad, dirección, **resultado**, **observaciones**.
3. **Enfoque y propósito** (texto fijo metodológico).
4. **Datos familiares** (tabla: padres, cónyuge, hijos, hermanos).
5. **Nivel académico** (tabla + validación de constancia).
6. **Información laboral** (tabla de empleos).
7. **Información complementaria y actividades de riesgo** (texto redactado por evaluador).
8. **Aspecto económico** (tabla de deudas + narrativa).
9. **Aspectos de salud** (narrativa).
10. **Hábitos personales** (narrativa).
11. **Vínculo con actividades delictivas y drogas** (narrativa).
12. **Aspectos judiciales** (narrativa).
13. **Información complementaria** (tabla campo/valor).
14. **Recomendaciones** (viñetas redactadas por evaluador).
15. **Resultados de evaluación poligráfica** (tabla de preguntas/respuesta/resultado/puntuación).
16. **Conclusiones** (texto).
17. **Firma del poligrafista** (nombre, registro APA).
18. **Anexo:** fotografía de tatuaje + matriz de riesgo.

> **Confirmado por Otto (22-jun):** el Word es **por candidato** (informe final individual del evaluado), no consolidado por orden.

**Implicaciones para el desarrollo del Word (.docx nativo):**
- Mezcla **tablas autogeneradas** (familia, académico, laboral, deudas, complementaria, resultados) + **narrativa redactada por el evaluador** (texto libre).
- Requiere **secciones internas del evaluador** (recomendaciones, conclusiones, narrativas, resultado poligráfico) → confirma la necesidad de los "campos internos" de §2.7.
- Debe conservar **encabezado de confidencialidad, branding, firma, anexos con imágenes**.
- El .docx debe permitir **edición real** conservando formato (requisito explícito de la cliente).
- **Dependencia importante:** el informe "rico" (con todas las tablas autogeneradas) **depende** de que el motor de formularios nuevo exista. Con el sistema actual, el Word solo puede reproducir lo que hoy hay en el PDF de informe (más limitado).

---

## 4. Brecha vs. sistema actual

| Componente | Hoy | Especificación nueva | Esfuerzo |
|---|---|---|---|
| Almacenamiento respuestas | `cuestionario_respuestas` clave→valor (texto) | Datos estructurados + tablas dinámicas (JSON/tablas hijas) | Alto |
| Secciones | Blade hardcodeado por tipo | 5 secciones matriz + variantes por servicio | Alto |
| Campos condicionales | Casi nulo | Extensivo | Medio-Alto |
| Tablas dinámicas | No existen | ~10 tablas distintas | Alto |
| Campos internos evaluador | No existen | Por sección, separados | Medio-Alto |
| Generación tablas → informe | No | Sí, editable | Alto |
| Foto candidato / tatuajes | No | Sí | Medio |
| Catálogo Deptos/Municipios GT | No | Sí, dependiente | Bajo-Medio |
| Informe Word | PDF resumen actual | .docx nativo con tablas + narrativa | Medio (Alto si depende del motor nuevo) |
| Reutilización entre servicios | No | Sí (depende de 1B) | Alto (1B) |
| 4 formularios diferenciados | Nombres, contenido no coincide | Contenido completo definido | Alto |

---

## 5. Plan de trabajo propuesto (por fases)

### Fase A — Legal (independiente, arranca ya · saldo Q 10,000)
- 7 autorizaciones legales por servicio/formulario + Infornet (Pre-empleo).
- Campo motivo/hecho en la orden (texto libre).
- Corrección del formulario "Específica" actual (igualar a Periódica) — *parche temporal hasta el rediseño*.
- Autorizaciones firmadas en PDF de cuestionario **y** de informe.
- Ajuste temporal Socioeconómico: marcar "Formulario Completado" manual solo para ese servicio (workaround Jotform).

### Fase Word — Informe .docx (extra Q 1,600, aprobado)
- **Opción inmediata:** Word del informe **con los datos que hoy tiene el sistema** (limitado).
- **Opción plena:** Word "rico" con tablas autogeneradas → **depende de completar el motor de formularios** (Fase F). Recomendado entregar versión base ahora y enriquecer cuando el motor esté completo.

### Fase F — Completar el motor de formularios (CIERRE DEL PROYECTO — sin cobro aparte)
> Estos formularios eran alcance original (entregados por la cliente en ago-2025). Completarlos cierra el proyecto y desbloquea el saldo Q 10,000. Se trabaja por etapas para avanzar ordenado y controlado:

- **F1 — Infraestructura del motor:** modelo de datos estructurado, tablas dinámicas reutilizables, campos condicionales, guardado automático, catálogos GT, foto. Pantalla de instrucciones.
- **F2 — Formulario Pre-empleo completo** (5 secciones, todas las reglas) → base Polígrafo/VSA Pre-empleo.
- **F3 — Campos internos del evaluador + generación de tablas hacia informe.**
- **F4 — Socioeconómico** (sección complementaria + referencias + bienes + presupuesto + vivienda).
- **F5 — Periódica** (sección laboral propia, 26 preguntas, omisiones).
- **F6 — Específica** (sobre periódica, caso/hecho).
- **F7 — Word "rico"** integrado con el motor (cierra la Fase Word plena).

### Fase C — Agregar servicio con reutilización (1B, Q 5,200 · 2–3 meses)
- Habilita la reutilización de datos entre servicios que el documento menciona ("cuando ya tengamos agregar servicio").

### WhatsApp API (Q 3,800 · pospuesto)

---

## 6. Orden de dependencias (resumen visual)

```
Fase A (legal) ─── independiente ──► puede entregarse primero
Fase Word base ── independiente ──► entregable pronto (limitado)
Fase F (motor) ──┬─► F1 ─► F2 ─► F3 ─► F4/F5/F6
                 └─► habilita Word "rico" (F7) y mejora informe
Fase C (1B) ─────► habilita reutilización plena en Socioeconómico
```

---

## 7. Impacto comercial (versión final 22-jun)

**Evidencia que cambió la postura:** los formularios originales (ago-2025) ya contenían todo el contenido. La cliente **tiene razón** en que los entregó desde el inicio. Cobrarlos como nuevos no es defendible.

**Estructura comercial definitiva:**

| Concepto | Monto | Naturaleza |
|----------|------:|-----------|
| **Completar formularios (4 tipos) + motor** | **Q 0 extra** | **Cierre del proyecto** → desbloquea saldo Q 10,000 |
| Saldo proyecto base | Q 10,000 | Se cobra al finalizar (incluye formularios + Fase A legal) |
| Informe **Word editable (.docx)** | Q 1,600 | Agregado legítimo (no estaba en original) — **aprobado** |
| **1B** Agregar servicio (reutilización datos) | Q 5,200 | Fase futura — diferida 2–3 meses |
| **WhatsApp API** | Q 3,800 | Fase futura — pospuesta |

- **No se cobra "Fase F" aparte.** Las menciones previas a Q 14,500/16,000 quedan anuladas.
- El **gesto de buena fe** ya está dado: análisis, autorizaciones legales e Infornet incluidos.
- **Alineación de incentivos:** completar bien los formularios = cerrar el proyecto = cobrar el saldo Q 10,000 pactado.

---

## 8. Preguntas / confirmaciones para la cliente

**Sobre alcance y prioridad**
1. ¿Confirma que la prioridad inmediata es **legal (Fase A) + Word base**, y que el **rediseño completo de formularios (Fase F)** se cotiza y planifica aparte?
2. ¿Está de acuerdo en recibir la Fase F **por etapas** (primero Pre-empleo, luego Socioeconómico, luego Periódica/Específica)?

**Sobre el informe Word**
3. El Word "rico" (con tablas autogeneradas familia/laboral/etc.) **depende** del motor nuevo. ¿Acepta una **versión base del Word ahora** (con lo que hoy hay en el informe) y la versión completa cuando exista el motor?
4. ✅ **RESUELTO (22-jun):** el Word es **por candidato** (informe final individual).
5. ¿Nos comparte la **plantilla oficial .docx** de REPRO (con encabezados, logo, pies, estilos) para clonar el formato con fidelidad? El PDF de ejemplo ayuda, pero el .docx fuente es ideal.

**Sobre los formularios (detalles que faltan)**
6. **Catálogo Departamentos/Municipios:** ¿usamos el oficial de Guatemala (22 deptos + municipios) estándar, correcto?
7. **Campos internos del evaluador:** ✅ **RESUELTO (22-jun):** **solo usuarios REPRO** pueden crear/editar campos internos, observaciones, análisis, resultados de evaluación y pruebas. La **empresa NO** los edita: la empresa solo sube archivos e información general del evaluado. Todo lo relativo a evaluaciones/pruebas/informes es exclusivo de REPRO. (Pendiente menor: definir si se versionan/auditan los cambios.)
8. **Fotografía del candidato:** ✅ **RESUELTO (22-jun):** se debe poder tomar **desde cualquier dispositivo** (cámara de celular/web) y también permitir subirla. Falta confirmar si es obligatoria.
9. **Secciones 5 vs 7:** ✅ **RESUELTO (22-jun):** se mantienen **5 secciones** (decisión cliente). Sin impacto en arquitectura ni precio; recomendado mantener así.
9. **Validación de constancias** (aparece "Validado exitosamente" en el informe): ¿es validación manual del evaluador o se espera algún proceso automático?
10. **Tabla académica autogenerada:** confirmar reglas exactas de qué niveles mostrar (el doc da ejemplos; confirmamos la tabla de la pág. correspondiente).
11. **Resultados poligráficos** (tabla preguntas/puntuación del informe): ¿esto lo captura el evaluador en el sistema o se pega manual en el Word?
12. **Anexos con imágenes** (fotos de tatuajes con matriz de riesgo): ¿el evaluador los sube y el sistema los inserta en el informe?

**Operativo**
13. Mientras se desarrolla la Fase F, ¿seguimos con el **formulario actual** para Polígrafo/VSA Pre-empleo, o prefieren esperar el nuevo? (Recomendación: seguir operando con el actual + workaround Socio.)

---

## 9. Recomendación final (22-jun)

1. **Completar los formularios** (motor + 4 tipos) como **cierre del proyecto** — sin cobro aparte. Avanzar por etapas (ver `PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md`).
2. **Arrancar en paralelo** Fase A (legal) + Word — no dependen del rediseño completo.
3. **Cobrar** únicamente: Word Q 1,600 (aprobado) y, a futuro, 1B (Q 5,200) y WhatsApp (Q 3,800).
4. **Pendientes del cliente** (§8): plantilla .docx oficial; auditoría de campos internos por etapa. **Foto obligatoria: decidido sí** (22-jun).
5. **Cobrar el saldo Q 10,000** al finalizar el cierre (formularios + Fase A).

**Mantenimiento:** al avanzar E1–E7, sincronizar con `PROGRESS.md`, `PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` y `CONTEXTO_AGENTES.md`.

---

*Documento técnico interno · Szystems · 22-jun-2026*
