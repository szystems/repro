# Informe de Cambios — REPRO Mayo 2026 (2ª parte)

**Actualización desplegada el 24 de mayo de 2026**
**Plataforma:** https://reproappv2.szystems.com

---

Este documento describe los cambios implementados en respuesta a las observaciones del 22 de mayo de 2026. Cada punto explica qué cambió, cómo se comporta el sistema ahora y cómo comprobarlo.

---

## 1. Estados del candidato — flujo completo y cambios automáticos

### ¿Cómo funciona ahora el flujo de estados?

Cada candidato dentro de una orden avanza por una serie de estados que reflejan en qué etapa del proceso se encuentra. La mayoría de estos cambios ahora ocurren **de forma automática** según las acciones que realiza el equipo de REPRO o el propio candidato.

### Tabla de estados automáticos del candidato

| # | Nombre del estado | Cuándo se asigna automáticamente |
|---|-------------------|----------------------------------|
| 1 | **Pendiente** | Al agregar el candidato a la orden. Estado inicial. |
| 2 | **Enlace enviado** | Al enviar el enlace del cuestionario al candidato por correo. |
| 3 | **Llenando formulario** | Cuando el candidato abre el enlace y comienza a responder. |
| 4 | **Formulario Recibido** | Cuando el candidato termina de responder el cuestionario. *(Antes este estado se llamaba "Documentos pendientes" — nombre cambiado a petición del cliente)* |
| 5 | **Programado** | Al registrar la fecha de evaluación en el calendario. |
| 6 | **En proceso** | Al subir el **archivo** de informe preliminar. *(Nuevo — antes había que cambiarlo a mano)* |
| 7 | **Completado** | Al subir el informe final del candidato. |

### Estados especiales (se asignan manualmente)

| Nombre | Cuándo usarlo |
|--------|---------------|
| **Inasistencia** | El candidato no se presentó a la evaluación. |
| **Reprogramado** | La evaluación fue reagendada para otra fecha. |
| **Desistió** | El candidato se retiró voluntariamente del proceso. Solo disponible en etapas tempranas (hasta "Programado"). |
| **Cancelado** | El candidato fue dado de baja del proceso. |

### ¿Cómo verificarlo?

1. Crear una orden nueva y agregar un candidato con correo. Verificar que el estado aparece como **"Enlace enviado"** de inmediato.
2. El candidato completa el cuestionario. Sin hacer nada más, verificar que el estado cambió a **"Formulario Recibido"**.
3. Asignar una fecha en el calendario. Verificar que el estado cambia a **"Programado"**.
4. Subir el archivo de informe preliminar (botón azul). Verificar que el estado cambia a **"En proceso"** automáticamente.
5. Subir el informe final. Verificar que el candidato pasa a **"Completado"** y la orden a **"Entregado"**.

---

## 2. Reprogramar una evaluación ya no borra al candidato del calendario

**Qué cambió:**
Había un error al reprogramar una evaluación: el sistema redirigía a una pantalla incorrecta y dejaba al candidato sin cita registrada, dando la impresión de que había sido eliminado. Ahora, al reprogramar, el sistema **regresa a la misma pantalla** desde donde se hizo la acción.

**Cómo verificarlo:**
1. Abrir una orden con un candidato programado.
2. Presionar el botón de "Reprogramar" e ingresar la nueva fecha.
3. Verificar que el sistema regresa a la pantalla anterior con la nueva fecha actualizada y el candidato sigue visible.

---

## 3. Botón para descargar PDF en el listado de evaluaciones

**Qué cambió:**
En la sección de reportes de evaluaciones, ahora aparece un **botón de descarga de PDF** (ícono de documento) junto a cada registro. Antes había que abrir el detalle de la orden para acceder al PDF.

**Cómo verificarlo:**
1. Ingresar con una cuenta de administrador.
2. Ir a **Reportes → Evaluaciones**.
3. Verificar que en cada fila del listado aparece un ícono o botón para descargar el PDF directamente.

---

## 4. Nuevo menú "Sedes REPRO" en el portal de empresa

**Qué cambió:**
En el menú lateral del portal de empresa aparece un nuevo ítem llamado **"Sedes REPRO"**, dentro de la sección "Contacto". Al ingresar, la empresa puede ver la información de contacto de cada sede de REPRO: dirección, teléfono, enlace de WhatsApp y botón "Ver en mapa".

**Detalles:**
- Solo se muestran las sedes que estén activas en el sistema.
- Si la sede tiene número de WhatsApp configurado, aparece un botón verde para contactar directamente.
- Si la sede tiene enlace de Google Maps configurado, aparece el botón "Ver en mapa".

**Cómo verificarlo:**
1. Ingresar con una cuenta de empresa.
2. Verificar que en el menú lateral aparece la opción **"Sedes REPRO"** en la sección "Contacto".
3. Hacer clic y verificar que aparecen las tarjetas con la información de cada sede activa.

---

## 5. Notificaciones internas completas según el rol

**Qué cambió:**
Se completó la matriz de notificaciones para que cada usuario reciba los avisos que le corresponden según su rol:

| Evento | Administrador | Colaborador REPRO | Empresa (cliente) |
|--------|:---:|:---:|:---:|
| Nueva orden creada | ✅ ya tenía | ✅ ya tenía | ✅ **nuevo** |
| Candidato asignado a orden | ✅ **nuevo** | ✅ **nuevo** | ✅ **nuevo** |
| Candidato completó el cuestionario | ✅ **nuevo** | ✅ ya tenía | — |
| Informe preliminar subido | ✅ **nuevo** | — | ✅ **nuevo** |
| Informe final disponible | ✅ ya tenía | — | ✅ ya tenía |

