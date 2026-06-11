# REPRO — Resumen de Cambios en el Sistema de Estados

**Preparado para:** Equipo REPRO
**Fecha:** 04/06/2026
**Asunto:** Confirmación del nuevo sistema de estados (Formulario, Programación, Evaluación y Orden)

---

## 1. ¿Qué vamos a cambiar y por qué?

Hoy el sistema maneja **un solo estado por candidato** que mezcla cosas muy distintas (si llenó el formulario, si ya tiene cita, en qué punto va la evaluación). Eso genera confusión: por ejemplo, "Formulario Recibido" aparecía después de "Programado", cuando en su proceso real ocurre antes.

Según los documentos que nos enviaron (**Listado de estados** y **ESTADOS**), vamos a separar ese estado único en **4 estados independientes**, para que cada uno responda a una sola pregunta clara:

| Estado | Responde a la pregunta | ¿Quién lo ve? |
|--------|------------------------|----------------|
| **1. Formulario** | ¿El candidato ya llenó el formulario? | REPRO, Cliente y Candidato |
| **2. Programación** | ¿Ya tenemos cita agendada? | REPRO y Cliente |
| **3. Evaluación** | ¿En qué etapa técnica va la evaluación? | REPRO y Cliente |
| **4. Orden** | Estado interno general del proceso | **Oculto** (solo en "Mis Órdenes") |

La gran ventaja: cada candidato mostrará **3 etiquetas claras** (Formulario / Programación / Evaluación) en lugar de una sola etiqueta confusa.

---

## 2. Cómo funcionará cada estado

### 🟦 Estado 1 — Formulario (automático)

Responde: **¿El candidato ya llenó su formulario?**

| Estado | Cuándo ocurre |
|--------|---------------|
| Link de formulario enviado | Automático, al crear la orden |
| Pendiente de llenar | Automático, si pasan **24 horas** sin que lo llene |
| Llenando | Automático, cuando el candidato abre el formulario |
| Formulario completado y recibido | Automático, cuando termina y envía |
| Vencido | Automático, si pasan **30 días** sin completarlo |

> Si un formulario "Vencido" se vuelve a abrir con el enlace, regresa a "Llenando" o "Completado".

### 🟩 Estado 2 — Programación (¿ya hay cita?)

| Estado | Cómo cambia |
|--------|-------------|
| Contactando | Automático, al crear la orden |
| Contactado | Manual |
| Programado | Automático, al agendar la cita en el calendario |
| Asistió | Manual |
| Proceso realizado | Automático, cuando la Evaluación pasa a "En revisión" |
| Reprogramado | Manual (botón Reprogramar) |
| Inasistencia / Desistió / Cancelado | Manual |

### 🟨 Estado 3 — Evaluación (etapa técnica)

El avance sigue **este orden estricto**, y el sistema solo mostrará los siguientes pasos válidos:

```
Pendiente de evaluación → En proceso → En revisión → Resultado Preliminar → Informe final enviado
```

- **Cancelado** y **Desistió** solo pueden marcarse mientras está en **"Pendiente de evaluación"**.
- Una vez que la evaluación entra a "En proceso", **ya no puede retroceder ni cancelarse** (para proteger el historial).

### ⚪ Estado 4 — Orden (oculto y automático)

Tal como lo solicitaron (Opción A), el estado de la orden **ya no se muestra ni se toca manualmente**. El sistema lo calcula solo:

| Estado | Regla automática |
|--------|------------------|
| Orden recibida | Al crear la orden |
| En proceso | Cuando al menos un candidato avanza de su estado inicial |
| Entregado | Cuando **todos** los candidatos están en Informe final enviado, Cancelado o Desistió |
| Cancelado | Cuando **todos** los candidatos están en Cancelado o Desistió |

> Seguirá apareciendo únicamente en **"Mis Órdenes" / "Mis Últimas Órdenes"** y en el **Listado de Empresas**.

**Limpieza de estados de Orden que nos pidieron:** según su tabla, los estados internos de la orden quedan simplificados así:

| Estado anterior | Acción solicitada |
|-----------------|-------------------|
| Solicitud | Se mantiene |
| Autorización | ❌ **Se elimina** |
| Requisito | ❌ **Se elimina** |
| Programación | ✏️ Se renombra a **"En programación"** |
| Realización de la Prueba | ✏️ Se renombra a **"En proceso"** |
| Informe Preliminar | ❌ **Se quita** (genera confusión) |
| Informe Final | Se mantiene |
| Entregado | ✏️ Se renombra a **"Completado"** |
| Cancelado | Se mantiene |

---

## 3. La lógica de avance (Máquina de Estados)

Tal como acordamos, la pantalla **solo mostrará los siguientes pasos posibles** según el estado actual; no todos los estados a la vez. Esto evita errores (como marcar "Completado" a alguien que nunca asistió) y mantiene un orden lógico.

**Reglas de coherencia entre estados (sinergia):**

1. **Proceso virtual:** el formulario debe estar "Completado y recibido" **antes** de poder programar la cita.
2. **Proceso presencial:** se puede programar aunque el formulario aún no esté completo (se llena en oficina).
3. La evaluación **no puede iniciar** ("En proceso") si el formulario no está "Completado y recibido".
4. Un candidato **no puede pasar a "En proceso"** si antes no estuvo "Programado".
5. Cuando la Evaluación pasa a "En revisión", la Programación cambia **sola** a "Proceso realizado".

