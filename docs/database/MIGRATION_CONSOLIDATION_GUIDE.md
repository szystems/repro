# MIGRACIÓN A SISTEMA CONSOLIDADO

## Análisis de Migraciones Actuales

### Migraciones que SE MANTIENEN (sin cambios):
- `2014_10_11_000000_create_empresas_table.php` ✅
- `2014_10_12_100000_create_password_resets_table.php` ✅  
- `2019_08_19_000000_create_failed_jobs_table.php` ✅
- `2019_12_14_000001_create_personal_access_tokens_table.php` ✅
- `2024_11_25_114748_create_configs_table.php` ✅
- `2025_11_08_000001_create_roles_and_permissions_tables.php` ✅

### Migraciones CONSOLIDADAS (reemplazadas por versiones unificadas):

#### 1. Users Table (3 migraciones → 1)
**ANTIGUAS (eliminar):**
- `2014_10_12_000000_create_users_table.php`
- `2025_11_08_000000_improve_users_table_structure.php`

**NUEVA (usar):**
- `2014_10_12_000000_create_users_table_consolidated.php`

#### 2. Ordenes Table (2 migraciones → 1)
**ANTIGUAS (eliminar):**
- `2025_11_12_200631_create_ordenes_table.php`
- `2025_11_13_235800_simplify_ordenes_table_remove_specific_fields.php`

**NUEVA (usar):**
- `2025_11_12_200631_create_ordenes_table_consolidated.php`

#### 3. Evaluados Orden Table (4 migraciones → 1)
**ANTIGUAS (eliminar):**
- `2025_11_12_200912_create_evaluados_orden_table.php`
- `2025_11_13_001434_add_apellidos_to_evaluados_orden_table.php`
- `2025_11_13_235708_add_specific_fields_to_evaluados_orden_table.php`
- `2025_11_15_123530_remove_unique_constraint_from_dpi_in_evaluados_orden_table.php`

**NUEVA (usar):**
- `2025_11_12_200912_create_evaluados_orden_table_consolidated.php`

## Proceso de Migración

### Paso 1: Backup de la Base de Datos
```bash
mysqldump -u usuario -p dbrepro > backup_antes_consolidacion.sql
```

### Paso 2: Limpiar Migraciones
```bash
# Resetear migraciones (SOLO EN DESARROLLO)
php artisan migrate:reset

# Eliminar archivos de migración antiguos
rm database/migrations/2014_10_12_000000_create_users_table.php
rm database/migrations/2025_11_08_000000_improve_users_table_structure.php
rm database/migrations/2025_11_12_200631_create_ordenes_table.php
rm database/migrations/2025_11_12_200912_create_evaluados_orden_table.php
rm database/migrations/2025_11_13_001434_add_apellidos_to_evaluados_orden_table.php
rm database/migrations/2025_11_13_235708_add_specific_fields_to_evaluados_orden_table.php
rm database/migrations/2025_11_13_235800_simplify_ordenes_table_remove_specific_fields.php
rm database/migrations/2025_11_15_123530_remove_unique_constraint_from_dpi_in_evaluados_orden_table.php
```

### Paso 3: Mover Migraciones Consolidadas
```bash
# Mover las migraciones consolidadas al directorio principal
mv database/migrations_consolidated/2014_10_12_000000_create_users_table_consolidated.php database/migrations/2014_10_12_000000_create_users_table.php
mv database/migrations_consolidated/2025_11_12_200631_create_ordenes_table_consolidated.php database/migrations/2025_11_12_200631_create_ordenes_table.php
mv database/migrations_consolidated/2025_11_12_200912_create_evaluados_orden_table_consolidated.php database/migrations/2025_11_12_200912_create_evaluados_orden_table.php
```

### Paso 4: Ejecutar Migraciones Limpias
```bash
# Ejecutar migraciones desde cero
php artisan migrate

# Sembrar datos iniciales
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=EmpresaSeeder
```

### Paso 5: Verificar Integridad
```bash
# Verificar estructura de tablas
php artisan tinker --execute="
echo 'Users: ' . App\\Models\\User::count();
echo 'Empresas: ' . App\\Models\\Empresa::count();
echo 'Roles: ' . App\\Models\\Role::count();
echo 'Permisos: ' . App\\Models\\Permission::count();
"
```

## Beneficios de la Consolidación

### ✅ Ventajas:
1. **Migraciones más limpias**: De 14 migraciones a 9 migraciones
2. **Menos errores**: Sin dependencias complejas entre migraciones
3. **Deploy más rápido**: Menos archivos que procesar
4. **Estructura final clara**: Cada tabla tiene su migración definitiva
5. **Historial simplificado**: Sin cambios y reversiones confusas

### ⚠️ Consideraciones:
1. **Solo para desarrollo**: Este proceso es seguro porque aún no estamos en producción
2. **Backup obligatorio**: Siempre respaldar antes de cambios estructurales
3. **Tests después**: Ejecutar suite completa de tests post-migración
4. **Seeders actualizados**: Verificar que seeders funcionen con nueva estructura

## Estado Final de Migraciones

```
database/migrations/
├── 2014_10_11_000000_create_empresas_table.php ✅
├── 2014_10_12_000000_create_users_table.php 🆕 (consolidada)
├── 2014_10_12_100000_create_password_resets_table.php ✅
├── 2019_08_19_000000_create_failed_jobs_table.php ✅
├── 2019_12_14_000001_create_personal_access_tokens_table.php ✅
├── 2024_11_25_114748_create_configs_table.php ✅
├── 2025_11_08_000001_create_roles_and_permissions_tables.php ✅
├── 2025_11_12_200631_create_ordenes_table.php 🆕 (consolidada)
└── 2025_11_12_200912_create_evaluados_orden_table.php 🆕 (consolidada)
```

**Total: 9 migraciones (vs 14 anteriores)**

## Comandos de Verificación Post-Consolidación

```bash
# 1. Verificar que todas las migraciones se ejecuten sin error
php artisan migrate:status

# 2. Verificar estructura de base de datos
php artisan tinker --execute="
Schema::hasTable('users') && 
Schema::hasTable('empresas') &&
Schema::hasTable('ordenes') &&
Schema::hasTable('evaluados_orden') &&
Schema::hasTable('roles') &&
Schema::hasTable('permissions')
"

# 3. Ejecutar tests críticos
php artisan test --filter=Feature

# 4. Verificar datos de ejemplo
php artisan tinker --execute="
App\\Models\\User::with('roles')->find(1);
App\\Models\\Orden::with(['empresa', 'evaluados'])->first();
"
```

---

✅ **SISTEMA DE MIGRACIONES CONSOLIDADO Y OPTIMIZADO**