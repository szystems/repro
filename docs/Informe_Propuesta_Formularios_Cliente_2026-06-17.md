# Propuesta de trabajo — Formularios y autorizaciones REPRO

**Fecha:** 17 de junio de 2026  
**Para:** Cliente REPRO  
**De:** Equipo de desarrollo (SZ Systems)  
**Referencia:** Respuesta al *Informe de estado actual de formularios* (16-jun-2026) + documento *Autorización para evaluación*

---

## 1. Objetivo de este documento

Resumimos **lo que entendimos de sus comentarios**, proponemos **soluciones sencillas y rápidas** para implementar, y dejamos claro **qué conviene dejar para después** para no retrasar la entrega.

El enfoque es práctico: **mejorar lo que ya funciona** (enlace del candidato, firma digital, PDF, órdenes) antes de rediseñar por completo la plataforma.

---

## 2. Lo que ustedes nos respondieron (en palabras simples)

Revisamos su documento con anotaciones en rojo y el PDF de autorizaciones. Esto es lo que entendimos:

| Tema | Su respuesta |
|------|--------------|
| **Autorizaciones legales** | Ya nos enviaron las plantillas por tipo de servicio y objetivo (Pre-empleo, Periódica, Específica). Además piden la **autorización Infornet** en procesos **Pre-empleo**. |
| **¿Formularios distintos por servicio?** | La diferencia principal está en las **autorizaciones** (Polígrafo, VSA, Socioeconómico). El detalle está en el documento de autorizaciones. |
| **Formulario “Específica” en línea** | El candidato llena **el mismo formulario que en Periódica**. Cuando conocen el caso concreto, ustedes generan **un documento aparte** acorde a la situación (no va en el formulario web). |
| **Socioeconómico vs Pre-empleo Polígrafo** | **Sí hay diferencias:** datos económicos, referencias laborales y personales, y **visita domiciliar**. |
| **Campos faltantes en Pre-empleo** | **Sí faltan campos.** Van a enviar otro documento con el detalle. |
| **Documentos obligatorios al final** | **Aún no tienen definido** cuáles deben ser obligatorios. |
| **Subir papelería después de enviar** | Preguntaron: *“Si el candidato ya finalizó, ¿cómo puede subir papelería?”* — queda por definir reglas. |
| **Varios servicios al mismo candidato** | Solicitan poder agregar, por ejemplo, Socioeconómico después de Polígrafo **sin volver a pedir toda la información** (expediente único, botón “Agregar servicio”). |

---

## 3. Cómo funciona hoy el sistema (resumen)

Para alinear expectativas:

1. El candidato entra por **enlace único** → verifica **DPI** → firma **autorización genérica** → llena **secciones del cuestionario** → puede adjuntar documentos → envía.
2. Al crear la orden se eligen **tipo de servicio** (Polígrafo / VSA / Socioeconómico) y **tipo de formulario** (Pre-empleo / Periódica / Específica).
3. **Socioeconómico** siempre usa el formulario **Pre-empleo** (5 secciones) en la práctica.
4. La autorización actual es **un texto corto y genérico**, no las **7 plantillas legales completas** que nos enviaron.
5. **No existe** hoy un paso separado para **Infornet**.
6. El formulario **Específica** tiene un error de configuración: muestra **4 secciones** con títulos que no coinciden y **no incluye antecedentes** — esto se puede corregir rápido.
7. Cada evaluado en una orden es **un registro independiente** (hoy, si el mismo candidato necesita otro servicio, se agrega como **otro evaluado** en la misma orden).

---

## 4. Brecha principal: qué falta vs qué pidieron

| Necesidad del cliente | Estado actual | ¿Se puede resolver pronto? |
|----------------------|---------------|----------------------------|
| 7 autorizaciones legales según servicio + objetivo | Texto genérico único | **Sí** — cambio acotado en pantalla de términos y PDF |
| Infornet (Pre-empleo), documento aparte | No existe | **Sí** — segundo paso en el mismo flujo |
| Texto del “hecho” en Específica / motivo en Periódica | No hay campo en la orden | **Sí** — campo que REPRO llena al crear/editar la orden |
| Específica = mismo formulario que Periódica | 4 secciones mal configuradas | **Sí** — corrección directa |
| Campos extra Socioeconómico | Mismo formulario que Pre-empleo | **Parcial** — cuando envíen el listado de campos |
| Campos faltantes Pre-empleo | Pendiente su documento | **Después** — depende de su entrega |
| Subir papelería después de enviar | Hoy **no** se puede | **Sí, opción simple** — ver §6 |
| Un candidato, varios servicios sin repetir datos | Requiere rediseño profundo | **Fase posterior** — ver §7 |

