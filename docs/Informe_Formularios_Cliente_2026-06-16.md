# Informe de Formularios REPRO — Estado actual y catálogo de preguntas

**Fecha:** 16 de junio de 2026  
**Plataforma:** https://reproappv2.szystems.com  
**Propósito:** Documentar qué preguntas y datos muestra hoy el sistema en cada combinación de **Tipo de servicio** y **Tipo de formulario**, para que el cliente pueda identificar qué información falta antes de la reunión de revisión.

---

## 1. Resumen ejecutivo

El candidato completa el formulario en línea mediante un **enlace único** (sin usuario ni contraseña). El flujo es:

1. **Verificación de identidad** (DPI de 13 dígitos)
2. **Autorización y términos** (texto según tipo de servicio + firma)
3. **Secciones del cuestionario** (1 a 4 o 5, según tipo de formulario)
4. **Pantalla de finalización** (revisión, carga opcional de documentos, firma digital y envío)

Al crear la orden, REPRO/empresa ya captura datos básicos del evaluado (nombre, DPI, correo, teléfono, puesto, etc.). El formulario en línea **amplía y confirma** esa información.

### Hallazgo importante para la reunión

Hoy el sistema distingue **dos dimensiones**:

| Dimensión | Qué controla en la práctica |
|-----------|----------------------------|
| **Tipo de servicio** (Polígrafo, VSA, Socioeconómico) | Principalmente el **texto de autorización** en términos y condiciones |
| **Tipo de formulario** (Pre-empleo, Periódica, Específica) | El **número de secciones** y los **títulos** mostrados al candidato |

Sin embargo, **el contenido de las preguntas (campos)** proviene hoy de **un mismo conjunto de pantallas** diseñado para Pre-empleo. Las variantes Periódica y Específica **cambian el nombre de las secciones**, pero **no tienen pantallas propias con preguntas distintas** todavía.

Además:

- **Socioeconómico** como servicio usa internamente el formulario **Pre-empleo** (5 secciones), no un formulario extendido de 7 secciones.
- Existe en código un diseño de formulario **Socioeconómico ampliado** (7 secciones), pero **no está activo** en la creación de órdenes ni en las pantallas del candidato.
- La tabla `formulario_campos` (campos dinámicos por configuración) **existe en base de datos pero no se utiliza** en el flujo actual del candidato.

Este informe describe **lo que el candidato ve hoy en producción**, no lo planificado en documentos antiguos de diseño.

---

## 2. Combinaciones posibles al crear una orden

### 2.1 Tipos de servicio (3)

| Valor en sistema | Etiqueta en pantalla |
|------------------|----------------------|
| `poligrafo` | Polígrafo |
| `vsa` | VSA |
| `socioeconomico` | Socioeconómico |

### 2.2 Tipos de formulario (3)

| Valor en sistema | Etiqueta en pantalla |
|------------------|----------------------|
| `preempleo` | Pre-empleo |
| `periodica` | Periódica |
| `especifica` | Específica |

### 2.3 Regla especial: Socioeconómico

Cuando el **tipo de servicio es Socioeconómico**, el sistema **fuerza automáticamente** el tipo de formulario a **Pre-empleo**, aunque en pantalla se pueda seleccionar otra opción al crear la orden.

> **Efecto:** Polígrafo/VSA + cualquier formulario → respeta la selección.  
> **Socioeconómico + cualquier formulario → siempre Pre-empleo (5 secciones).**

### 2.4 Matriz de combinaciones efectivas

| Tipo de servicio | Tipo de formulario seleccionado | Formulario que realmente ve el candidato | N.º secciones | ¿Cambia el texto de autorización? |
|------------------|--------------------------------|----------------------------------------|---------------|-----------------------------------|
| Polígrafo | Pre-empleo | Pre-empleo | 5 | Sí — consentimiento poligráfico |
| Polígrafo | Periódica | Periódica (mismos campos que Pre-empleo) | 5 | Sí — consentimiento poligráfico |
| Polígrafo | Específica | Específica (mismos bloques de campos, ver §6) | 4 | Sí — consentimiento poligráfico |
| VSA | Pre-empleo | Pre-empleo | 5 | Sí — consentimiento VSA |
| VSA | Periódica | Periódica | 5 | Sí — consentimiento VSA |
| VSA | Específica | Específica | 4 | Sí — consentimiento VSA |
| Socioeconómico | (cualquiera) | Pre-empleo | 5 | Sí — texto estudio socioeconómico |

