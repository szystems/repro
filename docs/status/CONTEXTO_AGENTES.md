# CONTEXTO PARA AGENTES IA - PROYECTO REPRO

**Sistema:** REPRO Guatemala - Plataforma de Evaluaciones Poligráficas  
**Fecha de Contexto:** 2 de julio de 2026  
**Estado:** ✅ FASE 20 DESPLEGADA · 🔄 Fase F **E3** (evaluador/informe) — **E2 ✅ cerrado + QA manual OK**  
**Versión:** 2.3.1 Producción  
**Plataforma:** https://reproappv2.szystems.com  
**Repo:** https://github.com/szystems/repro · branch `master` · commit `14a95f47`

---

## CONTEXTO RÁPIDO PARA AGENTES

### 🎯 PROPÓSITO DEL SISTEMA
REPRO Guatemala es un sistema web para gestionar evaluaciones poligráficas, VSA y socioeconómicas para empresas. Los usuarios empresariales crean órdenes con múltiples evaluados, los evaluados completan cuestionarios digitales, y REPRO realiza las evaluaciones y entrega resultados.

### ⚡ ESTADO ACTUAL (Julio 2026)
- ✅ **PRODUCCIÓN:** Fase 18 + Fase 19 + Fase 20 desplegadas en iPage (`reproappv2.szystems.com`)
- 🔄 **EN DESARROLLO:** **Fase F — Etapa E3 (evaluador + informe)** — E1 ✅ · E2 ✅ (QA manual 2-jul). Plan: `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md`
- ✅ **4 ESTADOS INDEPENDIENTES:** Formulario / Programación / Evaluación / Orden (Fase 18)
- ✅ **FASE 19:** Fix duplicación órdenes, capacidad por sede, historial empresa, archivar órdenes, búsqueda DPI/nombre
- ✅ **TESTS:** 740 tests — E2 Pre-empleo cerrado 2-jul
- ✅ **SEGURIDAD:** Permisos granulares + middleware `role` / `permission`
- ✅ **NOTIFICACIONES:** In-app ampliadas (creador, empresa, colaboradores — Fase 18)
- ⏳ **PENDIENTE OPS:** Cron iPage para auto-transiciones formulario 24h/30d (o fallback on-access)

### 📚 Mapa de contexto (mantener sincronizado)

| Documento | Cuándo actualizar |
|-----------|-------------------|
| `PROGRESS.md` | **Siempre** — sección 🔴 al inicio (fase activa, progreso E1, siguiente paso) |
| `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` | Al cerrar cada punto E1–E7 (marcar `[x]`, estado global al final) |
| `docs/status/CONTEXTO_AGENTES.md` | **Este archivo** — al cerrar sesión o punto relevante (estado, tests, Docker, E1) |
| `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md` | Solo si cambian decisiones comerciales o spec |
| `docs/business/COTIZACION_EXTRAS_JUNIO_2026_CLIENTE.md` | Solo si cambia pricing o extras aprobados |

**Regla (Otto):** no dejar código/documentación desincronizados — contexto actualizado en la misma sesión del cambio.

| Documento | Uso |
|-----------|-----|
| `PROGRESS.md` | Seguimiento activo por fase |
| `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` | **Plan principal** — checklists E1–E7 punto por punto |
| `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md` | Spec formularios + informe Word + decisiones comerciales |
| `docs/business/COTIZACION_EXTRAS_JUNIO_2026_CLIENTE.md` | Word Q 1,600 · 1B · WhatsApp |
| `docs/status/CONTEXTO_AGENTES.md` | Contexto técnico para agentes IA |
| `docs/Fase19_Alcance_Definitivo_2026-06-12.md` | Alcance Fase 19 aprobado |
| `docs/deployment/Fase19_deploy_manifest.txt` | Manifiesto último deploy mayor |

### ✅ Fase F E1 — CERRADA (23-jun-2026)

Motor base 1.1–1.9: tabla dinámica, condicionales, autosave, catálogo GT, foto, instrucciones, precarga, notas evaluador (`evaluador_notas`), tests motor.