---

## 5. Propuesta rápida — Fase A (prioridad inmediata)

Esta fase **no cambia la arquitectura** del sistema. Usa la estructura actual (orden → evaluado → cuestionario → firma → PDF).

### 5.1 Integrar las autorizaciones legales

**Qué haremos:**

- Sustituir el texto genérico por las **7 plantillas** que nos enviaron, según esta combinación:

| Servicio | Pre-empleo | Periódica | Específica |
|----------|:----------:|:---------:|:----------:|
| Polígrafo | ✅ | ✅ | ✅ |
| VSA | ✅ | ✅ | ✅ |
| Socioeconómico | ✅ | — | — |

*(Socioeconómico solo aplica Pre-empleo, como acordamos.)*

- Completar automáticamente en el texto: **nombre, DPI, empresa solicitante, tipo de servicio, tipo de formulario, fecha y lugar**.
- Mantener el flujo actual: **lectura → checkbox → firma digital en pantalla**.
- Guardar la firma y la fecha, como ya ocurre hoy.
- Incluir la autorización firmada **al final del PDF** del cuestionario/informe (evidencia legal).

**Por qué es rápido:** son cambios en pantallas y plantillas de texto, sin tocar base de datos compleja.

### 5.2 Autorización Infornet (solo Pre-empleo)

**Qué haremos:**

- Después de la autorización principal, mostrar un **segundo documento Infornet** (texto fijo que nos enviaron).
- Aplica a **Polígrafo + Pre-empleo**, **VSA + Pre-empleo** y **Socioeconómico + Pre-empleo**.
- El candidato puede usar la **misma firma** (como indicaron).
- Se guarda y se muestra **por separado** en el PDF (no mezclado con la autorización principal).

**Por qué es rápido:** un paso adicional en el mismo flujo, sin nuevo módulo.

### 5.3 Campo para REPRO: motivo o hecho de la evaluación

Para **Periódica** y **Específica**, las plantillas incluyen textos como “por motivo de ascenso…” o “hecho a investigar…”.

**Propuesta sencilla:**

- Agregar en la orden (visible solo para REPRO) un campo de texto: **“Motivo / hecho de la evaluación”**.
- REPRO lo completa **antes** de que el candidato firme.
- Ese texto aparece insertado en la autorización correspondiente.

**Para Específica:** confirman que el **formulario web no cambia**; el detalle del caso puede ir en este campo y/o en el documento manual que ustedes generan después.

### 5.4 Corregir formulario “Específica” en línea

**Qué haremos:**

- Igualar **Específica** a **Periódica**: **5 secciones**, mismos campos.
- Corregir títulos para que coincidan con el contenido.
- Recuperar la sección de **antecedentes y referencias** que hoy no aparece en Específica.

**Por qué es rápido:** ajuste de configuración de secciones que ya existen.

---

## 6. Propuesta rápida — Fase B (cuando nos envíen los campos)

Depende de los documentos que mencionaron (“estaremos creando el documento con las indicaciones”).

### 6.1 Campos adicionales Pre-empleo

Cuando recibamos su listado, agregamos los campos faltantes en las secciones correspondientes (datos personales, familia, laboral, etc.).

### 6.2 Bloque complementario Socioeconómico

Según su respuesta, lo distinto del socioeconómico sería:

- Datos económicos (ampliados o reordenados)
- Referencias laborales y personales (más estructuradas)
- Información relacionada con **visita domiciliar**

**Propuesta sencilla (sin rediseñar todo):**

- Mantener las **5 secciones base** compartidas.
- Agregar **1 sección extra** solo cuando el servicio sea Socioeconómico (o campos adicionales dentro de las secciones existentes), según el documento que nos envíen.

Así no activamos el diseño antiguo de 7 secciones que nunca se desplegó y evitamos un proyecto grande.

### 6.3 Subir papelería después de enviar el formulario

Hoy, una vez enviado, el enlace **no permite** subir más archivos.

**Opción simple que proponemos:**

- Permitir que el candidato, con el **mismo enlace**, acceda a una pantalla de **“Estado de mi proceso”** (ya existe parcialmente) donde pueda **subir documentos** durante un plazo definido (por ejemplo, mientras el enlace siga vigente o X días después de completar).
- REPRO también puede seguir recibiendo documentos por otros medios y cargarlos desde el panel admin (como hoy).