**Total combinaciones útiles para revisar con el cliente: 7** (3 formularios × 2 servicios poligrafía + 1 servicio socioeconómico).

---

## 3. Pasos comunes a todas las combinaciones

Estos pasos **no dependen** del tipo de servicio ni del tipo de formulario (salvo el texto legal).

### 3.1 Verificación de identidad

| Campo | Obligatorio | Notas |
|-------|-------------|-------|
| DPI (13 dígitos) | Sí | Debe coincidir con el registrado en la orden |

### 3.2 Autorización y términos

| Elemento | Detalle |
|----------|---------|
| Texto de autorización | Menciona nombre, DPI, empresa solicitante |
| Tipo de evaluación | Según servicio: Polígrafo / VSA / Estudio socioeconómico |
| Consentimiento adicional | Solo **Polígrafo** y **VSA** (no aplica igual en socioeconómico) |
| Aceptación de términos | Checkbox obligatorio |
| Firma de autorización | Firma digital en pantalla (obligatoria para continuar) |

### 3.3 Finalización (después de todas las secciones)

| Elemento | Detalle |
|----------|---------|
| Resumen por sección | Indica si cada sección fue completada |
| Carga de documentos | **Opcional** — PDF, imágenes, Word (máx. 10 MB) |
| Tipos de documento disponibles | Antecedentes penales, Antecedentes policíacos, CV, Constancia de estudios, Licencia (auto/moto), DPI escaneado, Pasaporte, Carta laboral, Foto de tatuaje, Autorización firmada, Seguimiento REPRO, Otro |
| Confirmación de veracidad | Checkbox obligatorio |
| Firma digital final | Obligatoria para enviar el cuestionario |

### 3.4 Datos ya capturados en la orden (antes del formulario)

Estos datos **no se piden de nuevo** en el formulario público (salvo confirmación/ampliación en sección 1):

- Nombre y apellidos  
- DPI  
- Correo electrónico  
- Teléfono  
- Dirección (si se ingresó al crear la orden)  
- Puesto a evaluar  
- Tipo de servicio y tipo de formulario  
- Sede REPRO / región empresa (si aplica)

---

## 4. Catálogo de preguntas — Formulario base (Pre-empleo)

Este es el contenido **real** de las 5 secciones que ve el candidato cuando el formulario efectivo es **Pre-empleo** (incluye Polígrafo/VSA Pre-empleo y **todo** servicio Socioeconómico).

### Sección 1 — Datos personales

| # | Pregunta / campo | Obligatorio | Tipo |
|---|------------------|-------------|------|
| 1 | Nombres completos | Sí | Texto |
| 2 | Apellidos completos | Sí | Texto |
| 3 | DPI | Sí | Solo lectura (precargado) |
| 4 | Fecha de nacimiento | Sí | Fecha (mayor de 18 años) |
| 5 | Edad | No | Calculada automáticamente |
| 6 | Género | Sí | Masculino / Femenino |
| 7 | Estado civil | Sí | Soltero, Casado, Unión libre, Divorciado, Viudo |
| 8 | Nacionalidad | Sí | Texto (default: Guatemalteca) |
| 9 | Lugar de nacimiento | Sí | Texto |
| 10 | Teléfono personal | Sí | Teléfono |
| 11 | Teléfono alternativo | No | Teléfono |
| 12 | Correo electrónico personal | Sí | Email |
| 13 | Dirección de residencia actual | Sí | Texto largo |
| 14 | Municipio | Sí | Texto |
| 15 | Departamento | Sí | Lista (22 departamentos de Guatemala) |
| 16 | Nivel educativo | Sí | Primaria, Básicos, Diversificado, Universidad, Posgrado (completo/incompleto) |
| 17 | Profesión u oficio | Sí | Texto |

> **Nota:** En validación interna existen campos `titulo_obtenido` e `institucion_educativa`, pero **no aparecen en pantalla** hoy.

---

### Sección 2 — Información familiar

