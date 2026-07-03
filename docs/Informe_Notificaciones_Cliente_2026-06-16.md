# Informe de Notificaciones REPRO — Estado actual

**Fecha:** 16 de junio de 2026  
**Plataforma:** https://reproappv2.szystems.com  
**Propósito:** Explicar de forma sencilla qué notificaciones existen hoy, quién las recibe, por qué canal (campana en el sistema o correo), y qué queda pendiente.

---

## 1. Resumen en pocas palabras

REPRO usa **dos canales principales** de notificación:

| Canal | ¿Quién lo ve? | ¿Para qué se usa hoy? |
|-------|---------------|------------------------|
| **Campana dentro del sistema** (icono 🔔 arriba a la derecha) | Usuarios con login: Admin, Colaboradores REPRO y Empresa | Avisos operativos de órdenes, evaluados, formularios e informes |
| **Correo electrónico** | Evaluados (candidatos) y usuarios según el evento | Enlace del formulario, resultados disponibles, recordatorios, avisos de sede |

**Decisión del cliente (2026):** la mayoría de avisos internos van por la **campana del sistema**, no por correo, para no saturar bandejas de entrada. Los correos se reservan para eventos importantes (enlace al candidato, resultados finales a la empresa, etc.).

**Los evaluados (candidatos) no tienen usuario ni campana.** Solo reciben **correo** cuando se les asigna el formulario (y recordatorios automáticos si están configurados en el servidor).

**WhatsApp automático:** no está integrado. En el sistema solo existen **enlaces manuales** a WhatsApp de las sedes REPRO (barra lateral / contacto).

---

## 2. Cómo funciona la campana (notificaciones in-app)

### Dónde aparece

- Icono de **campana** en la barra superior (Admin, REPRO y Empresa).
- Muestra un **contador rojo** con avisos no leídos.
- Al hacer clic: lista reciente (últimas 20) con mensaje y enlace.
- Enlace **“Ver todas las notificaciones”** abre el **centro de notificaciones** con filtros (leídas/no leídas, fecha, búsqueda).

### Qué incluye cada aviso

- Texto con **número de orden** y **nombre del candidato** (cuando aplica).
- Al hacer clic, lleva directo a la **orden correspondiente** (vista Admin/REPRO o vista Empresa, según quién reciba el aviso).

### Roles que reciben campana

| Rol en sistema | Quién es |
|----------------|----------|
| **Administrador** | Usuario REPRO con permisos completos |
| **Colaborador REPRO** | Usuario interno de REPRO |
| **Empresa** | Usuario del cliente (empresa contratante) |

---

## 3. Matriz completa — Notificaciones in-app (campana)

Esta es la tabla acordada con el cliente, con el **estado real en producción**:

| Evento | ¿Cuándo ocurre? | Administrador | Colaborador REPRO | Empresa (cliente) |
|--------|-----------------|:-------------:|:-----------------:|:-----------------:|
| **1. Nueva orden creada** | Al crear una orden (desde REPRO o desde Empresa) | ✅ Sí (incluye quien la creó) | ✅ Sí (incluye quien la creó) | ✅ Sí (usuarios de esa empresa) |
| **2. Evaluado asignado** | Al crear orden (solo Empresa) o al **agregar** evaluado al editar | ✅ Sí al editar (excepto quien hizo el cambio) | ✅ Sí al editar (excepto quien hizo el cambio) | ✅ Sí (siempre) |
| **3. Candidato completó el formulario** | Al enviar el cuestionario en línea | ✅ Sí | ✅ Sí | ✅ Sí |
| **4. Resultado preliminar subido** | Al guardar texto preliminar **o** subir archivo preliminar | ✅ Sí (excepto quien subió) | ✅ Sí (excepto quien subió) | ✅ Sí |
| **5. Informe final / resultados disponibles** | Al subir informe final, liberar resultados o activar visibilidad para empresa | ✅ Sí | ✅ Sí | ✅ Sí |

### Detalles importantes por evento

**1. Nueva orden creada**  
- Mensaje ejemplo: `Nueva orden #ORD-2026-0123 — Nombre Empresa | Poligrafo, Vsa`  
- Todos los usuarios REPRO activos y todos los usuarios Empresa de esa compañía reciben el aviso.