**Necesitamos que ustedes definan:** ¿cuánto tiempo debe estar abierta esa posibilidad?

### 6.4 Documentos obligatorios vs opcionales

Cuando definan la lista, podemos marcar ciertos tipos de documento como **obligatorios antes de enviar** (solo configuración por tipo de servicio o global). Lo dejamos pendiente hasta su respuesta.

---

## 7. Tema importante: varios servicios al mismo candidato (“Agregar servicio”)

Entendemos el requerimiento del final de su documento:

> Mismo candidato → Polígrafo terminado → agregar Socioeconómico → **no repetir** datos personales, familia, laboral, etc.

**Es un objetivo válido**, pero implica **cambiar la forma en que el sistema guarda la información** (expediente único, servicios múltiples, estados independientes por servicio, reutilización de respuestas). Eso es un **proyecto aparte**, con riesgo de retrasar las autorizaciones y correcciones que necesitan ya.

### Qué proponemos decirle al cliente (honesto y práctico)

| Ahora (sin rediseño) | Después (fase futura) |
|----------------------|------------------------|
| Se puede agregar un **segundo evaluado** en la **misma orden** (mismo DPI, otro tipo de servicio). | Botón **“Agregar servicio”** que reutilice datos ya capturados. |
| El candidato firma la **nueva autorización** y completa el formulario (hoy repetiría secciones). | Solo pide **autorización del nuevo servicio** + **campos complementarios**. |
| Operativamente funciona; no es ideal en tiempo del candidato. | Requiere diseño técnico, migración de datos y pruebas amplias. |

**Recomendación:** avanzar **Fase A** (autorizaciones + Infornet + corrección Específica) y **Fase B** (campos nuevos cuando los envíen). Programar **“Agregar servicio con reutilización de expediente”** como **Fase C** con alcance y cronograma propio, una vez estabilizada la Fase A.

Esto les permite **usar el sistema legalmente correcto pronto**, mientras planificamos la mejora operativa grande.

---

## 8. Resumen visual del flujo propuesto (Fase A)

```
Candidato abre enlace
        ↓
Verificación DPI
        ↓
Autorización legal correcta (1 de 7 plantillas)
        ↓
¿Es Pre-empleo?  →  Sí  →  Autorización Infornet (documento aparte, misma firma)
        ↓              No  →  (continúa)
Secciones del cuestionario (5 secciones; Específica = Periódica corregida)
        ↓
Finalización: documentos opcionales + firma final + envío
        ↓
PDF admin incluye: respuestas + autorización(es) firmada(s) + Infornet si aplica
```

---

## 9. Qué necesitamos que nos respondan

Marquen o respondan por escrito lo siguiente. Con esto podemos iniciar Fase A de inmediato y planificar Fase B.

### Autorizaciones

1. **Periódica:** ¿El “motivo” (ascenso, reubicación, confiabilidad, etc.) lo elige REPRO en un listado, o siempre es texto libre?
2. **Específica:** ¿REPRO debe llenar el “hecho a investigar” **antes** de enviar el enlace al candidato? ¿Es obligatorio para que el candidato pueda firmar?
3. **Infornet:** ¿Confirmamos que **una sola firma** vale para autorización principal + Infornet, o prefieren **dos casillas** de aceptación separadas?
4. **PDF:** ¿Las autorizaciones deben aparecer solo en el PDF del cuestionario, también en el **informe final de la orden**, o en **ambos**?

### Formularios

5. **Socioeconómico — visita domiciliar:** ¿Solo debe quedar en la **autorización legal**, o también quieren **campos en el formulario** (fecha propuesta, dirección de visita, persona contacto, etc.)?
6. **Campos faltantes:** ¿Cuándo estiman enviar el documento con el detalle de Pre-empleo y Socioeconómico?
7. **Papelería post-envío:** ¿El candidato debe poder subir archivos **después** de enviar el formulario? ¿Por cuántos días? ¿Con el mismo enlace?
8. **Documentos obligatorios:** ¿Tienen ya una lista tentativa (DPI, antecedentes, CV, etc.) aunque no sea definitiva?

### Prioridades y alcance

9. ¿Confirman que la **prioridad 1** es: **autorizaciones legales + Infornet + corregir Específica**?
10. ¿Aceptan dejar **“Agregar servicio sin repetir formulario”** para una **segunda entrega**, para no retrasar lo legal?

