# Respuesta a Observaciones — Sistema REPRO

**Fecha:** 9 de marzo de 2026
**Preparado para:** Equipo REPRO Guatemala
**En referencia a:** Observaciones recibidas el 9 de marzo de 2026

---

## Resumen

Recibimos y analizamos todas las observaciones. A continuación respondemos punto por punto: qué se va a corregir, qué ya funciona correctamente (y explicamos cómo usarlo), y el plan de trabajo para las mejoras solicitadas.

---

## ✅ Cosas que ya funcionan (explicación)

### Resultado preliminar
La opción de subir un resultado preliminar **ya existe**. Se encuentra dentro de la vista de cada evaluado en la orden. Al abrir el detalle de un evaluado, hay campos para subir el archivo de resultado preliminar y resultado final.

### Observaciones internas
Las observaciones que se ingresan al crear una orden son **observaciones internas** — solo visibles para personal de REPRO. Esto es por diseño, ya que contienen instrucciones operativas que el cliente no necesita ver. Sin embargo, vamos a mejorar esto para que las observaciones que SÍ corresponden al cliente sean visibles para ellos.

### Búsqueda de candidato anterior
El sistema **ya tiene** la funcionalidad de buscar si un candidato vino anteriormente usando su número de DPI. Vamos a hacer esta opción más visible y fácil de encontrar en el menú principal.

### Correo @szystems
El correo actual (@szystems) es temporal para las pruebas. Cuando el equipo REPRO tenga su dominio de correo configurado (por ejemplo @reprogt.com), se actualiza en el sistema y todos los correos saldrán desde la dirección que ustedes elijan.

---

## 🐛 Errores que vamos a corregir

| Problema reportado | Solución |
|---|---|
| **Datos se borran** al fallar la creación de una orden | Se va a corregir para que todos los datos ingresados (incluyendo los evaluados) se conserven si hay un error de validación |
| **Error al crear orden** desde usuario de empresa | Se va a corregir el error. La orden sí se crea pero el mensaje de confirmación falla — esto quedará resuelto |
| **Error en Configuración** | Se va a corregir un problema en el procesamiento de moneda que causaba el error |
| **Datos repetidos** al crear usuario | Se encontró un campo duplicado en el formulario que se va a eliminar |
| **Copiar enlace** no funciona | Esto requiere conexión HTTPS (segura). En el servidor de producción ya está habilitado — vamos a verificar y si persiste, agregar un método alternativo |

---

## 📋 Mejoras que vamos a implementar

### Cambios de texto y campos
- **"Estado de Cuestionarios"** se cambiará a **"Estado de Procesos"** en el menú del cliente
- **Fecha Límite** se quitará y se reemplazará por **Fecha de Creación** de la orden
- Los **nombres de los archivos PDF** generados incluirán el nombre del evaluado y el número de orden
- Se agregará **filtro por empresa** en el reporte de empresas (actualmente solo filtra por estado y fecha)- Se agregará el campo de **dirección** del evaluado al agregar o editar evaluados en una orden
- Se agregará un campo de **observaciones por evaluado** al crear o editar una orden, para que la empresa pueda incluir notas específicas sobre cada candidato (por ejemplo: “verificar si tiene tatuajes”). Estas observaciones serán visibles para el personal de REPRO responsable de la orden
### Mejoras de experiencia para el cliente (empresa)
- Se agregarán los **colores de estado** solicitados para que el cliente vea claramente en qué etapa va cada proceso:
  - 🟡 Formulario enviado
  - 🟠 Formulario lleno
  - 🔵 Programación pendiente
  - 🔵 Programado
  - 🟢 En proceso
  - 🟢 En análisis
  - 🟢 Resultado
  - 🟢 Informe generado
  - ⚪ Orden finalizada
- El cliente podrá ver **todos los estados** de su proceso (no solo uno)
- El cliente podrá **reenviar el enlace del formulario** al candidato directamente desde su vista
- La **firma del candidato** se moverá para que aparezca junto con la autorización