| # | Pregunta / campo | Obligatorio | Tipo |
|---|------------------|-------------|------|
| 1 | Estado civil (detalle) | Sí | Soltero, Casado, Unión libre, Divorciado, Viudo |
| 2 | ¿Tiene hijos? | Sí | Sí / No |
| 3 | Número de hijos | Condicional | Número (si tiene hijos) |
| 4 | Hijos menores de edad | Condicional | Número |
| 5 | Hijos que dependen económicamente | Condicional | Número |
| 6 | ¿Vive con pareja? | Sí | Sí / No |
| 7 | ¿Su pareja trabaja? | Condicional | Sí / No |
| 8 | Número de personas en el hogar | Sí | Número |
| 9 | Número de dependientes económicos | Sí | Número |
| 10 | Tipo de vivienda | Sí | Propia pagada, Propia pagando, Alquilada, Prestada, Casa familiar, Otro |
| 11 | Monto mensual de hipoteca (Q.) | Condicional | Monto |
| 12 | Años restantes de hipoteca | Condicional | Número |
| 13 | Monto mensual de alquiler (Q.) | Condicional | Monto |
| 14 | ¿Cuántas personas contribuyen a los gastos del hogar? | Sí | Número |
| 15 | Observaciones familiares | No | Texto largo |

---

### Sección 3 — Historial laboral

| # | Pregunta / campo | Obligatorio | Tipo |
|---|------------------|-------------|------|
| 1 | Situación laboral actual | Sí | Empleado, Independiente, Empresario, Desempleado, Estudiante, Jubilado |
| 2 | Años de experiencia laboral total | Sí | Número (0–50) |
| 3 | Nombre de la empresa/institución actual | Condicional | Texto (si es empleado) |
| 4 | Puesto/cargo actual | Condicional | Texto (si es empleado) |
| 5 | Fecha de inicio en el empleo actual | Condicional | Fecha |
| 6 | Salario mensual actual (Q.) | Condicional | Monto |
| 7 | Nombre del jefe inmediato | Condicional | Texto |
| 8 | Tipo de negocio/actividad | Condicional | Texto (independiente/empresario) |
| 9 | Ingresos mensuales promedio (Q.) | Condicional | Monto |
| 10 | Detalle de últimos 3 empleos | No | Texto largo (empresa, puesto, fechas, motivo salida, referencia) |
| 11 | Motivo principal de búsqueda de empleo | No | Desempleo, Mejor oportunidad, Cambio de área, Mejores ingresos, etc. |

---

### Sección 4 — Situación económica

| # | Pregunta / campo | Obligatorio | Tipo |
|---|------------------|-------------|------|
| 1 | Ingresos mensuales principales (Q.) | Sí | Monto |
| 2 | Ingresos adicionales (Q.) | No | Monto |
| 3 | Ingresos de otros miembros del hogar (Q.) | No | Monto |
| 4 | Total de ingresos mensuales (Q.) | No | Calculado automáticamente |
| 5 | Gastos de vivienda (Q.) | Sí | Monto |
| 6 | Gastos de alimentación (Q.) | Sí | Monto |
| 7 | Gastos de transporte (Q.) | Sí | Monto |
| 8 | Gastos de educación (Q.) | No | Monto |
| 9 | Gastos de salud (Q.) | No | Monto |
| 10 | Otros gastos (Q.) | No | Monto |
| 11 | Total de gastos mensuales (Q.) | No | Calculado |
| 12 | Balance mensual (Q.) | No | Calculado (ingresos − gastos) |
| 13 | ¿Tiene deudas actuales? | Sí | Sí / No |
| 14 | Detalle de deudas | Condicional | Texto largo |
| 15 | ¿Tiene ahorros? | Sí | Sí / No |
| 16 | Observaciones económicas | No | Texto largo |

---

### Sección 5 — Antecedentes y referencias