### ✅ Fase F E2 — CERRADA (2-jul-2026)

| Completado | Siguiente |
|------------|-----------|
| Pre-empleo 2.1–2.21 (5 secciones matriz) | **E3.1** UI espacios internos evaluador |
| 740 tests OK · **QA manual flujo completo OK** | E3.2–3.4 mapeo informe + permisos |

**Correcciones post-QA manual (2-jul):**
- **2.8** `HistorialAcademico` + `formacion-academica.js` — filas por nivel al seleccionar último grado.
- **2.12** Detalle condicional económico (vehículos, propiedades, SAT, etc.) en `situacion-economica.blade.php`.
- **2.13** Detalle condicional salud en `antecedentes.blade.php`.
- Validación legible: `CuestionarioValidacionLabels`, mensajes en `SaludHabitosCampos` / `SituacionEconomicaCampos`.
- Pantalla `completado.blade.php`: cierre con SweetAlert (pestaña manual).
- Sesión previa: foto/licencia/spinner, badge «Confidencial», autosave.

**Demo manual Pre-empleo:** `DemoPruebaManualE1Seeder` → `http://localhost:8000/cuestionario/e1demo2026pruebamanualtokenrepr0` · DPI `2405617300105`

**Tests E2:** `CuestionarioPreempleoSeccion2ExtendidaTest` · `CuestionarioPreempleoSecciones345Test` · `HistorialAcademicoTest`

### 🔄 Fase F E3 — SIGUIENTE

- **3.1** UI notas/análisis evaluador por sección (REPRO/ADMIN; base: `EvaluadorNotasSupport`, partial admin existente).
- **3.2** Mapeo respuestas → tablas informe (familia, académico, laboral, deudas, complementaria).
- **3.3** Reglas de exclusión al informe (integridad, económica interna, salud, judicial…).
- **3.4** Tests permisos (empresa NO ve internas).

**Tests motor (E1.9):** `CuestionarioMotorE1Test` · `CuestionarioSeccionesTest` · más suites por pieza.

**E2.1 datos generales:** `App\Support\DatosPersonalesCampos` · sección 1 Pre-empleo alineada a spec (tipo ID, nacimiento/residencia GT, IGSS, NIT, licencia; removidos género/profesión/nivel educativo) · tests `CuestionarioPreempleoDatosGeneralesTest`.

**Autosave (E1.3):** `App\Support\CuestionarioAutosave` (validación permisiva) · `POST /cuestionario/{token}/seccion/{n}/autosave` · JS `cuestionario-autosave.js` (debounce + indicador + sendBeacon) · **Guardar Borrador** también guarda parcial sin validación completa.

**Campos condicionales (E1.2):** componente `<x-campo-condicional trigger="..." show-when="...">` · JS `public/js/campos-condicionales.js` · deshabilita/limpia campos ocultos · integrado con `TablaDinamica.syncAll()` · evento `condicional:shown` para hooks locales.

**Tabla dinámica (E1.1):** `App\Support\TablaDinamica` · componente `<x-tabla-dinamica>` · JS `public/js/tabla-dinamica.js` · una sola tabla responsive (CSS tarjetas en móvil) · guardado vía `CuestionarioRespuesta::guardarTabla()` en `valor_json`.

**Precarga (E1.7):** al verificar DPI se congela snapshot de la orden en `datos_precarga_json`. Campos editables trazan cambios en `metadata.precarga` de cada respuesta. REPRO ve diferencias en detalle del cuestionario.

**Flujo cuestionario:** verificar DPI → **instrucciones** (`config/cuestionario_instrucciones.php`) → términos + firma → secciones.

**Foto candidato:** `App\Support\CuestionarioFotoCandidato` · storage `local` en `cuestionarios/fotos/{id}/` · componente `<x-foto-candidato>` · preview vía `GET /cuestionario/{token}/foto-candidato`.

### 🐳 Desarrollo local (Docker)

