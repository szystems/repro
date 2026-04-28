# Informe de actualizaciones — Abril 2026

**Aplicación:** REPRO — Portal de Evaluaciones
**URL:** https://reproappv2.szystems.com
**Fecha:** 23 de abril de 2026

Estimado cliente:

A continuación le detallamos, en lenguaje sencillo, los cambios que aplicamos a su portal tras las observaciones que nos compartió, y cómo puede verificarlos usted mismo.

---

## Parte 1 — Observaciones que usted reportó

### 1. Ya no aparece el error "Acceso no autorizado" al crear una orden

**Qué pasaba antes:** Al guardar una orden nueva, el sistema le mostraba un mensaje de error y no podía ver la orden recién creada.

**Qué se corrigió:** Ahora al crear la orden, lo lleva directamente a la vista de la orden, sin ningún error.

**Cómo comprobarlo:**
1. Inicie sesión con su cuenta.
2. Vaya a **Órdenes de Evaluación → Nueva Orden**.
3. Llene los datos y presione **Guardar**.
4. Debe abrirle la pantalla con el detalle de la orden. ✅

---

### 2. El botón PDF de la orden ahora sí descarga el informe

**Qué pasaba antes:** El botón del PDF abría una pestaña en blanco.

**Qué se corrigió:** El botón ahora genera y abre correctamente el PDF con los datos de la orden y sus evaluados.

**Cómo comprobarlo:**
1. En **Mis Órdenes**, busque cualquier orden en la lista.
2. Haga clic en el ícono rojo del PDF.
3. Se abrirá el documento con el resumen completo. ✅

---

### 3. El Reporte de Evaluaciones ya muestra sus datos

**Qué pasaba antes:** El reporte aparecía vacío aunque ya tuviera evaluados registrados.

**Qué se corrigió:** El reporte ahora lista correctamente todos los evaluados de sus órdenes, con filtros por fecha, estado y tipo de servicio.

**Cómo comprobarlo:**
1. Vaya al menú **Informes y Estadísticas → Reporte de Evaluaciones**.
2. Debe aparecer la tabla con sus evaluados y un resumen en la parte superior. ✅

---

### 4. Ahora puede elegir la Sede al crear una orden

**Qué pasaba antes:** No había cómo indicar a qué sede de REPRO pertenecía la orden.

**Qué se corrigió:** Se agregó un selector **Sede** en el formulario de nueva orden y edición.

**Cómo comprobarlo:**
1. Cree una nueva orden.
2. Busque el campo **Sede** — debe mostrarle las sedes disponibles (Guatemala, Quetzaltenango, etc.).
3. Elija una y guarde. La sede aparece después en el listado. ✅

---

### 5. Se redujo la cantidad de correos automáticos

**Qué pasaba antes:** Recibía muchos correos por cada acción pequeña del sistema.

**Qué se corrigió:** Ajustamos las notificaciones para que solo se envíen correos en eventos importantes (orden creada, cuestionario completado, resultados disponibles). Los eventos menores ya no generan correo.

**Cómo comprobarlo:** Con el uso normal notará menos correos. Si echa de menos alguno específico, avísenos y lo reactivamos.

---

### 6. Al crear una orden ahora aparece mensaje de éxito y puede subir papelería

**Qué pasaba antes:** Después de crear la orden no había confirmación ni forma clara de adjuntar documentos.

**Qué se corrigió:** Al guardar:
- Aparece un mensaje verde de "Orden creada con éxito".
- En la vista de la orden aparece un botón para **subir documentos** (PDF, imágenes, Word) por cada evaluado.

**Cómo comprobarlo:**
1. Cree una orden con al menos un evaluado.
2. Ya en la vista de la orden, en cada evaluado verá un botón **Subir documento**.
3. Puede adjuntar papelería directamente. ✅

---

### 7. Se muestra el nombre del candidato en el listado de órdenes