**2. Evaluado asignado**  
- **Al crear la orden:** la empresa recibe un aviso por cada evaluado (“evaluado asignado”); Admin y Colaborador REPRO reciben solo el aviso de “orden creada” (no duplican por evaluado).  
- **Al editar la orden y agregar evaluados:** Admin, Colaborador REPRO (excepto quien hizo el cambio) y Empresa reciben el aviso de evaluado asignado.  
- Mensaje ejemplo: `Juan Pérez asignado para evaluación — Orden #ORD-2026-0123`

**3. Formulario completado**  
- Mensaje ejemplo: `Juan Pérez completó su cuestionario — Orden #ORD-2026-0123`  
- **No se envía correo** a REPRO ni a la empresa por este evento (solo campana), por decisión del cliente.

**4. Resultado preliminar**  
- Se dispara al **guardar el texto** del informe preliminar en el editor **o** al **subir el archivo** PDF/DOC preliminar.  
- También puede activar la visibilidad de resultados para la empresa si aún no estaba liberada.

**5. Resultados disponibles (informe final)**  
- Se dispara al **subir informe final**, al **liberar resultados** manualmente (solo Admin) o al guardar/subir preliminar que active visibilidad.  
- Además de la campana, la **empresa recibe correo electrónico** (ver sección 4).

---

## 4. Matriz completa — Correos electrónicos

| Evento | Destinatario | ¿Activo? | Asunto / contenido resumido |
|--------|--------------|:--------:|----------------------------|
| **Enlace del formulario al candidato** | Evaluado (email registrado en la orden) | ✅ Sí | “REPRO - Ha sido asignado para evaluación” + enlace único + fecha de expiración |
| **Reenvío manual del enlace** | Evaluado | ✅ Sí | Mismo correo (botón “Correo” en la orden) |
| **Resultados disponibles para la empresa** | Todos los usuarios Empresa de esa compañía | ✅ Sí | “REPRO - Resultados disponibles: Orden …” + resumen de evaluados |
| **Nueva orden en su sede** | Colaboradores REPRO asignados a la sede de la orden | ✅ Sí | “REPRO - Nueva orden asignada a su sede” |
| **Recordatorio de formulario por vencer** | Evaluado con formulario pendiente | ⚙️ Programado* | “Recordatorio: Complete su cuestionario” (3 y 1 día antes de expirar) |
| **Usuario nuevo / reset de contraseña** | Usuario del sistema | ✅ Sí | Credenciales o contraseña temporal (gestión de usuarios) |
| **Formulario completado → correo a REPRO/Empresa** | — | ❌ Desactivado | Removido a petición del cliente (abr 2026) |
| **WhatsApp automático** | — | ❌ No existe | Solo enlaces manuales a sedes |

\* **Recordatorios automáticos:** el sistema tiene el comando configurado para ejecutarse **todos los días a las 8:00 AM** (`notificaciones:recordatorios`). En el servidor iPage debe existir un **cron job** de Laravel (`schedule:run`) para que esto ocurra de verdad. Si el cron no está activo, los recordatorios **no se envían solos** (el reenvío manual desde la orden sí funciona).

---

## 5. Qué recibe cada tipo de usuario (vista rápida)

### Empresa (cliente)

| Recibe campana | Recibe correo |
|----------------|---------------|
| Orden nueva | Resultados disponibles (informe final liberado) |
| Evaluado asignado (al crear o agregar) | — |
| Formulario completado | — |
| Preliminar listo | — |
| Resultados disponibles | — |

### Colaborador REPRO

| Recibe campana | Recibe correo |
|----------------|---------------|
| Orden nueva | Nueva orden en **su sede** (si tiene sede asignada) |
| Evaluado asignado (solo al **editar** y agregar) | — |
| Formulario completado | — |
| Preliminar (si no fue quien subió) | — |
| Resultados disponibles | — |

### Administrador REPRO

| Recibe campana | Recibe correo |
|----------------|---------------|
| Todo lo anterior | Nueva orden en sede (si aplica) |
| Puede liberar/ocultar resultados manualmente (toggle exclusivo Admin) | — |

### Evaluado (candidato, sin login)

| Recibe campana | Recibe correo |
|----------------|---------------|
| No aplica | Enlace al formulario al ser asignado |
| — | Recordatorio automático (si cron activo) |
| — | Reenvío manual desde la orden |

---

## 6. Flujo visual simplificado