```bash
cd /home/szott/proyectos/repro && docker compose up -d          # levantar stack
docker compose up -d nginx   # si localhost:8000 no responde (nginx caído)
docker compose exec app php artisan migrate --force
docker compose exec app php -d memory_limit=512M vendor/bin/phpunit
```

- App: http://localhost:8000 · phpMyAdmin: http://localhost:8080
- **Demo manual Pre-empleo:** `docker compose exec app php artisan db:seed --class=DemoPruebaManualE1Seeder --force` → token `e1demo2026pruebamanualtokenrepr0` · DPI `2405617300105`
- Contenedores: `repro-app`, `repro-db`, `repro-nginx`, `repro-phpmyadmin`
- Formulario actual (depto/municipio residencia): `resources/views/cuestionario/secciones/datos-personales.blade.php` + `<x-depto-municipio-select>`

---

## 🆕 ALCANCE NUEVO RECIBIDO (21-jun-2026) — LEER ANTES DE TOCAR FORMULARIOS

La cliente entregó la **especificación funcional completa de formularios** (`CREACIÓN FORMULARIOS DE SISTEMA.pdf`, 46 pág.) + un **informe de ejemplo** para el ítem Word. Análisis detallado en `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md`.

**Es una REINGENIERÍA del motor de cuestionarios, no "campos nuevos".** Hoy el sistema guarda respuestas como clave→valor en `cuestionario_respuestas` con Blade hardcodeado por sección. La especificación exige:
- Tablas dinámicas ilimitadas (hijos, hermanos, empleos, deudas, tatuajes, referencias, bienes…).
- Campos condicionales extensivos; tabla académica autogenerada.
- **Campos internos del evaluador** separados de las respuestas del candidato.
- Generación automática de tablas hacia el informe final (editables por evaluador).
- Foto del candidato + anexos con imágenes; catálogos Deptos/Municipios GT dependientes.
- 4 formularios diferenciados: **Pre-empleo (matriz, 5 secciones)**, **Socioeconómico (5 + 1 exclusiva)**, **Periódica**, **Específica**.

**⚠️ DECISIÓN CLAVE (22-jun): los formularios NO se cobran aparte.** Se verificó que los formularios originales entregados por la cliente al inicio del proyecto (ago-2025: `POLIGRAFO PRESENCIAL.pdf`, `SOCIOECONOMICO...pdf`, `PERIODICO ESPECIFICO.pdf`) **ya contenían todo el contenido** (preguntas, tablas familia/empleos/deudas, drogas, foto). El sistema en producción implementó solo ~70–90 campos (una fracción). Por tanto, **completar los formularios = CIERRE DEL PROYECTO** (alcance original) y desbloquea el **saldo Q 10,000**. Las menciones previas a "Fase F cobrable / Q 14,500-16,000" quedan **anuladas**.

**Decisiones comerciales vigentes (cliente):**
- ✅ **Completar formularios (4 tipos) + motor** → **sin cobro aparte**, es cierre del proyecto (saldo Q 10,000).
- ✅ **Word editable (.docx)** aprobado — **Q 1,600** (50% anticipo). Versión base ahora; "rica" depende del motor completo.
- 🕐 **1B Agregar servicio** — **Q 5,200**, programado a **2–3 meses**.
- 🕐 **WhatsApp API** — **Q 3,800**, pospuesto.
- ✅ Fase A legal (7 autorizaciones + Infornet + corrección Específica) → dentro del **saldo Q 10,000**.
- ✅ **Ajuste temporal Socioeconómico:** permitir marcar "Formulario Completado" manual **solo** para servicio Socioeconómico (usan Jotform mientras tanto).
- Estructura: **5 secciones** matriz + 1 exclusiva Socio.
- Campos internos del evaluador: **solo REPRO** los edita; empresa solo sube info general.

**Plan de trabajo ordenado:** `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md`
**Pendientes del cliente:** plantilla .docx oficial de REPRO; auditoría de campos internos (se piden por etapa). Foto obligatoria: **decidido sí** (cámara o subir).

---

## ARQUITECTURA CLAVE

