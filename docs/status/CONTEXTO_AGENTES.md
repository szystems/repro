# CONTEXTO PARA AGENTES IA - PROYECTO REPRO

**Sistema:** REPRO Guatemala - Plataforma de Evaluaciones Poligráficas  
**Fecha de Contexto:** 16 de junio de 2026  
**Estado:** ✅ FASE 20 DESPLEGADA · Fase 19 en producción  
**Versión:** 2.3.1 Producción  
**Plataforma:** https://reproappv2.szystems.com  
**Repo:** https://github.com/szystems/repro · branch `master` · commit `14a95f47`

---

## CONTEXTO RÁPIDO PARA AGENTES

### 🎯 PROPÓSITO DEL SISTEMA
REPRO Guatemala es un sistema web para gestionar evaluaciones poligráficas, VSA y socioeconómicas para empresas. Los usuarios empresariales crean órdenes con múltiples evaluados, los evaluados completan cuestionarios digitales, y REPRO realiza las evaluaciones y entrega resultados.

### ⚡ ESTADO ACTUAL (Junio 2026)
- ✅ **PRODUCCIÓN:** Fase 18 + Fase 19 desplegadas en iPage (`reproappv2.szystems.com`)
- 🔄 **FASE 20 (desplegada 2026-06-16):** Hotfix enlace cuestionario — vista dedicada 404, logging, vigencia token mínima
- ✅ **4 ESTADOS INDEPENDIENTES:** Formulario / Programación / Evaluación / Orden (Fase 18)
- ✅ **FASE 19:** Fix duplicación órdenes, capacidad por sede, historial empresa, archivar órdenes, búsqueda DPI/nombre
- ✅ **TESTS:** 653+ tests (PHPUnit 11, PHP 8.3, Docker `repro-app`)
- ✅ **SEGURIDAD:** Permisos granulares + middleware `role` / `permission`
- ✅ **NOTIFICACIONES:** In-app ampliadas (creador, empresa, colaboradores — Fase 18)
- ⏳ **PENDIENTE OPS:** Cron iPage para auto-transiciones formulario 24h/30d (o fallback on-access)

### 📚 Documentación clave
| Documento | Uso |
|-----------|-----|
| `PROGRESS.md` | Seguimiento activo por fase |
| `docs/Fase19_Alcance_Definitivo_2026-06-12.md` | Alcance Fase 19 aprobado |
| `docs/Informe_Cliente_2026-06-12_Fase19.md` | Informe para el cliente |
| `docs/deployment/Fase19_deploy_manifest.txt` | 58 archivos del último deploy |
| `docs/status/CONTEXTO_AGENTES.md` | Este archivo |
| `PROGRESS.md` → sección Fase 20 | Hotfix enlace cuestionario 404 |

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
| Suite completa | 653 | ✅ Pasando (2026-06-13) |
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

**Última actualización:** 13 de junio de 2026  
**Estado:** ✅ Fase 19 desplegada — contexto alineado con `PROGRESS.md`