| # | Pregunta / campo | Obligatorio | Tipo |
|---|------------------|-------------|------|
| 1 | Referencia personal #1 — Nombre | Sí | Texto |
| 2 | Referencia personal #1 — Teléfono | Sí | Teléfono |
| 3 | Referencia personal #1 — Relación | Sí | Texto |
| 4 | Referencia personal #2 — Nombre | Sí | Texto |
| 5 | Referencia personal #2 — Teléfono | Sí | Teléfono |
| 6 | Referencia personal #2 — Relación | Sí | Texto |
| 7 | ¿Ha tenido problemas legales o antecedentes penales? | Sí | Sí / No |
| 8 | Detalle de antecedentes | Condicional | Texto largo |
| 9 | ¿Ha sido despedido de algún trabajo? | Sí | Sí / No |
| 10 | Motivo del despido | Condicional | Texto largo |
| 11 | ¿Consume bebidas alcohólicas? | Sí | No, Ocasionalmente, Socialmente, Frecuentemente |
| 12 | ¿Ha consumido sustancias controladas o drogas? | Sí | Nunca, En el pasado, Ocasionalmente, Frecuentemente |
| 13 | ¿Ha recibido tratamiento psicológico o psiquiátrico? | Sí | Sí / No |
| 14 | Detalle del tratamiento | Condicional | Texto largo |
| 15 | Información adicional relevante | No | Texto largo |

---

## 5. Variante Periódica (5 secciones)

**Mismas preguntas que Pre-empleo** (§4), pero los **títulos de sección** cambian:

| N.º | Título mostrado al candidato | Contenido real (pantalla usada) |
|-----|------------------------------|----------------------------------|
| 1 | Actualización de datos | Datos personales (§4 Sección 1) |
| 2 | Cambios familiares | Información familiar (§4 Sección 2) |
| 3 | Situación laboral actual | Historial laboral (§4 Sección 3) |
| 4 | Situación económica | Situación económica (§4 Sección 4) |
| 5 | Antecedentes y referencias | Antecedentes y referencias (§4 Sección 5) |

**Diferencia por servicio:** solo el texto de autorización (Polígrafo vs VSA).

---

## 6. Variante Específica (4 secciones)

**Importante:** esta variante tiene **4 secciones** (no incluye una sección separada de antecedentes). Los títulos configurados **no coinciden** con el contenido mostrado:

| N.º | Título mostrado al candidato | Contenido real (pantalla usada) | ¿Coincide título/contenido? |
|-----|------------------------------|----------------------------------|----------------------------|
| 1 | Datos básicos | Datos personales | Parcialmente |
| 2 | Situación específica | Información familiar | **No** |
| 3 | Situación económica | Historial laboral | **No** |
| 4 | Antecedentes relevantes | Situación económica | **No** |

**Consecuencia:** en formulario **Específica**, el candidato **no completa** la sección de antecedentes y referencias (§4 Sección 5) dentro del flujo. Es muy probable que esto explique parte de la percepción de “faltan datos”.

**Diferencia por servicio:** solo el texto de autorización (Polígrafo vs VSA).

---

## 7. Servicio Socioeconómico

| Aspecto | Estado actual |
|---------|---------------|
| Formulario aplicado | **Pre-empleo** (5 secciones, §4) |
| Texto de autorización | “Estudio Socioeconómico” |
| Consentimiento poligráfico/VSA | No aplica |
| Formulario extendido de 7 secciones (diseño en código) | **No activo** — secciones planificadas pero sin pantallas: Situación habitacional, Referencias comunitarias, Verificación de documentos |

Si el cliente espera un estudio socioeconómico **más profundo** que el Pre-empleo estándar, hoy **ese contenido adicional no está desplegado**.

---

## 8. Diferencias por tipo de servicio (solo texto legal)

El **tipo de servicio no agrega ni quita preguntas** del cuestionario. Solo cambia:

| Servicio | En términos y condiciones |
|----------|---------------------------|
| **Polígrafo** | Autorización para evaluación poligráfica + consentimiento adicional (detector de verdad, facultades mentales, etc.) |
| **VSA** | Autorización para evaluación VSA + consentimiento adicional (análisis de estrés de voz) |
| **Socioeconómico** | Autorización para estudio socioeconómico (sin bloque poligráfico/VSA) |

---

## 9. Estado funcional del módulo (junio 2026)

