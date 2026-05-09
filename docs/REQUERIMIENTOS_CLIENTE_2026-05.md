# Requerimientos REPRO — Mayo 2026

---

## Fase 1 — Correcciones urgentes

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | C5 | Al subir el informe final, el cliente lo ve de inmediato sin intervención manual |
| ✓ | CO10 | Solo administradores pueden crear, editar o eliminar usuarios |
| ✓ | CA1 | El candidato ve el motivo cuando REPRO rechaza uno de sus documentos |
| ✓ | CO9 | El calendario muestra correctamente los candidatos disponibles para agendar |
| ✓ | A9 | El filtro de estado en cuestionarios (Pendiente / En Progreso / Completado) aplica correctamente |
| ✓ | N1 | "Fecha Programada" renombrada a "Fecha Tentativa (sujeta a agenda REPRO)" |

---

## Fase 2 — Mejoras rápidas

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | A1 | Renombrar la sección de cuestionarios a "Gestión de Cuestionario – Candidatos" |
| ✓ | A2 | Agregar filtros por tipo de servicio y sede en cuestionarios |
| ✓ | A3 | Mostrar columna de sede en la tabla de cuestionarios |
| ✓ | A10 | Notificación interna al crear una orden (empresa + servicio) |
| ✓ | C1 | Mostrar nombre del candidato en "Mis Últimas Órdenes" del panel del cliente |
| ✓ | C2 | El cliente puede crear sus propias órdenes desde el portal empresa |
| ✓ | C2-puesto | El cliente puede ingresar puesto y sede del candidato al crear una orden |
| ✓ | C3 | El cliente puede eliminar documentos que subió si aún están pendientes de revisión |
| ✓ | CO4 | Filtro por fecha en el listado de órdenes del colaborador |
| ✓ | CO8 | Filtro rápido para ver solo candidatos con cuestionario incompleto |

---

## Fase 3 — Funcionalidades nuevas

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | C2-sede | El cliente ingresa la sede del candidato directamente en la orden; aparece en reportes y PDF |
| ✓ | T1 | Separar el botón PDF en dos: Orden de Servicio e Informe del candidato |
| ✓ | CO5 | Vista previa de documentos en pantalla antes de descargar |
| ✓ | CO7 | El colaborador puede dejar una observación visible para la empresa en el detalle del candidato |
| ✓ | CO1 | Al crear/editar un colaborador, se le puede asignar sede y puesto |

---

## Fase 4 — Estados y bloqueos (alta complejidad)

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | A6 | Ampliar el flujo de estados del proceso a 8 etapas con colores y transiciones automáticas |
| ✓ | CO3 | Bloquear el informe final una vez entregado; requiere justificación para modificar |

---

## Fase 5 — Reportes y sedes

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | A5 | Panel por sede: procesos actuales, realizados, pendientes, búsqueda por nombre/DPI |
| ✓ | A7 | Reporte administrativo filtrable por sede con ranking de empresas |
| ✓ | C4 | Botón de WhatsApp con lista de sedes activas para contacto del cliente |
| ✓ | CO9-hist | Historial de candidatos en el calendario |

---

## Fase 6 — Configuración y módulo Finanzas

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | A8 | Dividir Configuración en subsecciones: Identidad, Plantillas y Catálogos |
| ✓ | A8-fin | Agregar sección "Finanzas" al menú con pantalla de "Próximamente" |

---

## Fase 7 — Editor de informes

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | CO6 | Editor de texto enriquecido para redactar el informe preliminar directamente en el sistema |

---

## Fase 8 — Mejoras visuales generales

Ajustes de presentación que afectan a todas las pantallas. No cambian funcionalidad, solo apariencia y comodidad de uso.

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | UI1 | Eliminar las barras de desplazamiento duplicadas que aparecen en el lado derecho. Dejar una sola barra, la del navegador. |
| ✓ | UI2 | Anclar el pie de página al final del contenido. Hoy queda un espacio en blanco grande entre el último elemento y el pie de página cuando la pantalla es corta. |
| ✓ | UI3 | Revisar todas las vistas para que no se generen contenedores con su propio scroll dentro de otros contenedores que también tienen scroll. |

> Nota: aplicada una mitigación temporal el 7 de mayo de 2026 que reduce el problema en la vista de detalle de orden, pero la solución completa requiere un repaso transversal y se aborda en esta fase.

---

## Fase 9 — Hardening pre-despliegue (auditoría profesional)

Auditoría ejecutada el 8 de mayo de 2026 antes de subir los cambios al servidor de producción. No corresponde a una solicitud explícita del cliente, pero es indispensable para garantizar la estabilidad y seguridad del despliegue.

### Hallazgos críticos resueltos

| Estado | Ref | Descripción |
|--------|-----|-------------|
| ✓ | H1 | Saneamiento del HTML del informe preliminar (Quill) para evitar inyección de scripts en sesión de empresa |
| ✓ | H2 | Ampliación del catálogo de permisos por rol con los 20 permisos correspondientes a las nuevas funcionalidades de Fases 1–8 (sedes, finanzas, calendario, notificaciones, documentos, informe preliminar, observaciones, historial DPI) |

### Pendientes antes del push a producción

| Estado | Ref | Descripción |
|--------|-----|-------------|
|   | H3 | Checklist de despliegue actualizado con el seeder de permisos como paso obligatorio post-pull |
|   | H4 | Verificación manual de `APP_DEBUG=false` en el servidor de producción |

### Mejoras planificadas para próximo sprint (no bloquean este deploy)

| Estado | Ref | Descripción |
|--------|-----|-------------|
|   | H5 | Migrar rutas con `role:admin,repro` hardcoded al middleware `permission:` para aprovechar el catálogo granular |
|   | H6 | Silenciar el warning de `psysh` que genera 8 entradas diarias de ruido en logs |
|   | H7 | Revisión final de las 14 ocurrencias restantes de `{!! !!}` (ya verificadas seguras) |
|   | H8 | Caché de configuración, rutas y vistas como paso fijo del script de despliegue |
|   | H9 | Crear Policies Eloquent para reemplazar los checks dispersos basados en `role_as` |
|   | H10 | Cobertura de tests para asignación automática de los nuevos permisos a roles |
|   | H11 | Tests E2E del flujo evaluado vía token único |
|   | H12 | Auditoría de las 5 ocurrencias de `DB::raw` para confirmar ausencia de input de usuario |

---

*8 de mayo de 2026 — Fase 1 completada (8/8) · Fase 2 completada (10/10) · Fase 3 completada (5/5) · Fase 4 completada (2/2) · Fase 5 completada (4/4) · Fase 6 completada (2/2) · Fase 7 completada (1/1) · Fase 8 completada (3/3) · Fase 9 en progreso (2/4 críticos · 8 mejoras planificadas) · 475 tests · 0 pendientes*