```
CREAR ORDEN
    → Campana: Admin + Colaborador + Empresa
    → Correo (opcional): Colaboradores de la sede REPRO asignada

ASIGNAR / AGREGAR EVALUADO
    → Correo al candidato: enlace del formulario
    → Campana (si es edición): Admin + Colaborador + Empresa

CANDIDATO COMPLETA FORMULARIO
    → Campana: Admin + Colaborador + Empresa
    → (Sin correo interno)

SUBIR / GUARDAR PRELIMINAR
    → Campana: Admin + Colaborador + Empresa
    → Puede liberar resultados visibles al cliente

SUBIR INFORME FINAL / LIBERAR RESULTADOS
    → Campana: Admin + Colaborador + Empresa
    → Correo: usuarios Empresa
```

---

## 7. Centro de notificaciones

Además de la campana, existe la página **Centro de notificaciones** (`/notificaciones/centro`) con:

- Historial paginado de todos los avisos.
- Filtros: leídas / no leídas, rango de fechas, búsqueda por texto.
- Acción “marcar como leída” (individual o todas).

Disponible para **Admin, Colaborador REPRO y Empresa** (cualquier usuario autenticado).

---

## 8. Lo que NO está implementado (pendiente / fase futura)

Estos puntos han sido discutidos con el cliente pero **aún no están en producción**:

| Funcionalidad solicitada | Estado |
|--------------------------|--------|
| Notificar al **candidato** cuando recibe/confirma formulario (más allá del correo inicial) | ❌ Pendiente |
| Notificar al candidato: “papelería validada”, “cita programada”, etc. | ❌ Pendiente |
| **WhatsApp API** automático (Meta, plantillas, costos) | ❌ Diferido — cotización aparte |
| Toggle en configuración para apagar notificaciones internas (`notificaciones_activas`) | ❌ Planificado, no activo |
| Correo a REPRO cuando se completa un formulario | ❌ Desactivado a propósito |

**Alternativa actual para contactar candidatos:** correo con enlace del formulario, botón “Reenviar correo” en la orden, y WhatsApp **manual** vía números de sede en el menú lateral.

---

## 9. Cómo probar cada notificación (checklist para el cliente)

| # | Acción de prueba | Resultado esperado |
|---|------------------|-------------------|
| 1 | Crear una orden de prueba | Campana para Admin, Colaborador y Empresa |
| 2 | Agregar evaluado con email | Correo al candidato con enlace |
| 3 | Completar formulario (enlace del evaluado) | Campana “completó su cuestionario” para los tres roles |
| 4 | Subir archivo preliminar | Campana “Resultado preliminar listo” |
| 5 | Subir informe final | Campana + **correo** a Empresa |
| 6 | Clic en aviso de campana | Abre la orden correcta |
| 7 | Ir a Centro de notificaciones | Historial y filtros funcionando |

---

## 10. Preguntas frecuentes

**¿Por qué a veces no llega el correo al candidato?**  
Verificar que el evaluado tenga email en la orden, revisar carpeta de spam, y usar “Reenviar correo” desde la orden. El enlace también se puede copiar con el botón “Enlace”.

**¿La empresa recibe correo por cada formulario completado?**  
No. Solo recibe **campana**. El correo a empresa es principalmente para **resultados disponibles**.

**¿El colaborador que sube el preliminar recibe su propio aviso?**  
No. Se excluye a quien realizó la acción para evitar duplicados.

**¿Hay notificaciones por SMS o WhatsApp?**  
No automáticas. WhatsApp es acceso manual a contactos de sede.

**¿Los recordatorios al candidato funcionan solos?**  
Solo si el servidor tiene activo el **programador de tareas (cron)** de Laravel. Si no, REPRO puede reenviar el enlace manualmente.

---

## 11. Resumen de estado (semáforo)

| Área | Estado |
|------|--------|
| Campana + centro de notificaciones | ✅ Completo y en producción |
| Matriz Admin / Colaborador / Empresa (5 eventos) | ✅ Implementada (Fase 18) |
| Correo enlace al candidato | ✅ Activo |
| Correo resultados a empresa | ✅ Activo |
| Correo nueva orden por sede | ✅ Activo |
| Recordatorios automáticos al candidato | ⚙️ Código listo; depende de cron en servidor |
| Notificaciones al candidato (eventos extra) | ❌ Pendiente fase futura |
| WhatsApp API | ❌ No implementado |

---

*Documento basado en el código desplegado en producción (junio 2026). Para dudas en reunión: traer ejemplo de orden de prueba y verificar campana + bandeja de correo.*