**Cómo verificarlo:**
1. Crear una orden nueva. Verificar que la empresa recibe una notificación interna (campana en la barra superior).
2. Agregar un candidato a la orden. Verificar que el administrador y el colaborador reciben la notificación.
3. Subir un informe preliminar. Verificar que el administrador y la empresa reciben la notificación.

---

## 6. Control de acceso por colaborador — quién ve qué módulos

**Qué cambió:**
Se corrigió un fallo de seguridad donde los colaboradores de REPRO podían acceder a módulos sensibles (Sedes, Finanzas, Calendario, Usuarios) aunque no tuvieran permiso. Ahora el acceso a cada módulo se controla de forma individual por usuario.

**Módulos que ahora requieren permiso explícito para colaboradores:**
- Gestión de Sedes
- Finanzas
- Calendario de evaluaciones
- Administración de Usuarios

**Cómo funciona:**
- Los **administradores** ven y acceden a todo sin restricciones.
- Los **colaboradores REPRO** solo ven en el menú los módulos que tengan habilitados. Si un módulo no aparece en el menú, tampoco pueden acceder por URL directa.

**Cómo verificarlo:**
1. Ingresar con una cuenta de **colaborador REPRO** (no administrador).
2. Verificar que el menú lateral solo muestra los módulos asignados a ese usuario.
3. Si no tiene acceso a Finanzas, intentar ir manualmente a la URL `/finanzas` → debe mostrar "Acceso denegado".

---

## 7. Nombre completo del candidato en "Últimas Órdenes" del panel de administrador

**Qué cambió:**
En el panel de inicio del administrador, la sección "Últimas Órdenes" ahora muestra el **nombre completo del candidato principal** de cada orden, facilitando identificar de qué proceso se trata sin tener que abrir cada orden.

**Cómo verificarlo:**
1. Ingresar con una cuenta de administrador.
2. En el panel de inicio, localizar la sección "Últimas Órdenes".
3. Verificar que en cada fila aparece el nombre del candidato además del código de orden.

---

## 8. PDFs mejorados — estados en español y fecha de evaluación

**Qué cambió:**
Dos mejoras en los documentos PDF generados por el sistema:

**PDF "Orden de Servicio":**
- El estado de cada candidato ahora aparece en **español claro** (ej. "Formulario Recibido", "En Proceso") en lugar de los códigos internos del sistema (ej. `docs_pendientes`, `en_proceso`).

**PDF "Informe de Candidatos":**
- Si la evaluación aún no ha ocurrido, el PDF ahora muestra la **fecha programada** con la nota "(programada)" en lugar de mostrar un guión vacío.
- El estado de la orden aparece con su nombre completo en lugar del código interno.

**Cómo verificarlo:**
1. Abrir una orden que tenga candidatos en distintos estados.
2. Generar el PDF "Orden de Servicio" y verificar que los estados aparecen en español.
3. Generar el "Informe de Candidatos" en una orden que aún no tenga fecha de evaluación realizada → verificar que aparece la fecha programada con la nota "(programada)".

---

## 9. Correcciones adicionales

### Editar perfil propio para colaboradores y empresa

**Qué cambió:**
Los usuarios de tipo empresa y colaboradores REPRO no podían editar su propio perfil (nombre, contraseña, etc.) porque el sistema los bloqueaba con "Acceso denegado". Ahora **cada usuario puede editar su propio perfil**, pero no puede editar el perfil de otros usuarios.

**Cómo verificarlo:**
1. Ingresar con una cuenta de empresa o colaborador REPRO.
2. Ir a la sección de edición de perfil.
3. Verificar que puede modificar sus datos y guardar correctamente.
4. Intentar acceder al perfil de otro usuario → debe mostrar "Acceso denegado".

---

### Permisos de colaboradores se guardan correctamente

**Qué cambió:**
Al editar los permisos de un colaborador REPRO y desmarcar todos los módulos, los cambios no se guardaban correctamente y el colaborador seguía viendo todo. Esto fue corregido: ahora los permisos se guardan exactamente como se configuran, incluso si se desmarcan todos.

**Cómo verificarlo:**
1. Ingresar como administrador.
2. Editar un colaborador REPRO y desmarcar todos los permisos de módulos.
3. Guardar y volver a editar el mismo colaborador → verificar que los módulos aparecen desmarcados.
4. Ingresar con la cuenta del colaborador → verificar que no tiene acceso a ningún módulo restringido.

---

## Resumen de cambios

| # | Cambio | Estado |
|---|--------|--------|
| 1 | Estados del candidato automáticos y tabla de flujo actualizada | ✅ Desplegado |
| 2 | Reprogramar ya no borra al candidato del calendario | ✅ Desplegado |
| 3 | Botón PDF en listado de evaluaciones del reporte | ✅ Desplegado |
| 4 | Menú "Sedes REPRO" en portal de empresa | ✅ Desplegado |
| 5 | Notificaciones internas completas por rol | ✅ Desplegado |
| 6 | Control de acceso granular por colaborador | ✅ Desplegado |
| 7 | Nombre completo candidato en Últimas Órdenes | ✅ Desplegado |
| 8 | PDFs con estados en español y fecha de evaluación | ✅ Desplegado |
| 9a | Colaboradores y empresa pueden editar su propio perfil | ✅ Desplegado |
| 9b | Permisos de colaboradores se guardan correctamente | ✅ Desplegado |

---

*Actualización desplegada el 24 de mayo de 2026 — 538 pruebas automatizadas pasando · 0 errores*