### Stack Tecnológico
```
Laravel 12.37.0 + PHP 8.3.16 + MySQL 8.0
Frontend: Blade + Bootstrap 5 + jQuery
Auth: Laravel Sanctum + Sistema Roles/Permisos
PDF: DomPDF con branding REPRO
```

### Usuarios del Sistema
```
ADMIN (role_as = 3) → 25 permisos → Control total
REPRO (role_as = 2) → 14 permisos → Evaluaciones + Reportes
EMPRESA (role_as = 1) → 6 permisos → Sus órdenes + Ver resultados
EVALUADOS → NO SON USUARIOS → Acceso por token único
```

### 🔥 REGLA CRÍTICA:
**❌ NUNCA crear usuarios con role_as = 0**
**✅ Los evaluados van en tabla `evaluados_orden` con token único**

---

## MÓDULOS COMPLETADOS

### 1. SEGURIDAD ✅
- Sistema dual: `role_as` (legacy) + `roles/permissions` (nuevo)
- Middleware: auth, role, permission, redirect.role
- 26 permisos distribuidos en 8 módulos

### 2. EMPRESAS ✅
- CRUD completo + PDFs con branding REPRO
- Relación 1:N con usuarios y órdenes

### 3. CONFIGURACIÓN ✅
- Configuración global del sistema
- Logo, email, moneda, redes sociales

### 4. ÓRDENES ✅
- CRUD completo con múltiples evaluados
- Códigos únicos: ORD-YYYY-NNNN
- PDF de orden con evaluados
- Cambio de estados con observaciones e historial (`estado_historial`)
- **Fase 18:** 4 estados independientes por candidato (ver abajo)
- **Fase 19:** Editar orden sin duplicar candidatos · archivar (solo admin, no borrar)
- **Fase 19:** Filtro órdenes archivadas (admin)

**Estados por candidato (Fase 18 — modelo vigente):**
```
estado_formulario    → 5 valores (link_enviado, pendiente_de_llenar, formulario_completado_y_recibido, etc.)
estado_programacion  → 8 valores (contactando, programado, inasistencia, proceso_realizado, etc.)
estado_evaluacion    → 7 valores (pendiente_de_evaluacion → en_proceso → en_revision → informe_final_enviado)
Orden.estado         → 4 valores automáticos: orden_recibida, en_proceso, entregado, cancelado
```

**Sinergia vigente (Fase 19):**
- S4: En Proceso exige formulario completado
- S5: En Proceso exige haber estado Programado
- S2 eliminado: Virtual puede programarse sin formulario
- Capacidad de citas por `sedes.capacidad`, no por poligrafista

### 5. CUESTIONARIOS (ADMIN) ✅
- Ver, editar, marcar completo
- PDF con branding REPRO
- Acceso desde listado de evaluados en orden
- **NUEVO:** 6 tarjetas estadísticas
- **NUEVO:** Link directo a orden
- **NUEVO:** Columna contacto (email, tel, cel)
- **NUEVO:** Columna servicio/formulario
- **NUEVO:** Reenvío manual de correos

### 6. CUESTIONARIOS (PÚBLICO) ✅
- Ruta: `GET /cuestionario/{token}` (`cuestionario.mostrar`) — **no** `/cuestionarios/` (admin)
- Acceso por token sin autenticación; exige `token_expira_at > now()`
- **Fase 20:** vista `enlace-invalido` distingue token inexistente vs expirado; log `Acceso a cuestionario rechazado`
- Vigencia: `Config::diasVigenciaTokenEnlace()` (mín. 1 día; 0 en BD → 30)
- Verificación de identidad por DPI
- Navegación por secciones
- Guardado automático
- Página de confirmación

### 7. DASHBOARD ✅
- Estadísticas diferenciadas por rol (Admin/REPRO vs Empresa)
- **Fase 19:** Búsqueda de candidatos por DPI o nombre (dashboard empresa)

**Ruta:** `GET /dashboard`

### 8. REPORTES ✅ (NUEVO)
- Reporte de Evaluaciones con filtros
- Reporte de Empresas (Admin/REPRO)
- Exportación PDF con branding REPRO (logo horizontal)
- Exportación Excel con columna Tipo de Formulario

