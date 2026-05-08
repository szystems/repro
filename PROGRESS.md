# PROGRESS — Requerimientos Cliente Mayo 2026

**Documento de seguimiento activo**
**Base de referencia:** docs/REQUERIMIENTOS_CLIENTE_2026-05.md
**Ultima actualizacion:** 2026-05-07
**Suite de tests:** 433/433

---

## Estado por Fase

| Fase | Descripcion | Estado |
|------|-------------|--------|
| Fase 1 | Correcciones urgentes (8 items) | COMPLETADA |
| Fase 2 | Mejoras rapidas (10 items) | PENDIENTE |
| Fase 3 | Funcionalidades nuevas | PENDIENTE |
| Fase 4 | Estados y bloqueos | PENDIENTE |
| Fase 5 | Reportes y sedes | PENDIENTE |
| Fase 6 | Configuracion + Finanzas | PENDIENTE |
| Fase 7 | Editor de informes | PENDIENTE |
| Fase 8 | Mejoras visuales (layout/scroll) | PENDIENTE |

---

## Fase 1 - Correcciones urgentes - COMPLETADA 2026-05-07

Verificacion manual completada. 433 tests pasando.

| Ref | Descripcion | Tests | Verificado |
|-----|-------------|-------|------------|
| N1 | Fecha Programada renombrada a Fecha Tentativa | Sprint1BugFixesTest (2) | OK |
| CO10 | Solo admins crean/editan/eliminan usuarios | AuditoriaSeguridadTest (4) | OK |
| A9 | Filtro estado cuestionarios corregido | Sprint1BugFixesTest (3) | OK |
| C3 | Upload documentos evaluado, fix 413 nginx 20M + PHP 20M | infra | OK |
| CA1 | Candidato ve motivo de rechazo de documento | Sprint1BugFixesTest (2) | OK |
| CO9-1 | Dropdown calendario incluye evaluados con cita | Sprint1BugFixesTest (2) | OK |
| CO9-2 | Conteo calendario mensual = vista dia | Sprint1BugFixesTest (1) | OK |
| C5 | Al subir informe final auto-entrega y cliente ve informe | Fase2DocumentacionTest (4) | OK |

### Cambios adicionales durante Fase 1

C2 implementado adelantado:
- Empresa puede crear ordenes propias desde el portal
- Boton Nueva Solicitud en empresa/ordenes/index.blade.php
- 5 tests en EmpresaCrearOrdenTest

Infraestructura:
- docker/nginx/default.conf: client_max_body_size 20M
- Dockerfile: upload_max_filesize=20M, post_max_size=20M
- Contenedores reconstruidos

Notificaciones fix:
- CSRF: _notificaciones_bell.blade.php usaba meta csrf-token inexistente, cambiado a Blade csrf_token()
- URLs por rol: 4 notificaciones ahora generan URL segun role_as del destinatario
- 2 notificaciones existentes en BD corregidas via Tinker

Vista empresa/ordenes/show:
- Muestra botones Informe Final y Preliminar cuando orden entregada y resultados_visibles_empresa=true

UI Layout (mitigacion temporal, solucion completa en Fase 8):
- CSS: .content-wrapper-scroll anidado con overflow:visible en ambos layouts
- JS: OverlayScrollbars no se aplica a wrappers anidados en custom-scrollbar.js
- Cache-buster v=20260507 en script de custom-scrollbar.js
- Fix HTML: div extra en modal reprogramacion de admin/ordenes/show.blade.php

---

## Fase 2 - Mejoras rapidas - PENDIENTE

| Ref | Descripcion |
|-----|-------------|
| A1 | Renombrar seccion cuestionarios a Gestion de Cuestionario - Candidatos |
| A2 | Filtros tipo de servicio y sede en cuestionarios |
| A3 | Columna sede en tabla cuestionarios |
| A10 | Notificacion interna al crear orden |
| C1 | Nombre candidato en Mis Ultimas Ordenes del dashboard |
| C2-puesto | Campo puesto y sede del candidato al crear orden |
| C3 | Cliente elimina documentos pendientes propios |
| CO4 | Filtro por fecha en listado de ordenes colaborador |
| CO8 | Filtro cuestionarios incompletos colaborador |

---

## Fase 8 - Deuda Layout UI - PENDIENTE

Problema: multiples barras de scroll apiladas y footer flotante.

Causa raiz: page-wrapper con overflow-y:auto y height:100vh del template mas
content-wrapper-scroll con overflow-y:auto en layouts mas OverlayScrollbars plugin.
36 vistas anidan un segundo content-wrapper-scroll.

Plan L1-L7:
- L1: Un unico modelo de scroll (scroll en html, sidebar position fixed)
- L2: Quitar overflow-y:auto y height:100vh de page-wrapper
- L3: Eliminar OverlayScrollbars en content-wrapper-scroll
- L4: Quitar div content-wrapper-scroll anidado en las 36 vistas
- L5: Mover footer dentro del wrapper de contenido
- L6: Auditar otros overflow:auto internos
- L7: Test visual - 1 solo scrollbar en pantallas menores a 1080p

---

## Historial baseline tests

| Fecha | Tests | Hito |
|-------|-------|------|
| 2026-04-22 | ~391 | Ronda 1 observaciones cliente |
| 2026-04-22 | 399 | Sprint Auditoria-1 |
| 2026-04-22 | 403 | Sprint Auditoria-2 |
| 2026-04-22 | 409 | Sprint Auditoria-3 |
| 2026-05-07 | 428 | Sprint-1 Fase 1 N1 CO10 A9 C3 CA1 CO9 C5 |
| 2026-05-07 | 433 | C2 + fixes notificaciones + infra 20M |
