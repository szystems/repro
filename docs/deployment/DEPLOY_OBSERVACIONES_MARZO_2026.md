# Guía de Despliegue — Observaciones Marzo 2026 (Fases 8A–8F)

**Fecha:** 20 de marzo de 2026
**Origen:** 44 tareas del Plan de Observaciones del Cliente
**Commits:** `d5f347a4` → `d000954f` (27 commits en master)

---

## 📋 Pre-requisitos

1. **Hacer backup completo** de la base de datos en producción
2. **Hacer backup** de los archivos actuales del servidor (al menos la carpeta `app/`, `resources/views/`, `routes/`)
3. Tener acceso a phpMyAdmin o terminal MySQL

---

## 1. Crear Carpetas Nuevas

Crear estas carpetas en el servidor si no existen:

```
app/Notifications/
resources/views/emails/
resources/views/partials/
```

---

## 2. Archivos Nuevos (10 archivos — crear en el servidor)

### PHP (6 archivos)

| Archivo | Descripción |
|---------|-------------|
| `app/Http/Controllers/NotificacionesController.php` | API de notificaciones (JSON, marcar leída) |
| `app/Mail/NuevaOrdenSedeMail.php` | Email notificación nueva orden a sede |
| `app/Notifications/OrdenCreadaNotification.php` | Notificación in-app: orden creada |
| `app/Notifications/CuestionarioCompletadoNotification.php` | Notificación in-app: cuestionario completado |
| `app/Notifications/ResultadosDisponiblesNotification.php` | Notificación in-app: resultados disponibles |
| `app/Notifications/EvaluadoAsignadoNotification.php` | Notificación in-app: evaluado asignado |

### Vistas (4 archivos)

| Archivo | Descripción |
|---------|-------------|
| `resources/views/admin/cuestionarios/historial-dpi.blade.php` | Vista búsqueda historial por DPI |
| `resources/views/emails/nueva-orden-sede.blade.php` | Template email nueva orden a sede |
| `resources/views/empresa/ordenes/_documentos_evaluado.blade.php` | Partial documentos para empresa |
| `resources/views/partials/_notificaciones_bell.blade.php` | Campana de notificaciones en navbar |

---

## 3. Archivos Modificados (47 archivos — reemplazar en el servidor)

### Controllers (8 archivos)

| Archivo |
|---------|
| `app/Http/Controllers/Admin/CalendarioController.php` |
| `app/Http/Controllers/Admin/ConfigController.php` |
| `app/Http/Controllers/Admin/CuestionariosController.php` |
| `app/Http/Controllers/Admin/OrdenesController.php` |
| `app/Http/Controllers/Admin/ReportesController.php` |
| `app/Http/Controllers/Admin/UsersController.php` |
| `app/Http/Controllers/CuestionarioController.php` |
| `app/Http/Controllers/Empresa/EmpresaController.php` |

### Models (5 archivos)

| Archivo |
|---------|
| `app/Models/DocumentoEvaluado.php` |
| `app/Models/EvaluadoOrden.php` |
| `app/Models/Orden.php` |
| `app/Models/Sede.php` |
| `app/Models/User.php` |

### Middleware y Form Requests (6 archivos)

| Archivo |
|---------|
| `app/Http/Middleware/CheckPermission.php` |
| `app/Http/Requests/ConfigFormRequest.php` |
| `app/Http/Requests/EvaluadoFormRequest.php` |
| `app/Http/Requests/OrdenFormRequest.php` |
| `app/Http/Requests/ProgramarCitaRequest.php` |
| `app/Http/Requests/UserFormRequest.php` |

### Rutas (1 archivo)

| Archivo |
|---------|
| `routes/web.php` |

### Vistas Admin (15 archivos)