**Rutas:**
```
GET /reportes/evaluaciones       - Reporte evaluaciones
GET /reportes/empresas           - Reporte empresas
GET /reportes/evaluaciones/pdf   - Exportar PDF
GET /reportes/evaluaciones/excel - Exportar Excel
```
**Tests:** 10 tests pasando

### 9. PORTAL EMPRESA ✅
- Dashboard con búsqueda de candidatos (Fase 19)
- Navegación: órdenes, evaluados, cuestionarios
- **Fase 19:** Historial de estados visible (config `historial_visible_empresa`, default ON)
- Visualización de resultados cuando `resultados_visibles_empresa` activo
- No ve órdenes archivadas

**Controlador:** `EmpresaController.php` · `AdminController.php` (dashboard empresa)

### 10. NOTIFICACIONES EMAIL ✅
- Email al asignar evaluado (automático)
- Email recordatorio diario (8:00 AM)
- Email confirmación al completar
- Reenvío manual desde UI

**Mailables:**
```
EvaluadoAsignadoMail        - Al crear evaluado
RecordatorioCuestionarioMail - Recordatorio diario
CuestionarioCompletadoMail   - Al completar
```

**Comando:** `php artisan cuestionarios:enviar-recordatorios`
**Tests:** 8 tests pasando

---

## FLUJO PRINCIPAL

```
1. EMPRESA/REPRO crea ORDEN con evaluados
   ├── Múltiples evaluados por orden
   ├── Tipos: Polígrafo, VSA, Socioeconómico  
   └── Formularios: Pre-empleo, Periódica, Específica

2. Sistema genera código único (ORD-2026-NNNN)
   └── Crea tokens únicos para cada evaluado

3. EVALUADO accede con token
   ├── Verifica identidad con DPI
   ├── Completa cuestionario por secciones
   └── Finaliza y firma

4. ADMIN/REPRO ve cuestionario completado
   ├── Puede editar respuestas si necesario
   └── Genera PDF del cuestionario

5. Orden avanza por estados hasta "entregado"
```

---

## BASE DE DATOS CLAVE

### Tablas Principales
```sql
users           -- Usuarios: admin, repro, empresa (NO evaluados)
empresas        -- Empresas clientes
ordenes         -- Órdenes de evaluación
evaluados_orden -- Evaluados con token único (NO son users)
cuestionarios   -- Respuestas JSON por evaluado
roles           -- admin, repro, empresa
permissions     -- 26 permisos granulares
```

### Relaciones
```
Empresa → hasMany → User (role_as = 1)
Empresa → hasMany → Orden
Orden → hasMany → EvaluadoOrden
EvaluadoOrden → hasOne → Cuestionario
```

---

## BRANDING REPRO (PDFs)

### Colores
```css
--color-principal: #000555;  /* Azul oscuro */
--color-secundario: #ffb000; /* Amarillo */
--color-terciario: #ffcc33;  /* Amarillo claro */
--color-fondo: #f8f9fa;      /* Gris claro */
```

### Estructura Header
```html
<div class="repro-header" style="background: #000555;">
    <div class="repro-logo-container" style="background: #f8f9fa;">
        <img src="logoreproxelahorizontal.png" />
    </div>
    <h1 style="color: #ffb000;">Título</h1>
</div>
```

---

## ARCHIVOS CLAVE

### Controladores
```
app/Http/Controllers/Admin/
├── OrdenesController.php        # CRUD + PDF + cambiar estado + reenviar correo
├── CuestionariosController.php  # Ver/editar + PDF
├── EmpresasController.php       # CRUD + PDFs
├── UsersController.php          # CRUD + PDFs
├── DashboardController.php      # Dashboard por rol
├── ReportesController.php       # Reportes + exportación PDF/Excel
└── ConfigController.php

app/Http/Controllers/
├── CuestionarioController.php   # Flujo público evaluados + notificaciones
└── EmpresaController.php        # Portal empresa (verOrden, verCuestionario)
```