---

## 4. Cambios visibles en las pantallas

- **Listado de Órdenes y de Evaluados:** mostrarán las 3 etiquetas separadas (Formulario / Programación / Evaluación), cada una con su fecha y hora.
- **Historial completo:** cada cambio de estado guardará **fecha, hora, estado anterior y estado nuevo**. Esto les permitirá responder al cliente con evidencia (ej.: "el candidato fue contactado el 02/06 a las 9:15").
- **Campo de Motivo / Observación:** cuando un estado cambie por una causa externa (papelería incompleta, problemas de salud, solicitud del cliente, etc.), se podrá registrar una nota. Es de uso interno y, si lo desean, visible para el cliente.
- **Vista del candidato:** verá solo lo esencial: *Formulario pendiente · Formulario recibido · Evaluación programada · Papelería validada y aceptada*.
- **Listado de Empresas:** la columna "Estado" pasará a llamarse "Estado de Orden" y "Registro" a "Fecha de registro".

---

## 5. Cambios en las notificaciones

Según la tabla que nos compartieron, ajustaremos quién recibe cada notificación interna (in-app). Estos son los cambios solicitados:

| Evento | Administrador | Colaborador | Empresa (cliente) |
|--------|---------------|-------------|-------------------|
| **Nueva orden creada** | Sí — **incluyendo a quien la creó** | Sí — **incluyendo a quien la creó** | Sí — **incluyendo a quien la creó** |
| **Candidato completó el cuestionario** | Sí | Sí | ✅ **Ahora también la empresa** |
| **Resultado preliminar subido** | Sí (excepto quien lo subió) | ✅ **Ahora también el colaborador** | Sí |
| **Informe final disponible** | ✅ **Ahora también el admin** | ✅ **Ahora también el colaborador** | Sí + **Correo electrónico** |

> Hoy algunas de estas notificaciones no llegaban a todos. Con el cambio, cada parte involucrada quedará enterada en el momento correcto.

---

## 6. Comportamiento en el Calendario

Mantenemos el comportamiento actual del calendario, que es coherente con el nuevo modelo:

| Estado del candidato | ¿Aparece en el calendario? |
|----------------------|----------------------------|
| Programado | ✅ Sí, en su bloque horario |
| Inasistencia | ✅ Sí, con borde rojo |
| Reprogramado | ✅ Sí, como historial en el día original |
| Completado / Cancelado / Desistió | ❌ No (ya no está activo) |
| Sin cita aún (formulario/contacto) | ❌ No (todavía no tiene fecha) |

> En su documento marcaron algunas **dudas (❓)** sobre estos casos. Lo incluimos como pregunta abajo para confirmar el comportamiento que desean.

---

## 7. Preguntas para confirmar antes de empezar

Necesitamos su confirmación en estos puntos para evitar retrabajos:

1. **Modelo definitivo (importante):** La captura que nos compartieron muestra un flujo **en una sola línea** (`Solicitud → Link Enviado → Formulario Recibido → ...`), pero los documentos PDF describen **4 estados separados**. Entendemos que **lo definitivo son los 4 estados independientes de los PDF** y que la captura era solo una referencia anterior. **¿Es correcto?**

2. **"En Proceso" — ¿manual o automático?** En su documento indican que "En Proceso" debe ser **manual** (la evaluación se está efectuando y alguien de REPRO lo marca). Hoy el sistema lo pone automático al subir el informe preliminar. **¿Confirmamos que lo dejamos 100% manual?**

3. **Estado "Asistió":** ¿quién y en qué momento lo marca? ¿Lo registra el personal de REPRO cuando el candidato llega a la oficina?

4. **Calendario (dudas marcadas):** En su tabla del calendario marcaron varias ❓. ¿Desean que los candidatos **Completados/Cancelados/Desistió** sigan sin aparecer en el calendario activo (como ahora), o prefieren verlos en alguna sección de historial?

5. **Datos actuales:** Hoy ya existen candidatos en producción con estados del modelo anterior (programado, contactando, etc.). Al hacer el cambio, los reacomodaremos automáticamente al nuevo modelo. ¿Nos confirman que podemos hacer esta migración de los datos existentes?

6. **Campo Motivo/Observación:** ¿debe ser **obligatorio** al cancelar o marcar inasistencia/desistió, o siempre **opcional**?

7. **Proceso Virtual vs. Presencial:** El sistema necesita saber si una orden es virtual o presencial para aplicar la regla del formulario. ¿Esa información ya la definen al crear la orden, o debemos agregar un campo nuevo para indicarlo?

8. **"Desistió" reactivable:** En el documento anterior mencionaron que un candidato que desiste a veces pide volver al proceso. ¿Quieren que "Desistió" se pueda reactivar (igual que "Cancelado"), o que siga siendo un estado final?

9. **Notificación al crear orden:** Confirmamos que la notificación de "Nueva orden creada" ahora **sí debe llegar también a la persona que la creó** (antes se excluía). ¿Correcto?

---

> Una vez confirmados estos puntos, iniciamos el desarrollo. Calculamos el trabajo en **4 semanas** divididas en: base de datos, lógica de estados, automatizaciones (24h/30 días) y rediseño de las pantallas, más las pruebas.
