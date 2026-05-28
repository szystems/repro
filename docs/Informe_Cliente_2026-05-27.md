# Informe de Cambios — REPRO Mayo 2026 (3ª parte)

**Actualización desplegada el 27 de mayo de 2026**
**Plataforma:** https://reproappv2.szystems.com

---

Este documento cubre los cambios implementados durante la última semana de mayo 2026, incluyendo mejoras al flujo de estados, ajustes en las vistas del portal de empresa y mejoras al módulo de calendario.

---

## 1. Nuevo estado: "Resultado Preliminar"

### ¿Qué cambió?

Se agregó un estado intermedio llamado **"Resultado Preliminar"** en el flujo del candidato, ubicado entre **"En Proceso"** y **"Completado"**. El flujo completo ahora es:

| # | Estado | Cuándo ocurre |
|---|--------|---------------|
| 1 | Pendiente | Al agregar el candidato a la orden |
| 2 | Enlace enviado | Al enviar el cuestionario al candidato |
| 3 | Llenando formulario | Cuando el candidato abre el enlace |
| 4 | Formulario Recibido | Cuando el candidato termina el cuestionario |
| 5 | Programado | Al registrar la cita en el calendario |
| 6 | En proceso | Al subir el archivo de informe preliminar |
| **7** | **Resultado Preliminar** | **Al marcar que el resultado preliminar está listo para revisión** |
| 8 | Completado | Al subir el informe final del candidato |

Este estado permite indicar que ya existe un resultado preliminar disponible antes de que se emita el informe final oficial.

### ¿Cómo comprobarlo?

1. Abrir una orden con un candidato en estado **"En Proceso"**.
2. Verificar que en el selector de estados aparece la opción **"Resultado Preliminar"**.
3. Cambiarlo a ese estado y confirmar que el badge del candidato muestra el color y etiqueta correctos.

---

## 2. El evaluador (poligrafista) ya no aparece en las vistas de la empresa

### ¿Qué cambió?

En el portal de empresa, al ver el detalle de una orden, ya no se muestra el nombre del **evaluador o poligrafista** asignado a cada candidato. Esta información es interna de REPRO y no corresponde compartirla con la empresa cliente.

### ¿Cómo comprobarlo?

1. Ingresar con una cuenta de empresa.
2. Abrir una orden que tenga candidatos con evaluador asignado.
3. Verificar que en los detalles de cada candidato **no aparece** el nombre del evaluador ni el campo "Poligrafista" o "Evaluador".

---

## 3. Las notas de revisión de documentos ahora son visibles para la empresa

### ¿Qué cambió?

Cuando el equipo de REPRO revisa los documentos del candidato y deja una **observación o nota** (por ejemplo, "Documento ilegible, por favor reenviar en mayor resolución"), esa nota ahora aparece visible para la empresa en el portal, debajo del documento correspondiente.

Antes, las notas de revisión solo eran visibles internamente para el equipo de REPRO.

### ¿Cómo comprobarlo?

1. Ingresar como administrador o colaborador de REPRO.
2. Abrir una orden con documentos subidos por el candidato.
3. Al revisar un documento, dejar una nota en el campo de observación y guardar.
4. Ingresar con la cuenta de la empresa correspondiente.
5. Abrir la misma orden y verificar que la nota aparece debajo del documento revisado.

---

## 4. Se eliminó el botón "Informe de Candidatos"

### ¿Qué cambió?

En la vista de detalle de una orden, existía un botón llamado **"Informe de Candidatos"** que generaba un PDF con el listado de candidatos. Este botón fue eliminado porque generaba un documento redundante respecto a los informes individuales ya disponibles, y causaba confusión sobre qué documento descargar.

Para acceder al informe de cada candidato, use los botones de descarga individuales que aparecen en cada evaluado dentro de la orden.

### ¿Cómo comprobarlo?

1. Abrir cualquier orden como administrador o empresa.
2. Verificar que **no aparece** el botón "Informe de Candidatos" en los controles de la orden.
3. Confirmar que los botones de descarga por candidato (informe final, informe preliminar) siguen disponibles normalmente.

---

## 5. Los candidatos ya no desaparecen del calendario al marcar inasistencia o reprogramar

### ¿Qué cambió?

Antes, cuando un candidato era marcado como **"Inasistencia"** o era **reprogramado** para otra fecha, desaparecía del día en que estaba programado originalmente. Esto dificultaba llevar el registro histórico de cada jornada.

Ahora, **cada día del calendario mantiene el registro completo** de lo que ocurrió en esa fecha.

**Candidatos con inasistencia:**
- Siguen apareciendo en su bloque horario del día original.
- Se muestran con un borde rojo y opacidad reducida para diferenciarlos visualmente.

**Candidatos reprogramados:**
- El día original muestra una sección al pie titulada **"Reprogramados desde este día"** con:
  - Nombre completo del candidato.
  - Hora en que estaba originalmente programado.
  - Nueva fecha a la que fue reagendado.
  - Empresa y tipo de evaluación.

### ¿Cómo comprobarlo?

**Para inasistencia:**
1. Marcar a un candidato como **"Inasistencia"** desde el calendario.
2. Volver al mismo día y verificar que sigue apareciendo en su bloque, ahora con borde rojo.

**Para reprogramados:**
1. Reprogramar a un candidato para otra fecha.
2. Volver al día original y verificar que aparece la sección **"Reprogramados desde este día"** al pie de la página.

---

## 6. Corrección: reprogramar un candidato con inasistencia ya no genera error

### ¿Qué cambió?

Al intentar reprogramar desde el calendario un candidato que había sido marcado como **"Inasistencia"**, el sistema mostraba el error **"Debe seleccionar un evaluado."** e impedía continuar.

El error fue corregido. Ahora el sistema identifica automáticamente al candidato al reprogramar directamente desde su bloque en el calendario.

### ¿Cómo comprobarlo?

1. Buscar en el calendario un candidato marcado como **"Inasistencia"** (borde rojo).
2. Hacer clic en **"Reprogramar"**.
3. Ingresar la nueva fecha y confirmar.
4. Verificar que la reprogramación se procesa correctamente y el candidato aparece en la sección histórica del día original.

---

## Resumen de cambios

| # | Cambio | Estado |
|---|--------|--------|
| 1 | Nuevo estado "Resultado Preliminar" entre En Proceso y Completado | ✅ Desplegado |
| 2 | Evaluador no aparece en vistas del portal de empresa | ✅ Desplegado |
| 3 | Notas de revisión de documentos visibles para la empresa | ✅ Desplegado |
| 4 | Eliminado botón redundante "Informe de Candidatos" | ✅ Desplegado |
| 5 | Candidatos con inasistencia o reprogramados permanecen en el calendario | ✅ Desplegado |
| 6 | Corrección: reprogramar candidato con inasistencia ya no genera error | ✅ Desplegado |

---

*Actualización desplegada el 27 de mayo de 2026 — 538 pruebas automatizadas pasando · 0 errores*