### Vistas Principales
```
resources/views/admin/
├── ordenes/       # index, show, create, edit, pdf
├── cuestionarios/ # index (mejorado), show, edit, pdf
├── dashboard/     # index
├── reportes/      # evaluaciones, empresas, pdf/
├── empresa/       # CRUD + PDFs
└── user/          # CRUD + PDFs

resources/views/empresa/
├── ordenes/       # index, show (portal empresa)
└── cuestionarios/ # show (portal empresa)

resources/views/cuestionario/
├── verificar-identidad.blade.php
├── seccion.blade.php
├── finalizar.blade.php
└── completado.blade.php

resources/views/emails/
├── evaluado-asignado.blade.php
├── recordatorio-cuestionario.blade.php
└── cuestionario-completado.blade.php

resources/views/layouts/
└── cuestionario.blade.php  # Layout público
```

### Mailables (NUEVO)
```
app/Mail/
├── EvaluadoAsignadoMail.php
├── RecordatorioCuestionarioMail.php
└── CuestionarioCompletadoMail.php
```

### Comandos Artisan (NUEVO)
```
app/Console/Commands/
└── EnviarRecordatoriosCuestionario.php  # Diario 8:00 AM
```

### Modelos
```
app/Models/
├── Orden.php           # Estados, código único
├── EvaluadoOrden.php   # Token, cuestionario_completado
├── Cuestionario.php    # Respuestas JSON
├── Empresa.php
└── User.php            # HasRolesAndPermissions
```

---

## RUTAS IMPORTANTES

### Admin (requiere auth)
```php
// Órdenes
Route::resource('ordenes', OrdenesController::class);
Route::patch('ordenes/{orden}/cambiar-estado', ...);
Route::get('ordenes/{orden}/pdf', ...);

// Cuestionarios
Route::get('cuestionarios', ...);
Route::get('cuestionarios/{id}', ...);
Route::get('cuestionarios/{id}/pdf', ...);

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index']);

// Reportes
Route::get('reportes/evaluaciones', ...);
Route::get('reportes/evaluaciones/pdf', ...);
Route::get('reportes/evaluaciones/excel', ...);
Route::get('reportes/empresas', ...);

// Reenviar correo
Route::post('evaluados/{evaluado}/reenviar-correo', ...);
```

### Portal Empresa (requiere auth + role empresa)
```php
// Órdenes de empresa
Route::get('empresa/ordenes', [EmpresaController::class, 'ordenes']);
Route::get('empresa/ordenes/{id}', [EmpresaController::class, 'verOrden']);

// Cuestionarios de empresa (si resultados disponibles)
Route::get('empresa/cuestionarios/{id}', [EmpresaController::class, 'verCuestionario']);
```

### Público (sin auth)
```php
Route::get('cuestionario/{token}', ...);
Route::post('cuestionario/{token}/verificar', ...);
Route::get('cuestionario/{token}/seccion/{n}', ...);
Route::post('cuestionario/{token}/seccion/{n}', ...);
Route::get('cuestionario/{token}/finalizar', ...);
Route::post('cuestionario/{token}/completar', ...);
```

---

## PRÓXIMOS MÓDULOS A IMPLEMENTAR

### 1. CALENDARIO/AGENDA (Prioridad Alta)
- Vista de evaluaciones programadas
- Agenda para poligrafistas
- Filtros por fecha y poligrafista

### 2. AUDITORÍA/LOGS (Prioridad Alta)
- Registro de acciones de usuarios
- Historial de cambios en órdenes
- Trazabilidad completa

### 3. GESTIÓN DE POLIGRAFISTAS (Prioridad Media)
- Asignación de evaluaciones
- Carga de trabajo
- Disponibilidad

### 4. RESULTADOS DE EVALUACIONES (Prioridad Media)
- Carga de resultados poligráficos
- Generación de informes finales
- Firma digital

### 5. API REST (Prioridad Baja)
- Endpoints para consulta
- Webhooks
- Documentación

---

## COMANDOS ÚTILES