### Modalidad de cita
- Al programar una cita se podrá seleccionar si es **presencial** o **virtual**
- Para **polígrafo** la cita será siempre presencial (se asigna automáticamente)
- Para **VSA** la cita será siempre virtual (se asigna automáticamente)
- Para **socioeconómico** se podrá escoger entre presencial o virtual
- La modalidad será visible en el detalle del evaluado y en el calendario

### Mejoras en sedes
- Se agregará un campo de **sede responsable** al crear la orden, para indicar qué sede está a cargo de esa orden
- Al programar la cita de cada evaluado, la sede de la orden vendrá **pre-seleccionada** para agilizar el proceso (pero se podrá cambiar si un evaluado asiste a otra sede)
- Se agregarán **filtros por sede** en los listados y reportes de órdenes para ver solo las órdenes de una sede en específico
- Se agregará campo de **WhatsApp** y **enlace de ubicación** (Google Maps) al crear sedes
- Cada usuario de REPRO tendrá asignada la **sede en la que trabaja**, para saber quién es responsable de qué
- Cuando una empresa cree una nueva orden, los **empleados de REPRO de la sede responsable recibirán un aviso** para que sepan que tienen una nueva orden que trabajar

### Mejoras en los informes PDF
- El PDF incluirá el **texto de autorización** firmado por el candidato
- El PDF incluirá la **papelería verificada** (documentos subidos y su estado de validación)
- Se incluirá el **nombre y firma del responsable** del proceso en el informe

### Reportes
- Los reportes se podrán **filtrar por mes**, tanto para el cliente como internamente

### Servicios múltiples
- Se podrá agregar al **mismo evaluado más de una vez** en una orden con diferente tipo de servicio (por ejemplo: una vez para Polígrafo y otra para Socioeconómico). Cada servicio genera su propio formulario, programación y resultado de forma independiente.

### Documentos
- Se mejorará la gestión de documentos para que si la papelería **ya fue subida**, el candidato lo vea y pueda actualizarla en caso necesario
- Los inputs de documentos permitirán **tomar fotografía** directamente desde el celular
- La papelería será visible al editar el informe y se podrá **validar internamente**
- La **empresa podrá subir documentos** de sus evaluados directamente desde su portal, para adelantar el proceso cuando ya cuente con la papelería antes de que el candidato llene el cuestionario
- El personal de REPRO podrá adjuntar **archivos de seguimiento** en cada evaluado (reportes, notas, evidencias). Estos documentos serán visibles para la empresa como parte del expediente del evaluado

---

## 🚀 Funcionalidades nuevas (desarrollo adicional)

Estas funcionalidades requieren desarrollo significativo y se implementarán en fases posteriores:

### Sistema de notificaciones internas
En lugar de que todas las notificaciones lleguen por correo electrónico, se implementará un **sistema de notificaciones dentro del sistema** (icono de campana con contador). Esto aplica tanto para notificaciones al cliente como internas. Los correos seguirán disponibles como opción pero no serán la única vía.

### Sistema de permisos por usuario
Se activará un sistema de permisos que permite:
- **Para personal REPRO:** Controlar qué opciones puede ver y usar cada empleado (actualmente todos tienen acceso completo)
- **Para empresas:** Que el administrador de la empresa pueda controlar los permisos de su personal

---

## 📅 Orden de implementación

| Fase | Descripción | Cantidad de tareas |
|------|-------------|-------------------|
| **8A** | Corrección de errores | 6 | ✅ Completada |
| **8B** | Ajustes de texto, campos y accesibilidad | 9 | ✅ Completada |
| **8C** | Estados, colores, sedes, modalidad y mejoras de experiencia | 13 | ☐ Pendiente |
| **8D** | Mejoras en informes PDF | 5 | ☐ Pendiente |
| **8E** | Servicios múltiples, documentos y reportes | 7 | ☐ Pendiente |
| **8F** | Notificaciones y permisos | 4 | ☐ Pendiente |
| **Total** | | **44 tareas** | |

Las fases se ejecutarán en ese orden, priorizando la corrección de errores y las mejoras que más impactan la experiencia diaria de uso.

---

*Informe preparado el 9 de marzo de 2026*