| Archivo |
|---------|
| `resources/views/admin/calendario/dia.blade.php` |
| `resources/views/admin/cuestionarios/pdf.blade.php` |
| `resources/views/admin/ordenes/_documentos_evaluado.blade.php` |
| `resources/views/admin/ordenes/create.blade.php` |
| `resources/views/admin/ordenes/edit.blade.php` |
| `resources/views/admin/ordenes/index.blade.php` |
| `resources/views/admin/ordenes/pdf.blade.php` |
| `resources/views/admin/ordenes/show.blade.php` |
| `resources/views/admin/reportes/empresas.blade.php` |
| `resources/views/admin/reportes/evaluaciones.blade.php` |
| `resources/views/admin/sedes/_form.blade.php` |
| `resources/views/admin/sedes/show.blade.php` |
| `resources/views/admin/user/add.blade.php` |
| `resources/views/admin/user/edit.blade.php` |
| `resources/views/admin/user/show.blade.php` |

### Vistas Empresa (5 archivos)

| Archivo |
|---------|
| `resources/views/empresa/cuestionarios/index.blade.php` |
| `resources/views/empresa/ordenes/index.blade.php` |
| `resources/views/empresa/ordenes/show.blade.php` |
| `resources/views/empresa/usuarios/create.blade.php` |
| `resources/views/empresa/usuarios/edit.blade.php` |

### Vistas Cuestionario y Layouts (6 archivos)

| Archivo |
|---------|
| `resources/views/cuestionario/finalizar.blade.php` |
| `resources/views/cuestionario/terminos.blade.php` |
| `resources/views/layouts/incadmin/nav.blade.php` |
| `resources/views/layouts/incadmin/sidebar.blade.php` |
| `resources/views/layouts/incempresa/nav.blade.php` |
| `resources/views/layouts/incempresa/sidebar.blade.php` |

### PDF (1 archivo)

| Archivo |
|---------|
| `resources/views/pdf/cuestionario_empresa.blade.php` |

---

## 4. SQL — Cambios en Base de Datos

Ejecutar en phpMyAdmin o terminal MySQL **en este orden exacto**:

```sql
-- ============================================
-- 1. Dirección en evaluados (Fase 8B)
-- ============================================
ALTER TABLE `evaluados_orden`
  ADD COLUMN `direccion` VARCHAR(300) NULL AFTER `telefono`;

-- ============================================
-- 2. WhatsApp y Maps en sedes (Fase 8C)
-- ============================================
ALTER TABLE `sedes`
  ADD COLUMN `whatsapp` VARCHAR(30) NULL AFTER `telefono`,
  ADD COLUMN `enlace_maps` VARCHAR(500) NULL AFTER `whatsapp`;

-- ============================================
-- 3. Sede responsable en órdenes (Fase 8C)
-- ============================================
ALTER TABLE `ordenes`
  ADD COLUMN `sede_id` BIGINT UNSIGNED NULL AFTER `empresa_id`,
  ADD CONSTRAINT `ordenes_sede_id_foreign`
    FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) ON DELETE SET NULL;

-- ============================================
-- 4. Modalidad de cita en evaluados (Fase 8C)
-- ============================================
ALTER TABLE `evaluados_orden`
  ADD COLUMN `modalidad` ENUM('presencial','virtual') NULL AFTER `sede_id`;

-- ============================================
-- 5. Sede en usuarios REPRO (Fase 8C)
-- ============================================
ALTER TABLE `users`
  ADD COLUMN `sede_id` BIGINT UNSIGNED NULL AFTER `empresa_id`,
  ADD CONSTRAINT `users_sede_id_foreign`
    FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) ON DELETE SET NULL;

-- ============================================
-- 6. Responsable del proceso (Fase 8D)
-- ============================================
ALTER TABLE `evaluados_orden`
  ADD COLUMN `responsable_id` BIGINT UNSIGNED NULL AFTER `poligrafista_id`,
  ADD CONSTRAINT `evaluados_orden_responsable_id_foreign`
    FOREIGN KEY (`responsable_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- ============================================