```bash
# Servidor de desarrollo
php artisan serve

# Ejecutar tests
php artisan test                           # Todos los tests
php artisan test --filter=Dashboard        # Tests de dashboard
php artisan test --filter=Reportes         # Tests de reportes
php artisan test --filter=Notificaciones   # Tests de notificaciones
php artisan test --filter=Ordenes          # Tests de órdenes

# Enviar recordatorios manualmente
php artisan cuestionarios:enviar-recordatorios

# Tinker para debugging
php artisan tinker

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## RESUMEN DE TESTS

| Área | Tests | Estado |
|------|-------|--------|
| Suite completa | 685 | ✅ Pasando (2026-06-23, E1.1 tabla dinámica) |
| Fase 19 | `Fase19Sprint3Test` | ✅ Historial, archivar, búsqueda |
| Sinergia | `Fase18SinergiaReglasSemana3Test` | ✅ S4, S5, S2 eliminado |
| Calendario | `CalendarioTest` | ✅ Capacidad sede |

**Ejecutar:** `docker exec repro-app php -d memory_limit=512M vendor/bin/phpunit`

---

## 📁 REGLAS DE ORGANIZACIÓN

**❌ NUNCA crear archivos .md en la raíz del proyecto**
**✅ SIEMPRE usar carpetas en docs/ según categoría:**
```
docs/status/     → Estados y auditorías
docs/technical/  → Documentación técnica
docs/business/   → Documentos de negocio
docs/security/   → Seguridad
docs/database/   → Base de datos
docs/guides/     → Guías de usuario
docs/deployment/ → Despliegue
```

---

## LÓGICA DE NEGOCIO IMPORTANTE

### Visibilidad de Resultados para Empresa
```php
// En modelo Orden.php
public function resultadosDisponiblesParaEmpresa(): bool
{
    return $this->resultados_visibles_empresa == 1;
}
```
- El campo `resultados_visibles_empresa` en la tabla `ordenes` controla si los usuarios empresa pueden ver los cuestionarios completados
- El reporte de evaluaciones NO usa este filtro (muestra todos los evaluados)
- El acceso al cuestionario individual SÍ usa este filtro

### Redirección por Rol después de CRUD
```php
// OrdenesController.php - store(), update(), destroy()
if (Auth::user()->role_as == 1) {
    return redirect()->route('empresa.ordenes')->with('status', '...');
}
return redirect()->route('ordenes.index')->with('status', '...');
```

---

## DESPLIEGUE EN PRODUCCIÓN

### Hosting: iPage
- **URL:** https://reproappv2.szystems.com
- **Guía:** `docs/deployment/IPAGE_DEPLOY.md`
- **Manifiesto Fase 19:** `docs/deployment/Fase19_deploy_manifest.txt` (58 archivos)
- **Último deploy:** 2026-06-13 · commits `8093ab0a`, `14a95f47` · migraciones batch 111

### Carpetas típicas a subir (FTP):
```
app/, database/, resources/, routes/  (+ vendor/ en deploy completo)
```

### Migraciones Fase 19 (ya aplicadas en prod):
```
2026_06_10_120000_add_historial_visible_empresa_to_configs_table
2026_06_10_120001_add_archivada_fields_to_ordenes_table
```

---

## CONTACTO

**Desarrollador:** Otto Szarata (szystems@hotmail.com)  
**Sistema:** REPRO Guatemala  
**Repositorio:** repro (branch: master)  

---

**Última actualización:** 2 de julio de 2026  
**Estado:** ✅ **Fase F E2 Pre-empleo cerrado (2.1–2.21)** · **731 tests OK** · siguiente: **E3 campos evaluador/informe** · plan: `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md`

**E2 cierre:** tablas dinámicas (hijos, hermanos, formación, empleos, deudas, tatuajes, perforaciones) · exparejas · resumen familiar · integridad/judicial/salud internos · complementaria al informe · admin/PDF · tests `CuestionarioPreempleoSeccion2ExtendidaTest`, `CuestionarioPreempleoSecciones345Test`, `CuestionarioModuloCompletoTest`.
