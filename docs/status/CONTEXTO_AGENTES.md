# CONTEXTO PARA AGENTES IA - PROYECTO REPRO

**Sistema:** REPRO Guatemala - Plataforma de Evaluaciones Poligráficas  
**Fecha de Contexto:** 21 de enero de 2026  
**Estado:** ✅ MÓDULOS PRINCIPALES + NUEVAS FUNCIONALIDADES COMPLETADAS  
**Versión:** 2.1.0 Producción  

---

## CONTEXTO RÁPIDO PARA AGENTES

### 🎯 PROPÓSITO DEL SISTEMA
REPRO Guatemala es un sistema web para gestionar evaluaciones poligráficas, VSA y socioeconómicas para empresas. Los usuarios empresariales crean órdenes con múltiples evaluados, los evaluados completan cuestionarios digitales, y REPRO realiza las evaluaciones y entrega resultados.

### ⚡ ESTADO ACTUAL (Enero 2026)
- ✅ **OPERACIONAL:** 8 módulos principales funcionando
- ✅ **CUESTIONARIOS:** Flujo completo implementado + reenvío manual de correos
- ✅ **PDFs:** Diseño unificado con branding REPRO
- ✅ **ÓRDENES:** Sistema de estados completo
- ✅ **SEGURO:** Sistema de permisos granular
- ✅ **ÍNTEGRO:** Base de datos 100% consistente
- ✅ **DASHBOARD:** Estadísticas por rol (Admin/REPRO vs Empresa)
- ✅ **REPORTES:** Evaluaciones y Empresas con exportación PDF/Excel
- ✅ **NOTIFICACIONES:** Emails automáticos y manuales
- ✅ **TESTS:** 74+ tests automatizados pasando

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
- 10 estados de workflow
- Códigos únicos: ORD-YYYY-NNNN
- PDF de orden con evaluados
- Cambio de estados con observaciones
- **NUEVO:** Botón reenviar correo a evaluados

**Estados:**
```
solicitud → autorizacion → requisito → programacion → 
en_proceso → analisis → preliminar → final → entregado/cancelado
```

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
- Acceso por token sin autenticación
- Verificación de identidad por DPI
- Navegación por secciones
- Guardado automático
- Página de confirmación

### 7. DASHBOARD ✅ (NUEVO)
- Estadísticas diferenciadas por rol
- Tarjetas: Órdenes, Evaluados, Completados, Pendientes
- Listas de recientes con acciones rápidas
- Accesos directos a funcionalidades

**Ruta:** `GET /dashboard`
**Tests:** 6 tests pasando

### 8. REPORTES ✅ (NUEVO)
- Reporte de Evaluaciones con filtros
- Reporte de Empresas (Admin/REPRO)
- Exportación PDF con branding REPRO
- Exportación Excel

**Rutas:**
```
GET /reportes/evaluaciones       - Reporte evaluaciones
GET /reportes/empresas           - Reporte empresas
GET /reportes/evaluaciones/pdf   - Exportar PDF
GET /reportes/evaluaciones/excel - Exportar Excel
```
**Tests:** 10 tests pasando

### 9. NOTIFICACIONES EMAIL ✅ (NUEVO)
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
├── DashboardController.php      # Dashboard por rol (NUEVO)
├── ReportesController.php       # Reportes + exportación (NUEVO)
└── ConfigController.php

app/Http/Controllers/
└── CuestionarioController.php   # Flujo público evaluados + notificaciones
```

### Vistas Principales
```
resources/views/admin/
├── ordenes/       # index, show, create, edit, pdf
├── cuestionarios/ # index (mejorado), show, edit, pdf
├── dashboard/     # index (NUEVO)
├── reportes/      # evaluaciones, empresas, pdf (NUEVO)
├── empresa/       # CRUD + PDFs
└── user/          # CRUD + PDFs

resources/views/cuestionario/
├── verificar-identidad.blade.php
├── seccion.blade.php
├── finalizar.blade.php
└── completado.blade.php

resources/views/emails/  # (NUEVO)
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

// Dashboard (NUEVO)
Route::get('dashboard', [DashboardController::class, 'index']);

// Reportes (NUEVO)
Route::get('reportes/evaluaciones', ...);
Route::get('reportes/evaluaciones/pdf', ...);
Route::get('reportes/evaluaciones/excel', ...);
Route::get('reportes/empresas', ...);

// Reenviar correo (NUEVO)
Route::post('evaluados/{evaluado}/reenviar-correo', ...);
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

| Módulo | Tests | Estado |
|--------|-------|--------|
| Dashboard | 6 | ✅ Pasando |
| Reportes | 10 | ✅ Pasando |
| Notificaciones | 8 | ✅ Pasando |
| Órdenes | 7 | ✅ Pasando |
| Cuestionarios | 34 | ✅ 32 pasando, 2 pendientes |
| Otros | 9 | ✅ Pasando |
| **TOTAL** | **74+** | **✅ Funcionando** |

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

## CONTACTO

**Desarrollador:** Otto Szarata (szystems@hotmail.com)  
**Sistema:** REPRO Guatemala  
**Repositorio:** repro (branch: master)  

---

**Última actualización:** 21 de enero de 2026  
**Estado:** ✅ ACTUALIZADO Y VÁLIDO  
