# Informe de Cambios — REPRO Junio 2026 (Fase 18 — Sistema de Estados)

**Actualización desplegada el 10 de junio de 2026**
**Plataforma:** https://reproappv2.szystems.com

---

Este documento describe los cambios implementados según los documentos que nos compartieron (**ESTADOS**, **Listado de estados**), el **Informe de cambios y preguntas Junio 2026**, y el acuerdo posterior sobre **modalidad Presencial/Virtual**. Cada sección explica **qué cambió**, **por qué se realizó**, **cómo funciona ahora** y **cómo comprobarlo**.

---

## Contexto general — ¿Por qué este cambio?

### El problema que teníamos

Antes, cada candidato tenía **un solo estado** que mezclaba cosas distintas: si había llenado el formulario, si ya tenía cita, en qué etapa iba la evaluación técnica, etc. Eso generaba confusión. Por ejemplo, podía aparecer "Formulario Recibido" después de "Programado", cuando en la práctica el formulario ocurre antes.

### La solución acordada

Separamos ese estado único en **4 estados independientes**. Cada uno responde a una sola pregunta:

| Campo | Pregunta que responde | ¿Quién lo ve? |
|-------|----------------------|---------------|
| **Formulario** | ¿El candidato ya llenó el formulario? | REPRO, Cliente y Candidato |
| **Programación** | ¿Ya tenemos cita agendada? | REPRO y Cliente |
| **Evaluación** | ¿En qué etapa técnica va la prueba? | REPRO y Cliente |
| **Orden** | Estado interno general del proceso | Solo en listados ("Mis Órdenes") |

**Ventaja principal:** cada candidato ahora muestra **3 etiquetas claras** (Formulario / Programación / Evaluación) en lugar de una sola etiqueta confusa.

---

## 1. Nuevo sistema de 3 estados visibles por candidato

### ¿Qué cambió?

En el listado de órdenes, en el detalle de cada orden y en los reportes, cada candidato muestra **tres badges independientes**:

- **Estado Formulario** (amarillo/azul/verde según avance)
- **Estado Programación** (contacto, cita, inasistencia, etc.)
- **Estado Evaluación** (etapa técnica: pendiente, en proceso, revisión, informe)

Además, en el detalle de la orden, el equipo REPRO puede **cambiar cada estado por separado** mediante un selector que solo muestra los pasos válidos desde el estado actual.

### ¿Por qué?

Así cada persona del equipo puede ver de un vistazo en qué etapa está cada parte del proceso, sin tener que adivinar qué significa un solo estado mezclado.

### ¿Cómo comprobarlo?

1. Ingresar como administrador o colaborador REPRO.
2. Ir a **Órdenes** y abrir cualquier orden con candidatos.
3. Verificar que cada candidato muestra **3 badges** en el listado.
4. Dentro del detalle, expandir un candidato y confirmar las tres secciones: **Estado Evaluación**, **Estado Formulario** y **Estado Programación**, cada una con su selector de cambio.

---

## 2. Estado Formulario — ¿Llenó el candidato su formulario?

### ¿Qué cambió?

Nuevo conjunto de estados dedicados **solo al formulario en línea**:

| Estado | Cuándo ocurre |
|--------|---------------|
| **Link Pendiente** | Candidato sin correo al crear la orden |
| **Link Enviado** | Automático al crear la orden con email |
| **Pendiente de Llenar** | Automático si pasan 24 horas sin que lo llene |
| **Formulario Completado y Recibido** | Automático al enviar el cuestionario |
| **Vencido** | Automático si pasan 30 días sin completarlo |

### ¿Por qué?

El formulario es un proceso distinto a la evaluación presencial o virtual. Separarlo evita que estados como "Link Enviado" aparezcan en la etapa de evaluación técnica.

### ¿Cómo comprobarlo?

1. Crear una orden nueva con un candidato que tenga **correo electrónico**.
2. Verificar que el badge de Formulario muestra **"Link Enviado"**.
3. Crear otra orden con candidato **sin correo** → debe mostrar **"Link Pendiente"**.
4. Si el candidato completa el cuestionario, el formulario debe pasar a **"Formulario Completado y Recibido"** automáticamente.

> **Nota:** Los cambios automáticos de 24 horas y 30 días requieren que el proceso programado del servidor (cron) esté activo. Si no está configurado aún, esos dos estados se actualizarán al ejecutar el mantenimiento correspondiente.

---

## 3. Estado Programación — ¿Ya hay cita agendada?

### ¿Qué cambió?

Nuevo campo independiente con estos estados:

| Estado | Cómo cambia |
|--------|-------------|
| **Contactando** | Automático al crear la orden |
| **Contactado** | Manual |
| **Programado** | Automático al agendar en el calendario |
| **Proceso Realizado** | Automático cuando Evaluación pasa a "En Revisión" |
| **Reprogramado** | Manual (botón Reprogramar) |
| **Inasistencia** | Manual |
| **Desistió** | Manual — **reactivable** a Contactando |
| **Cancelado** | Manual — estado final |

