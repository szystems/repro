# Informe de Cambios — REPRO Mayo 2026

**Actualización desplegada el 18 de mayo de 2026**
**Plataforma:** https://reproappv2.szystems.com

---

Este documento describe los **12 cambios solicitados** en la ronda de observaciones de mayo 2026. Cada punto explica qué cambió, cómo verificarlo y qué esperar.

---

## 1. Los estados de la orden y del candidato cambian solos según lo que pasa

**Qué cambió:**
Antes había que cambiar el estado manualmente en muchos pasos del proceso. Ahora el sistema actualiza los estados de forma automática cuando ocurren ciertas acciones. Hay dos tipos de estados: el **estado general de la orden** y el **estado individual del candidato** dentro de esa orden.

### Cambios automáticos en el estado del candidato (evaluado)

| Acción que ocurre | Estado anterior | Nuevo estado automático |
|---|---|---|
| Se agrega al candidato a la orden **con correo electrónico** | Pendiente | **Enlace enviado** |
| El candidato **termina de responder** el cuestionario | Enlace enviado | **Documentos pendientes** |
| Se sube el **informe final** del candidato | (cualquiera) | **Completado** |

### Cambios automáticos en el estado general de la orden

| Acción que ocurre | Estado anterior | Nuevo estado automático |
|---|---|---|
| Se sube el **informe preliminar** (si la orden aún no llegó a esa etapa) | Solicitud / En proceso / etc. | **Preliminar** |
| Se sube el **informe final** | (cualquiera) | **Entregado** + resultados visibles para la empresa |

> **Nota:** Cuando la orden pasa a "Entregado" automáticamente al subir el informe final, los resultados se hacen visibles para la empresa también de forma automática, sin pasos adicionales.

---

### ¿Se puede poner "Cancelado" o "Desistió" en cualquier momento?

**Estado "Cancelado" (de la orden general):**
Sí, se puede cancelar una orden desde **cualquier etapa del proceso**, excepto cuando ya está en estado **"Entregado"** (una vez entregada al cliente, ya no se puede cancelar). Si una orden fue cancelada por error, el sistema permite reactivarla volviendo al estado "Solicitud".

**Estado "Desistió" (del candidato):**
Este estado indica que el candidato se retiró voluntariamente del proceso. Se puede marcar como "Desistió" en las etapas iniciales: Pendiente, Contactando, Contactado, Enlace enviado, Confirmado, Programado, Inasistencia y Reprogramado.

Sin embargo, **no se puede marcar como "Desistió"** en las etapas avanzadas: En sede, Documentos pendientes, En proceso y Completado. Esto es lógico porque en esos puntos el candidato ya estaba físicamente presente o ya completó el proceso.

**Estado "Cancelado" (del candidato):**
Se puede cancelar al candidato desde casi cualquier estado del proceso, **excepto** si ya está "Completado" o "Desistió" (estados finales que no pueden cambiarse).

---

**Cómo verificar los cambios automáticos:**
1. Crear una orden nueva y agregar un candidato con correo electrónico → verificar que el estado del candidato cambia a "Enlace enviado" de inmediato.
2. Completar el cuestionario desde el enlace enviado → verificar que el estado cambia a "Documentos pendientes".
3. Subir un informe final → verificar que el estado del candidato pasa a "Completado" y el estado de la orden pasa a "Entregado" automáticamente.

---

## 2. Las empresas más activas aparecen en el panel de administrador

**Qué cambió:**
En el panel de inicio del administrador ya aparece un ranking con las **empresas con más órdenes activas**. Este cambio ya estaba implementado desde antes, pero se confirmó y verificó que funciona correctamente.

**Cómo verificarlo:**
1. Ingresar con una cuenta de administrador.
2. En el panel de inicio (dashboard) buscar la sección de "Top Empresas" o similar.
3. Verificar que aparece la lista de empresas con sus respectivos conteos de órdenes.

---

## 3. Configuración del sistema con más opciones

**Qué cambió:**
En la sección de **Configuración** del panel de administrador se agregaron dos campos nuevos:

- **Nombre de la empresa** (`nombre_empresa`): Permite personalizar el nombre que aparece en los PDF generados por el sistema (orden de servicio e informe). Si se deja vacío, aparecerá "REPRO Guatemala" por defecto.
- **Días de vigencia del token** (`dias_vigencia_token`): Controla cuántos días tiene validez el enlace que se envía al candidato para completar su cuestionario. Por defecto son 30 días.

**Cómo verificarlo:**
1. Ingresar con una cuenta de administrador.
2. Ir a **Administración → Configuración**.
3. Verificar que en la pestaña "Identidad" aparece el campo "Nombre de la empresa".
4. Verificar que en la pestaña "Catálogos" aparece el campo "Días de vigencia del token".
5. Ingresar un nombre en "Nombre de la empresa", guardar y luego generar el PDF de una orden para confirmar que aparece el nombre nuevo en el pie de página.

---

## 4. Campo de Sede o Región del candidato en la orden

**Qué cambió:**
Al crear o editar una orden de evaluación, ahora existe un campo llamado **"Sede / Región de la empresa"** donde se puede especificar la sede o región específica del candidato dentro de la empresa contratante. Este campo es opcional.

**Cómo verificarlo:**
1. Ingresar con una cuenta de administrador o empresa.
2. Crear una nueva orden de evaluación.
3. Verificar que aparece el campo "Sede / Región de la empresa" en el formulario.
4. Llenarlo con algún valor (ej. "Región Norte") y guardar.
5. Abrir la orden guardada y confirmar que el valor aparece en el detalle.

---

## 5. El informe se hace visible para la empresa en cuanto se sube

**Qué cambió:**
Antes había que activar manualmente la opción de hacer visible el informe para la empresa. Ahora, **en cuanto el colaborador de REPRO sube el archivo del informe final**, el sistema automáticamente lo marca como visible para la empresa sin pasos adicionales.

**Cómo verificarlo:**
1. Abrir una orden que tenga estado "Completado" o similar.
2. Subir el archivo del informe final como administrador o colaborador de REPRO.
3. Sin hacer nada más, ingresar con la cuenta de la empresa correspondiente.
4. Verificar que el informe ya aparece visible y disponible para descargar en el portal de la empresa.

---

## 6. Botón de WhatsApp en el menú lateral con lista de sedes

**Qué cambió:**
En el menú lateral (sidebar), tanto del panel de administrador como del portal de empresa, aparece un **botón de WhatsApp** que al hacer clic despliega una lista con los números de las sedes de REPRO para contacto directo.

**Importante:** Este botón solo aparece si las sedes de REPRO tienen configurado su número de WhatsApp en el sistema. Actualmente las sedes en producción tienen el campo de WhatsApp vacío, por lo que el botón **no se mostrará hasta que se configuren los números** desde la administración de sedes.

**Cómo activarlo:**
1. Ingresar con una cuenta de administrador.
2. Ir a la sección de administración de Sedes.
3. Editar cada sede y agregar su número de WhatsApp (formato: código de país + número, sin espacios ni guiones, ej. `50212345678`).
4. Al guardar, el botón aparecerá automáticamente en el menú lateral de todos los usuarios.

---

## 7. El portal de la empresa diferencia el informe preliminar del informe final

**Qué cambió:**
En el portal de la empresa, al ver el detalle de una orden, ahora se muestran **dos secciones claramente separadas**:

- Una tarjeta **verde** para el **Informe Final** (el documento oficial entregado al cierre).
- Una tarjeta **azul** para el **Informe Preliminar / Observaciones** (notas intermedias del proceso).

Antes ambos documentos se podían confundir. Ahora el diseño visual los distingue claramente.

**Cómo verificarlo:**
1. Ingresar con una cuenta de empresa.
2. Abrir una orden que tenga informe final subido.
3. Verificar que se ven las dos tarjetas con sus respectivos colores y etiquetas diferenciadas.

---

## 8. Las notificaciones incluyen el número de orden