**Qué pasaba antes:** El listado solo mostraba el código de la orden.

**Qué se corrigió:** Ahora debajo del código aparece el nombre del primer evaluado (candidato), para que pueda identificarlas fácilmente.

**Cómo comprobarlo:**
1. Vaya a **Órdenes de Evaluación**.
2. En la columna **Código**, bajo el número de orden, verá el nombre del candidato. ✅

---

### 8. El botón "Agregar Evaluado" se reposicionó

**Qué pasaba antes:** El botón quedaba en un lugar poco visible.

**Qué se corrigió:** Ahora **siempre aparece debajo del listado de evaluados**, centrado, tanto al crear como al editar órdenes.

**Cómo comprobarlo:**
1. Cree o edite una orden.
2. En la sección **Evaluados**, agregue un evaluado.
3. El botón **Agregar Evaluado** seguirá siempre visible debajo, listo para el siguiente. ✅

---

### 9. Descarga del informe PDF directamente desde "Mis Reportes"

**Qué pasaba antes:** En **Mis Reportes** se veía el listado de evaluados pero no había forma directa de descargar el informe individual de cada uno; el cliente no sabía dónde encontrarlo.

**Qué se corrigió:** Se agregó una columna **Informe** al final de la tabla:

- Si REPRO ya marcó los resultados como disponibles para usted, aparece un **botón rojo PDF** que descarga el informe del evaluado.
- Si todavía está en evaluación, aparece la etiqueta **"En proceso"** indicando que el informe aún no fue habilitado.

**Importante:** El informe PDF de cada evaluado solo se habilita cuando un trabajador de REPRO:
1. Cambia el estado de la orden a **Entregado**, y
2. Activa la opción **"Resultados visibles para la empresa"**.

Mientras esto no ocurra, verá la etiqueta "En proceso" en lugar del botón.

**Cómo comprobarlo:**
1. Vaya a **Mis Reportes**.
2. En la última columna **Informe**, las órdenes ya entregadas muestran el botón rojo PDF.
3. Haga clic para descargar el informe individual del evaluado.

---

## Parte 2 — Mejoras adicionales (internas, de seguridad y rendimiento)

Aprovechando la actualización, aplicamos mejoras preventivas que usted **no ve directamente**, pero que protegen su información y hacen la app más estable:

| Mejora | Qué significa para usted |
|---|---|
| **Cifrado de datos sensibles** | Las observaciones y notas internas quedan guardadas en la base de datos de forma cifrada. Si alguien no autorizado accediera, no podría leerlas. |
| **Registro de cambios de estado (auditoría)** | Cada vez que una orden o evaluado cambia de estado queda guardado quién lo cambió, cuándo y desde qué equipo. Sirve para rastrear cualquier cambio. |
| **Validación de archivos subidos** | Solo se aceptan PDF, Word e imágenes. Archivos sospechosos son rechazados automáticamente. |
| **Evita evaluados duplicados** | No podrá registrar dos veces al mismo candidato con el mismo DPI para el mismo tipo de servicio. |
| **Protección contra ataques automáticos** | Los formularios de login y recuperación de contraseña tienen límite de intentos. |
| **Correos electrónicos únicos** | Dos usuarios ya no pueden tener el mismo email (ni con mayúsculas ni minúsculas). |
| **Eliminación reversible (papelera)** | Si borra una orden por error, internamente queda marcada como eliminada y puede recuperarse. |
| **Mejor velocidad** | Los listados de órdenes y reportes cargan más rápido, especialmente cuando hay muchos registros. |

---

## ¿Cómo reportarnos algo?

Si algo no funciona como describe este informe, por favor avísenos indicando:
1. En qué pantalla ocurrió.
2. Qué esperaba que pasara.
3. Qué pasó realmente (captura de pantalla si es posible).

Responderemos a la brevedad.

---

**Desarrollado y mantenido por:** Szystems
**Versión en producción:** v2.3.0 — abril 2026