**Importante:** según su confirmación, el estado **"Asistió"** no se incluyó. El flujo va directo de Programado a Proceso Realizado cuando corresponde.

### ¿Por qué?

La programación de citas, contacto telefónico e inasistencias es un proceso operativo distinto de la evaluación técnica y del formulario.

### ¿Cómo comprobarlo?

1. Crear una orden → Programación debe iniciar en **"Contactando"**.
2. Programar una cita en el calendario → debe cambiar a **"Programado"** automáticamente.
3. Cambiar manualmente Evaluación a **"En Revisión"** → Programación debe pasar sola a **"Proceso Realizado"**.
4. Reprogramar un candidato → debe quedar en **"Reprogramado"** y seguir visible en el historial del día original (comportamiento ya existente del calendario).

---

## 4. Estado Evaluación — Etapa técnica de la prueba

### ¿Qué cambió?

La evaluación física (polígrafo, VSA, socioeconómico, presencial o por videollamada) ahora tiene su propio flujo con **7 estados**:

```
Pendiente de Evaluación → En Proceso → En Revisión → Resultado Preliminar → Informe Final Enviado
```

Estados de excepción:
- **Cancelado** y **Desistió** — solo se pueden marcar desde **"Pendiente de Evaluación"**.
- Una vez que la evaluación entra a **"En Proceso"**, el flujo es **irreversible** (no se puede cancelar ni retroceder, para proteger el historial).

**Cambio importante respecto al sistema anterior:** subir el archivo de informe preliminar **ya no cambia** automáticamente el estado a "En Proceso". Ese paso es **100% manual**, según lo acordado.

Subir el **informe final** sí lleva automáticamente a **"Informe Final Enviado"**.

### ¿Por qué?

La evaluación técnica nunca tiene "link" ni "formulario" — esos conceptos pertenecen al otro campo. Separarlos corrige la confusión que se veía al crear órdenes nuevas.

### ¿Cómo comprobarlo?

1. Crear una orden → Evaluación debe iniciar en **"Pendiente de Evaluación"** (no "Link Enviado").
2. Intentar cambiar a **"En Proceso"** sin formulario completo ni cita → el sistema debe **bloquear** con mensaje de error.
3. Con formulario completo y cita programada, cambiar a **"En Proceso"** → debe permitirlo.
4. Subir informe preliminar → el estado **no debe cambiar solo**; el equipo REPRO lo marca manualmente.
5. Subir informe final → debe pasar a **"Informe Final Enviado"** automáticamente.

---

## 5. Estado de Orden simplificado (4 valores automáticos)

### ¿Qué cambió?

El estado general de la orden se redujo a **4 valores** y se calcula automáticamente:

| Estado | Regla |
|--------|-------|
| **Orden Recibida** | Al crear la orden |
| **En Proceso** | Cuando al menos un candidato avanza de su estado inicial |
| **Entregado** | Cuando todos los candidatos están en Informe Final Enviado, Cancelado o Desistió |
| **Cancelado** | Cuando todos los candidatos están Cancelados o Desistieron |

**Estados eliminados** (ya no existen): Autorización, Requisito, Informe Preliminar como estado de orden, etc.

Este estado solo aparece en **"Mis Órdenes"**, **"Mis Últimas Órdenes"** y el **listado del portal empresa** (columna renombrada a **"Estado de Orden"**).

### ¿Por qué?

Los estados intermedios de la orden generaban confusión. El detalle real del proceso vive ahora en los 3 estados de cada candidato.

### ¿Cómo comprobarlo?

1. Crear una orden → Estado de Orden = **"Orden Recibida"**.
2. Avanzar un candidato en cualquiera de sus 3 estados → la orden debe pasar a **"En Proceso"**.
3. En el portal empresa, verificar que la columna se llama **"Estado de Orden"** y **"Fecha de Registro"** (antes "Estado" y "Fecha Creación").

---

## 6. Selector de Modalidad — Presencial / Virtual

### ¿Qué cambió?

Al crear o editar una orden, el equipo REPRO puede indicar para cada candidato si el proceso será **Presencial** o **Virtual**. La modalidad:

- Se muestra como badge en listados, detalle de orden y reportes.
- Se puede **editar en cualquier momento**.
- Queda registrada en el **historial de cambios**.
- **No aparece** en el portal de empresa (es decisión interna de REPRO).

**Reglas automáticas según modalidad:**

| Modalidad | Al programar cita nueva |
|-----------|----------------------|
| **Presencial** | Se puede programar aunque el formulario NO esté completo (el candidato lo llena en oficina) |
| **Virtual** | El sistema **exige** que el formulario esté "Completado y Recibido" antes de programar |