---

## 10. Plan de trabajo sugerido

| Entrega | Contenido | Depende de |
|---------|-----------|------------|
| **Entrega 1 — Fase A** | 7 autorizaciones + Infornet + campo motivo/hecho + corrección Específica + PDF | Respuestas §9 puntos 1–4 y 9 |
| **Entrega 2 — Fase B** | Campos nuevos Pre-empleo y Socioeconómico + papelería post-envío (si aplica) + docs obligatorios | Documento de campos + respuestas §9 puntos 5–8 |
| **Entrega 3 — Fase C** | Expediente único / Agregar servicio sin duplicar información | Acuerdo de alcance y cronograma aparte |

---

## 11. Mensaje de cierre (para alinear expectativas)

Hemos analizado sus respuestas y el paquete de autorizaciones. **La mayor parte de lo urgente se puede resolver sin rediseñar la plataforma:** integrar sus textos legales, Infornet, corregir Específica y mejorar el PDF.

Lo que **sí requiere más tiempo** es la visión de **un solo expediente con varios servicios** sin repetir datos — lo apoyamos, pero recomendamos **no mezclarlo** con la primera entrega para no retrasar el cumplimiento legal de las autorizaciones.

Quedamos atentos a sus respuestas del §9 y al documento de campos faltantes para continuar.

---

## Anexo A — Respuestas a consultas WhatsApp (17-jun-2026)

### A.1 ¿Informes en Word?

Hoy el sistema maneja **dos cosas distintas** (conviene explicárselo así al cliente):

| Tipo | ¿Cómo se genera? | Formato | Para qué sirve |
|------|------------------|---------|----------------|
| **Informe / resumen de candidatos** (botón PDF en la orden) | **Automático** desde datos del sistema | Solo **PDF** | Resumen administrativo (estados, preliminar escrito en el sistema, observaciones) |
| **Informe final del candidato** (Resultado Final en cada evaluado) | **Archivo que sube REPRO** | **PDF, Word (.doc/.docx)** | Documento oficial entregable al cliente |

**Respuesta corta al cliente:**

> Hoy el **informe final oficial** no lo genera el sistema en Word: ustedes lo preparan (Word o PDF), lo **suben** en la orden por candidato, y el sistema lo guarda, lo muestra a la empresa y permite **descargarlo de nuevo** cuando quieran reimprimir.  
> Si editan el Word fuera del sistema, **vuelven a subir** la versión actualizada (los administradores pueden reemplazar el archivo).  
> El PDF automático de la orden es un **resumen**, no sustituye el informe final que ustedes redactan.  
> **Exportar a Word editable** desde el resumen automático **no está hoy**; se puede cotizar aparte si lo necesitan.

### A.2 Agregar servicio a orden ya creada — cotización orientativa

| Opción | Qué incluye | Tiempo estimado* | Cuándo conviene |
|--------|-------------|------------------|-----------------|
| **Práctica** | Botón “Agregar servicio” en la misma orden; datos básicos prellenados; nuevo evaluado con su autorización y estados propios; el candidato puede repetir formulario si aplica | **2–3 semanas** | Entrega más rápida; operación mejora pero no elimina todo el relleno |
| **Completa** | Reutilizar respuestas ya capturadas; solo autorización del nuevo servicio + campos complementarios (ej. socioeconómico); un expediente visual por candidato | **5–7 semanas** | Cuando confirman alcance y priorizan no molestar al candidato |

\*Estimación de desarrollo y pruebas, **sin incluir** autorizaciones Fase A ni campos nuevos del formulario.

**Mensaje sugerido WhatsApp (Word):**

> Sobre Word: el informe final que entregan al cliente hoy se **sube** ustedes (Word o PDF) en cada candidato — eso ya funciona. El PDF que genera el sistema es un resumen. Si editan el informe en Word, suben la versión nueva y queda actualizado para descarga. Generar Word editable automático desde el sistema lo vemos como mejora futura si lo necesitan.

**Mensaje sugerido WhatsApp (Agregar servicio):**

> Sobre agregar servicio a una orden existente: sí es posible y entendemos que les pasa seguido. Hay una versión **práctica** (~2–3 semanas) con botón en la orden, datos prellenados y flujo del nuevo servicio; y una versión **completa** (~5–7 semanas) que evita repetir todo el formulario al candidato. Les enviamos cotización formal con las dos opciones para que elijan.

---

*Documento preparado por el equipo de desarrollo REPRO — junio 2026.*
