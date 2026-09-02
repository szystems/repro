# Permisos portal empresa — Especificación cliente (ago 2026)

**Fuente:** Stephany Castro · `USUARIO DE CLIENTE (2).pdf` · WhatsApp 2–4 ago 2026  
**Plan maestro:** `PLAN_REVISION_AGOSTO_2026.md` §3  
**Implementación:** `App\Support\EmpresaPermisosSupport` · `User::hasPermission()`

---

## Tipos de usuario

| Tipo | Campo BD | Descripción |
|------|----------|-------------|
| **Usuario principal** | `users.principal = 1` | Gerente RRHH / titular de la cuenta empresa. Acceso completo al portal cliente (permisos del MAPA). |
| **Trabajador** | `users.principal = 0` | Reclutador o asistente. Solo lo marcado en `users.permisos` (JSON `permisos_empresa[]`). |
| **REPRO** | `role_as >= 2` | Colaboradores REPRO. Permisos Spatie; no usan `permisos_empresa`. |

---

## Matriz de permisos (trabajador)

Lo que la cliente pidió explícitamente en el PDF:

| Acción | Checkbox UI | Clave JSON | Permiso middleware | Principal | Trabajador (default) |
|--------|-------------|------------|-------------------|-----------|----------------------|
| Ver listado de órdenes | Ver órdenes | `ver_ordenes` | `ordenes.ver` | ✅ | ✅ |
| **Crear nueva orden** | Crear órdenes | `crear_ordenes` | `ordenes.crear` | ✅ | ❌ (no por defecto) |
| Editar/cancelar orden propia | Editar / cancelar… | `editar_ordenes` | `ordenes.editar`, `ordenes.eliminar` | ✅ | ✅ |
| Ver resultados liberados | Ver resultados | `ver_resultados` | `resultados.ver`, `cuestionarios.ver` | ✅ | ✅ |
| PDF orden de servicio | Descargar PDFs… | `descargar_pdf` | `ordenes.ver`, `resultados.descargar` | ✅ | ✅ |
| Subir papelería candidato | Subir papelería | `subir_documentos` | `documentos.subir` | ✅ | ✅ |
| Ver/descargar papelería | Ver y descargar… | `descargar_documentos` | `documentos.ver` | ✅ | ✅ |
| Reportes evaluaciones | Ver reportes | `ver_reportes` | `reportes.ver` | ✅ | ❌ (no por defecto) |

**Perfil default trabajador:** `EmpresaPermisosSupport::PERMISOS_DEFAULT_TRABAJADOR`

---

## Reglas de enforcement (código)

### `User::hasPermission($permisoSistema)`

1. **Empresa principal** → `permisoSistemaPermitido()` (todo el MAPA).
2. **Empresa trabajador** → **solo** JSON `permisos_empresa` vía `empresaTienePermisoSistema()`. **No** se usa el rol Spatie `empresa` (evita que el rol otorgue `ordenes.crear` a todos).
3. **REPRO/admin** → permisos del rol Spatie.

Alta desde **REPRO → Usuarios** (`UsersController::insertuser`): si el tipo es Usuario Empresa y **no** va marcado como principal, se guarda `principal=0` y `permisos` = `PERMISOS_DEFAULT_TRABAJADOR` (igual que un trabajador creado en el portal). El titular (`principal=1`) no usa JSON.

### UI empresa (ocultar si no hay permiso)

| Elemento | Condición |
|----------|-----------|
| Menú «Nueva Orden» | `hasPermission('ordenes.crear')` |
| Botón «Nueva Solicitud» en listado | `hasPermission('ordenes.crear')` |
| PDF orden de servicio | `hasPermission('ordenes.ver')` |
| Subir papelería | `hasPermission('documentos.subir')` |
| Ver/descargar papelería | `hasPermission('documentos.ver')` |
| Menú «Mis Reportes» | `hasPermission('reportes.ver')` |

Rutas protegidas con middleware `permission:*` en `routes/web.php` (órdenes, documentos, etc.).

### Bloqueos REPRO-only (no negociables)

| Recurso | Regla |
|---------|-------|
| Informe Word (.docx) | `role_as >= 2` en `OrdenesController::informeWord()` |
| Edición cuestionario admin | permisos REPRO |

---

## Pendiente (no confundir con permisos)

| Ítem | Sprint | Notas |
|------|--------|-------|
| Mis Órdenes = misma vista REPRO | D | Solo layout/listado; permisos ya OK |
| Estado de Procesos = misma vista REPRO | D | |
| Confidencialidad entre reclutadores | E | ✅ `EmpresaVisibilidadReclutadoresSupport` + campos orden/empresa |
| WhatsApp virtual 77637811 | E | ✅ Migración corrige PROCESO VIRTUAL en prod |

---

## Aviso legal (formulario)

Texto bajo enlace del formulario (empresa + REPRO):

> *Este formulario debe ser llenado únicamente por el aplicante. Está estrictamente prohibido que sea completado por el reclutador.*

Partial: `resources/views/shared/partials/aviso-formulario-solo-candidato.blade.php`

---

## QA manual sugerido

1. Crear trabajador con defaults (sin «Crear órdenes»).
2. Login trabajador → no debe verse «Nueva Orden»; `/ordenes/create` → 403.
3. Con permisos default → subir papelería, ver/descargar docs, PDF orden OK.
4. Login principal → crear orden OK; gestionar usuarios OK.
5. Desmarcar «Subir papelería» → formulario subida oculto; POST → 403.

---

## Archivos clave

```
app/Support/EmpresaPermisosSupport.php
app/Models/User.php                    → hasPermission(), tienePermisoEmpresa()
app/Http/Middleware/CheckPermission.php
resources/views/empresa/usuarios/create.blade.php
resources/views/empresa/usuarios/edit.blade.php
resources/views/layouts/incempresa/sidebar.blade.php
resources/views/empresa/ordenes/index.blade.php
resources/views/empresa/ordenes/show.blade.php
resources/views/empresa/ordenes/_documentos_evaluado.blade.php
```

**Deploy producción:** 2026-08-06 (FTP + caché) — archivos listados arriba.

**Última actualización:** 2026-08-06