**Consideración acordada:** si ya había una cita programada como Presencial (sin formulario) y luego cambian a Virtual, **la cita existente se respeta**. La regla de formulario solo aplica a **programaciones nuevas** posteriores al cambio.

En el calendario, al seleccionar un candidato, la modalidad se **precarga automáticamente** en el formulario de programación.

### ¿Por qué?

Ustedes nos confirmaron esta propuesta el 4 de junio para que el sistema aplique la regla correcta sin que el personal tenga que recordarla manualmente.

### ¿Cómo comprobarlo?

1. Crear/editar orden como REPRO → verificar selector **Presencial / Virtual** por candidato.
2. Candidato **Virtual** sin formulario completo → intentar programar en calendario → debe **bloquear** con mensaje.
3. Cambiar a **Presencial** → debe permitir programar sin formulario.
4. En calendario, seleccionar candidato con modalidad ya definida → el campo debe **precargarse**.
5. Ingresar como **empresa** → confirmar que el selector de modalidad **no aparece**.

---

## 7. Reglas de coherencia entre estados (Sinergia)

### ¿Qué cambió?

El sistema ahora valida automáticamente que los estados sean coherentes entre sí:

| Regla | Comportamiento |
|-------|----------------|
| Virtual sin formulario | No permite programar cita nueva |
| Presencial | Permite programar sin formulario completo |
| Iniciar evaluación ("En Proceso") | Requiere formulario completo **y** cita programada |
| Evaluación → "En Revisión" | Programación pasa sola a "Proceso Realizado" |

### ¿Por qué?

Evita errores operativos (por ejemplo, marcar evaluación en proceso sin haber programado la cita).

### ¿Cómo comprobarlo?

1. Candidato virtual, formulario incompleto → programar → **bloqueado**.
2. Candidato con cita pero formulario incompleto → cambiar evaluación a "En Proceso" → **bloqueado**.
3. Candidato con formulario completo y cita → cambiar a "En Proceso" → **permitido**.
4. Cambiar evaluación a "En Revisión" → verificar que Programación quedó en "Proceso Realizado".

---

## 8. Historial de cambios y campo Motivo/Observación

### ¿Qué cambió?

Cada cambio de estado (Formulario, Programación, Evaluación, Orden y Modalidad) queda registrado en un **historial** con:

- Fecha y hora
- Campo que cambió
- Estado anterior → Estado nuevo
- Usuario que realizó el cambio
- Observación (si se ingresó)

Al cambiar un estado manualmente, aparece la opción **"+ Motivo / observación (opcional)"** para dejar una nota (por ejemplo: "Candidato reagendó por viaje", "Inasistencia sin aviso").

### ¿Por qué?

