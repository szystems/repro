# Informe de cambios — REPRO Mayo 2026

Actualización desplegada el **8 de mayo de 2026** en https://reproappv2.szystems.com

---

## Cambios realizados

**Correcciones urgentes**
- Informe final visible para el cliente de inmediato al subirlo
- Solo administradores pueden crear, editar o eliminar usuarios
- El candidato ve el motivo cuando REPRO rechaza un documento
- Calendario muestra correctamente los candidatos disponibles para agendar
- Filtro de estado en cuestionarios corregido
- "Fecha Programada" renombrada a "Fecha Tentativa (sujeta a agenda REPRO)"

**Mejoras en portal cliente (empresa)**
- El cliente puede crear sus propias órdenes desde el portal
- Al crear una orden, puede ingresar puesto, sede y nombre del candidato
- El cliente puede eliminar documentos que subió si están pendientes de revisión
- Nombre del candidato visible en "Mis Últimas Órdenes" del dashboard

**Mejoras para colaboradores (REPRO)**
- Filtro por fecha en listado de órdenes
- Filtro rápido de candidatos con cuestionario incompleto
- Vista previa de documentos en pantalla antes de descargar
- El colaborador puede dejar una observación visible para la empresa
- Al crear/editar colaborador se le asigna sede y puesto

**Funcionalidades nuevas**
- Flujo de estados ampliado a 8 etapas con colores y transiciones automáticas
- Informe final bloqueado tras entrega; requiere justificación para modificar
- Editor de texto enriquecido para redactar el informe preliminar
- PDF dividido en dos botones: Orden de Servicio e Informe del candidato
- Sede del candidato en la orden, reportes y PDF
- Panel por sede con procesos actuales, realizados y pendientes
- Reporte administrativo filtrable por sede con ranking de empresas
- Botón de WhatsApp con lista de sedes activas
- Historial de candidatos en el calendario
- Sección "Finanzas" en el menú (próximas funcionalidades)
- Configuración dividida en pestañas: Identidad, Catálogos y Plantillas
- Notificaciones internas al crear una orden

**Mejoras visuales**
- Una sola barra de desplazamiento (se eliminaron las duplicadas)
- Pie de página anclado correctamente al final del contenido
- Encabezados de vistas con diseño uniforme en todo el panel

---

## Seguridad y despliegue

- Saneamiento del HTML del informe preliminar (protección contra inyección de scripts)
- Catálogo de permisos por rol ampliado a 44 permisos en 16 módulos
- Servidor verificado con `APP_ENV=production` y `APP_DEBUG=false`
- 31 archivos auditados byte-a-byte: 31/31 coinciden con el código fuente
- 3 migraciones de base de datos aplicadas sin pérdida de datos

---

**475 pruebas automatizadas · 0 errores · Sin regresiones en producción**