**Qué cambió:**
Los correos de notificación que envía el sistema ahora incluyen el **código o número de la orden** a la que hacen referencia. Esto facilita identificar de qué candidato o proceso se trata sin necesidad de buscar en el sistema.

Los tipos de notificación afectados son:
- Notificación cuando se asigna un evaluado a una orden.
- Notificación cuando el candidato completa el cuestionario.
- Notificación cuando los resultados están disponibles.

**Cómo verificarlo:**
1. Crear una orden y completar alguno de los pasos que genera una notificación (asignar evaluado, completar cuestionario, etc.).
2. Revisar el correo recibido por el usuario correspondiente.
3. Verificar que el cuerpo del correo menciona el código o número de la orden.

---

## 9. Se eliminó la "Fecha Tentativa" de los formularios

**Qué cambió:**
El campo **"Fecha Tentativa"** (que era una fecha aproximada sujeta a agenda) fue eliminado de los formularios de creación y edición de órdenes, tanto en el panel de administrador como en el portal de empresa. También se eliminó del PDF del informe.

**Cómo verificarlo:**
1. Crear o editar una orden de evaluación.
2. Verificar que no aparece ningún campo llamado "Fecha Tentativa" en el formulario.
3. Generar el PDF de la orden y confirmar que tampoco aparece esa fecha en el documento.

---

## 10. El editor del informe preliminar tiene un nombre más claro

**Qué cambió:**
El campo de texto enriquecido donde el colaborador redacta el informe preliminar ahora está etiquetado como **"Informe Preliminar / Observaciones"** en lugar del nombre anterior que podía prestarse a confusión.

**Cómo verificarlo:**
1. Ingresar con una cuenta de administrador o colaborador de REPRO.
2. Abrir una orden y buscar el editor de texto del informe.
3. Verificar que la etiqueta del campo dice "Informe Preliminar / Observaciones".

---

## 11. Permisos del sistema ajustados por tipo de usuario

**Qué cambió:**
Se revisaron y corrigieron las restricciones de acceso de la plataforma para asegurar que cada tipo de usuario solo pueda ver y hacer lo que le corresponde:

- Los **colaboradores de REPRO** ya no pueden ver la sección de Usuarios ni la de Configuración (esas son exclusivas de administradores).
- Las rutas de administración de usuarios están protegidas correctamente y no pueden ser accedidas por perfiles no autorizados.
- Las páginas de error (403 acceso denegado, 404 no encontrado, 500 error de servidor) ahora tienen un diseño propio de la plataforma en lugar de las páginas genéricas del servidor.

**Cómo verificarlo:**
1. Ingresar con una cuenta de **colaborador REPRO** (no administrador).
2. Verificar que en el menú lateral **no aparece** la opción "Usuarios" ni "Configuración".
3. Intentar acceder manualmente a `/users` o `/config` → debe redirigir o mostrar acceso denegado.
4. Acceder a una URL inexistente como `/algo-que-no-existe` → debe mostrar la página de error 404 con diseño de REPRO.

---

## 12. Corrección del desplazamiento en el portal de empresa

**Qué cambió:**
En el portal de empresa había un problema visual donde aparecían **dos barras de desplazamiento** al mismo tiempo o el contenido no se podía hacer scroll correctamente en algunas pantallas. Esto fue corregido: ahora el contenido se desplaza normalmente con una sola barra de desplazamiento.

**Cómo verificarlo:**
1. Ingresar con una cuenta de empresa.
2. Navegar por las diferentes secciones del portal (mis órdenes, detalle de una orden, etc.).
3. Verificar que el desplazamiento es fluido y que solo hay una barra de scroll visible a la vez.

---

## Notas adicionales

- **Barra de navegación fija:** La barra superior (navbar) del panel de administrador ahora permanece visible al hacer scroll hacia abajo, facilitando la navegación sin tener que subir hasta arriba.
- **Administrador puede reemplazar el informe final:** Los administradores del sistema pueden eliminar y volver a subir el informe final de una orden incluso después de que fue entregada, en caso de necesitar corregirlo.

---

*Actualización desplegada el 18 de mayo de 2026 — 490 pruebas automatizadas pasando · 0 errores*