Según su solicitud (#6 del informe de preguntas), necesitaban evidencia para responder al cliente final (ej.: "el candidato fue contactado el 02/06 a las 9:15").

### ¿Cómo comprobarlo?

1. Abrir detalle de orden → expandir un candidato.
2. Cambiar cualquier estado, expandir el campo de observación, escribir un motivo y guardar.
3. Abrir **"Historial de cambios"** debajo de los selectores.
4. Verificar que aparece la entrada con fecha, usuario, estados y la observación ingresada.

---

## 9. Vista simplificada para el candidato

### ¿Qué cambió?

Cuando el candidato abre su enlace después de completar el formulario, ve una pantalla de **"Estado de tu proceso"** con un recorrido visual de 4 pasos:

1. **Formulario** — pendiente o recibido
2. **Evaluación** — cita agendada (con fecha, hora y modalidad si aplica)
3. **Revisión de resultados** — equipo REPRO analizando
4. **Informe final** — enviado a la empresa

El candidato **no ve** los nombres técnicos internos del sistema.

### ¿Por qué?

El candidato solo necesita saber en qué etapa va su proceso, sin la complejidad operativa que usa el equipo REPRO.

### ¿Cómo comprobarlo?

1. Copiar el enlace de un candidato que ya completó el formulario.
2. Abrirlo en el navegador → debe mostrar la pantalla de estado (no el formulario otra vez).
3. Verificar que los pasos se muestran en lenguaje sencillo.
4. Si tiene cita programada, debe mostrar fecha, hora y modalidad.

---

## 10. Notificaciones internas actualizadas

### ¿Qué cambió?

Se ajustó quién recibe cada notificación según la matriz acordada:

| Evento | Administrador | Colaborador | Empresa |
|--------|:---:|:---:|:---:|
| **Nueva orden creada** | ✅ incluye al creador | ✅ incluye al creador | ✅ incluye al creador |
| **Candidato completó cuestionario** | ✅ | ✅ | ✅ **ahora también** |
| **Resultado preliminar subido** | ✅ (excepto quien subió) | ✅ **ahora también** | ✅ |
| **Informe final disponible** | ✅ **ahora también** | ✅ **ahora también** | ✅ + correo |

### ¿Por qué?

Antes algunas notificaciones no llegaban a todos los involucrados. Con el cambio, cada parte queda enterada en el momento correcto.

### ¿Cómo comprobarlo?

1. Crear una orden → verificar que **quien la creó** también recibe la notificación (campana superior).
2. Completar un cuestionario → ingresar como empresa → verificar notificación.
3. Subir resultado preliminar → verificar que un **colaborador distinto** al que subió recibe aviso.
4. Habilitar resultados finales → verificar notificación para admin, colaborador y empresa.

---

## 11. Migración de datos existentes

### ¿Qué cambió?

Los candidatos que ya existían en el sistema con el modelo anterior fueron **reacomodados automáticamente** al nuevo modelo de 4 estados durante el despliegue. Por ejemplo:

- Estados de programación (programado, inasistencia, etc.) se movieron al campo **Programación**.
- Estados de formulario se movieron al campo **Formulario**.
- La evaluación técnica se reinició al vocabulario correcto (**Pendiente de Evaluación**, etc.).

### ¿Por qué?

Ustedes confirmaron (#5) que los datos actuales son de prueba y autorizaron la migración.

### ¿Cómo comprobarlo?

1. Abrir órdenes que existían antes del despliegue.
2. Verificar que cada candidato muestra los **3 badges** con valores coherentes.
3. Si algún candidato se ve inconsistente, reportarlo para ajuste manual puntual.

---

## Preguntas pendientes de su confirmación

Estos puntos quedaron implementados según el acuerdo del 4 de junio, pero conviene que nos confirmen si desean algún ajuste:

| # | Tema | Estado actual | ¿Desean cambio? |
|---|------|---------------|-----------------|
| 1 | Estado **"Llenando"** en formulario | Simplificado a "Pendiente de Llenar" (sin estado intermedio al abrir) | Confirmar si necesitan el estado "Llenando" |
| 2 | Formulario **"Vencido"** al reabrir enlace | Por ahora es estado final; no reactiva automáticamente al abrir el link | Confirmar si deben poder reactivarlo |
| 3 | Vista candidato paso 4 | Se llama **"Informe final"** (no "Papelería validada") | Confirmar nombre preferido |
| 4 | Saltos de estado en Evaluación | Solo el **siguiente paso válido** (no saltar varios pasos) | Confirmar si necesitan saltar adelante manualmente |

---

## Resumen de cambios

| # | Cambio | Estado |
|---|--------|--------|
| 1 | 4 estados independientes (Formulario / Programación / Evaluación / Orden) | ✅ Desplegado |
| 2 | 3 badges por candidato en listados y detalle | ✅ Desplegado |
| 3 | Flujo de Evaluación corregido (7 estados técnicos, sin "link") | ✅ Desplegado |
| 4 | Programación con 8 estados (sin "Asistió") | ✅ Desplegado |
| 5 | Orden simplificada a 4 estados automáticos | ✅ Desplegado |
| 6 | Selector Modalidad Presencial/Virtual con reglas automáticas | ✅ Desplegado |
| 7 | Reglas de sinergia (virtual, formulario, programación) | ✅ Desplegado |
| 8 | Selectores dinámicos + historial + motivo/observación | ✅ Desplegado |
| 9 | Vista candidato simplificada (timeline 4 pasos) | ✅ Desplegado |
| 10 | Notificaciones ampliadas (creador, empresa, colaborador) | ✅ Desplegado |
| 11 | Migración automática de datos existentes | ✅ Desplegado |
| 12 | Columnas empresa: "Estado de Orden" y "Fecha de Registro" | ✅ Desplegado |

---

## Guía rápida de verificación (checklist completo)

Para validar todo el despliegue en un solo recorrido:

- [ ] Crear orden con email → Formulario "Link Enviado", Programación "Contactando", Evaluación "Pendiente de Evaluación"
- [ ] Crear orden sin email → Formulario "Link Pendiente"
- [ ] Asignar modalidad Virtual → bloquea programar sin formulario
- [ ] Asignar modalidad Presencial → permite programar sin formulario
- [ ] Programar cita → Programación "Programado"
- [ ] Cambiar evaluación a "En Revisión" → Programación "Proceso Realizado" automático
- [ ] Registrar motivo en cambio de estado → aparece en historial
- [ ] Completar cuestionario → empresa recibe notificación
- [ ] Abrir enlace candidato post-formulario → vista timeline
- [ ] Portal empresa → columnas renombradas, sin selector modalidad

---

*Actualización desplegada el 10 de junio de 2026 — Fase 18 completa · Plataforma: https://reproappv2.szystems.com*

*Documento de referencia previo al despliegue: `docs/resumen_cambios_cliente.md`*
