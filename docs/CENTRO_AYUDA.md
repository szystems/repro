# Centro de Ayuda REPRO

**Implementado:** 2026-08-14  
**Estado:** Fases 1–4 completas (MVP + contextual + tours + candidato)

## Rutas

| Ruta | Acceso | Descripción |
|------|--------|-------------|
| `/ayuda` | Auth (REPRO + Cliente) | Índice por categorías |
| `/ayuda/{slug}` | Auth + permisos | Artículo individual |
| `/ayuda/buscar?q=` | Auth | Búsqueda |
| `/ayuda/faq` | Auth | Preguntas frecuentes |
| `/ayuda/glosario` | Auth | Términos del sistema |
| `/cuestionario/ayuda` | Público | Ayuda para candidatos |

## Arquitectura

- **Config:** `config/ayuda.php` → `config/ayuda/manifest.php` (artículos, FAQ, glosario)
- **Lógica:** `app/Support/AyudaSupport.php` (filtro por rol, permisos, principal)
- **Controller:** `app/Http/Controllers/AyudaController.php`
- **Vistas artículo:** `resources/views/ayuda/articles/*.blade.php`
- **Candidato:** `config/cuestionario_ayuda.php`

## Integración UI

- Sidebar admin + empresa: «Centro de Ayuda»
- Header: icono `?`
- Dashboard: tarjeta con 3 artículos destacados
- Contextual `?`: órdenes index/show/create, editar cuestionario
- Tour Driver.js: primera visita al dashboard (localStorage)
- Candidato: botón Ayuda en navbar + enlace en enlace inválido

## Mantenimiento

1. **Nuevo artículo:** agregar entrada en `manifest.php` + blade en `ayuda/articles/`
2. **Ayuda contextual:** campo `contexto` con patrones (`ordenes/*`, `cuestionarios/*/editar`)
3. **Prioridad contextual:** campo `orden` (menor = gana en misma ruta)
4. **Permisos:** `permisos`, `solo_principal`, `solo_admin`, `audiencias`

## Pendiente opcional

- ~~Capturas anotadas en `public/assets/ayuda/screens/`~~ (3 capturas: orden detalle, enlaces, crear orden)
- Panel admin para editar artículos sin deploy (tabla `ayuda_articulos`)
- Métricas de artículos más consultados

## Mejoras UI (2026-08-15)

- Partials mock wireframe (`ayuda/partials/mock/*`)
- TOC sticky por artículo (`secciones` en manifest)
- Índice agrupado por módulo del menú
- FAQ con iconos y enlace a guía completa
- Glosario con iconos y «Ver en ayuda»
- Diagrama visual en flujo completo
- Capturas reales en `public/assets/ayuda/screens/`

## Tests

```bash
php artisan test --filter=CentroAyudaTest
```