| Funcionalidad | Estado |
|---------------|--------|
| Enlace único por evaluado | ✅ Activo |
| Verificación por DPI | ✅ Activo |
| Términos + firma de autorización | ✅ Activo |
| 5 secciones Pre-empleo completas | ✅ Activo |
| Variante Periódica (títulos distintos) | ✅ Activo (mismos campos) |
| Variante Específica | ⚠️ Parcial — 4 secciones, títulos desalineados, **sin antecedentes** |
| Socioeconómico ampliado (7 secciones) | ❌ No desplegado |
| Campos dinámicos (`formulario_campos`) | ❌ Tabla existe, no se usa en UI |
| Título obtenido / Institución educativa | ❌ Validados en código, no visibles al candidato |
| Carga de documentos al finalizar | ✅ Opcional |
| Mensaje claro enlace inválido/expirado | ✅ Mejorado (jun 2026) |
| Edición admin del cuestionario completado | ✅ Disponible para REPRO |
| PDF del cuestionario | ✅ Disponible en módulo admin |

---

## 10. Hoja de revisión para la reunión con el cliente

Use esta tabla para marcar qué falta según sus formularios en papel o procesos actuales.

### 10.1 Polígrafo + Pre-empleo / VSA + Pre-empleo / Socioeconómico

*(Mismas 5 secciones — §4)*

| Bloque | ¿Completo según cliente? | Datos que faltan (anotar) |
|--------|--------------------------|---------------------------|
| Datos personales | ☐ Sí ☐ No | |
| Información familiar | ☐ Sí ☐ No | |
| Historial laboral | ☐ Sí ☐ No | |
| Situación económica | ☐ Sí ☐ No | |
| Antecedentes y referencias | ☐ Sí ☐ No | |
| Documentos adjuntos | ☐ Sí ☐ No | |
| Términos / autorización | ☐ Sí ☐ No | |

### 10.2 Polígrafo o VSA + Periódica

*(Mismos campos que Pre-empleo — §5)*

| Bloque | ¿Completo según cliente? | Datos que faltan (anotar) |
|--------|--------------------------|---------------------------|
| Actualización de datos | ☐ Sí ☐ No | |
| Cambios familiares | ☐ Sí ☐ No | |
| Situación laboral actual | ☐ Sí ☐ No | |
| Situación económica | ☐ Sí ☐ No | |
| Antecedentes y referencias | ☐ Sí ☐ No | |

### 10.3 Polígrafo o VSA + Específica

*(Revisar con atención — §6)*

| Bloque | ¿Completo según cliente? | Datos que faltan (anotar) |
|--------|--------------------------|---------------------------|
| Datos básicos | ☐ Sí ☐ No | |
| Situación específica *(hoy muestra info familiar)* | ☐ Sí ☐ No | |
| Situación económica *(hoy muestra historial laboral)* | ☐ Sí ☐ No | |
| Antecedentes *(hoy muestra situación económica; no hay pantalla de antecedentes)* | ☐ Sí ☐ No | |

---

## 11. Preguntas sugeridas para la reunión

1. ¿Sus formularios en papel difieren **por servicio** (Polígrafo/VSA/Socio) o solo por **tipo** (Pre-empleo/Periódica/Específica)?
2. Para **Específica**, ¿qué preguntas de “situación específica” esperan que no están hoy?
3. Para **Socioeconómico**, ¿necesitan las secciones extra (vivienda detallada, referencias comunitarias, verificación documental) del diseño extendido?
4. ¿Faltan campos concretos en Pre-empleo? (ej.: redes sociales, propiedades, composición familiar detallada, referencias laborales estructuradas, etc.)
5. ¿Qué documentos deberían ser **obligatorios** además de opcionales al finalizar?

---

## 12. Próximos pasos técnicos (propuesta post-reunión)

Según lo acordado con el cliente, el desarrollo podría priorizarse así:

1. **Corregir formulario Específica** — pantallas y secciones alineadas; incluir antecedentes si aplica.
2. **Definir contenido real** de Periódica y Específica (no solo cambiar títulos).
3. **Activar formulario Socioeconómico extendido** (7 secciones) si se confirma alcance.
4. **Agregar campos faltantes** acordados en Pre-empleo (p. ej. título e institución educativa).
5. **Evaluar uso de `formulario_campos`** para mantenimiento futuro sin cambios de código.

---

*Documento generado a partir del código en producción (junio 2026). Para pruebas en vivo: crear orden de prueba, abrir enlace del evaluado y recorrer cada sección.*