-- 7. Agregar 'seguimiento' a tipo_documento (Fase 8E)
-- ============================================
ALTER TABLE `documento_evaluados`
  MODIFY COLUMN `tipo_documento` ENUM(
    'antecedentes_penales','antecedentes_policiacos','cv',
    'constancia_estudios','licencia_auto','licencia_moto',
    'dpi_archivo','pasaporte','carta_laboral','foto_tatuaje',
    'autorizacion_firmada','otro','seguimiento'
  ) NOT NULL;

-- ============================================
-- 8. Tabla de notificaciones (Fase 8F)
-- ============================================
CREATE TABLE `notifications` (
  `id` CHAR(36) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `data` TEXT NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `notifications_notifiable_type_notifiable_id_index`
    (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. Ejecutar RolesAndPermissionsSeeder (Fase 8F)
-- ============================================
-- IMPORTANTE: Ejecutar ANTES del paso 10.
-- Desde SSH o terminal del servidor:
--   php artisan db:seed --class=RolesAndPermissionsSeeder
--
-- Si NO tenés acceso SSH, ejecutar manualmente los INSERT
-- de las tablas roles, permissions y role_permission.
-- (Ver archivo database/seeders/RolesAndPermissionsSeeder.php)

-- ============================================
-- 10. Asignar roles a usuarios existentes (Fase 8F)
-- ============================================
INSERT IGNORE INTO `user_role` (`user_id`, `role_id`, `created_at`, `updated_at`)
SELECT u.id, r.id, NOW(), NOW()
FROM `users` u
JOIN `roles` r ON (
  (u.role_as = 1 AND r.name = 'empresa') OR
  (u.role_as = 2 AND r.name = 'repro') OR
  (u.role_as = 3 AND r.name = 'admin')
);

-- ============================================
-- 11. Registrar migraciones ejecutadas
-- ============================================
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_03_09_191445_add_direccion_to_evaluados_orden_table', 99),
('2026_03_09_194538_add_whatsapp_maps_to_sedes_table', 99),
('2026_03_09_194727_add_sede_id_to_ordenes_table', 99),
('2026_03_09_195451_add_modalidad_to_evaluados_orden_table', 99),
('2026_03_09_200446_add_sede_id_to_users_table', 99),
('2026_03_20_100000_add_responsable_id_to_evaluados_orden_table', 99),
('2026_03_20_175327_add_seguimiento_to_tipo_documento_enum', 99),
('2026_03_20_181632_create_notifications_table', 99),
('2026_03_20_182137_assign_roles_to_existing_users', 99);
```

---

## 5. Post-despliegue

Ejecutar en el servidor después de subir todos los archivos y el SQL:

```bash
# Limpiar cachés de Laravel
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 6. Verificación

Después del despliegue, verificar:

- [ ] Login como admin — verificar campana de notificaciones en navbar
- [ ] Login como empresa — verificar campana de notificaciones en navbar
- [ ] Crear una orden — verificar que no hay errores
- [ ] Verificar que "Estado de Procesos" aparece en sidebar empresa (antes "Estado de Cuestionarios")
- [ ] Menú "Historial por DPI" visible en sidebar admin
- [ ] Editar usuario REPRO — verificar sección de permisos por módulo
- [ ] Crear sub-usuario empresa — verificar checkboxes de permisos
- [ ] Editar una sede — verificar campos WhatsApp y Enlace Maps
- [ ] Crear orden — verificar selector de sede responsable
- [ ] Programar cita — verificar campo modalidad (presencial/virtual)
- [ ] Descargar PDF de evaluado — verificar nombre `{nombre}_{apellido}_Orden{codigo}.pdf`

---

## 📊 Resumen

| Concepto | Cantidad |
|----------|----------|
| Archivos nuevos a crear | 10 |
| Archivos modificados a reemplazar | 47 |
| **Total archivos** | **57** |
| Queries SQL (bloques) | 11 |
| Columnas nuevas en BD | 7 |
| Tablas nuevas en BD | 1 (`notifications`) |
| Enum modificado | 1 (`tipo_documento`) |

---

*Generado el 20 de marzo de 2026*
