# Guía de Despliegue en iPage - REPRO

## Estructura en el servidor

```
/reproapp/                    ← Raíz del subdominio
├── .htaccess                 ← Usar .htaccess_ipage_root (renombrar a .htaccess)
├── .env                      ← Usar .env_ipage (renombrar a .env)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── .htaccess             ← El que ya existe
│   ├── index.php
│   ├── assets/
│   ├── img/
│   └── ...
├── resources/
├── routes/
├── storage/
└── vendor/
```

## Archivos a SUBIR por FTP

### ✅ SUBIR estas carpetas completas:
```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
vendor/
```

### ✅ SUBIR estos archivos de la raíz:
```
artisan
composer.json
composer.lock
server.php
```

### ✅ SUBIR estos archivos ESPECIALES (renombrados):
| Archivo local | Renombrar a en servidor |
|--------------|------------------------|
| `.env_ipage` | `.env` |
| `.htaccess_ipage_root` | `.htaccess` |

### ❌ NO SUBIR:
```
.env                  ← Usar .env_ipage en su lugar
.git/
.venv/
node_modules/
tests/
docs/
scripts/
.gitignore
.editorconfig
phpunit.xml
webpack.mix.js
package.json
package-lock.json
composer.json.backup
README.md
boost.json
```

## Pasos de instalación

### 1. Subir archivos por FTP
1. Conectar a iPage por FTP
2. Navegar a `/reproapp/`
3. Subir todas las carpetas listadas arriba
4. Subir `.env_ipage` y renombrarlo a `.env`
5. Subir `.htaccess_ipage_root` y renombrarlo a `.htaccess`

### 2. Permisos de carpetas (si tienes acceso SSH)
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Si no tienes SSH, usar el administrador de archivos de iPage para cambiar permisos a:
- `storage/` → 775 (recursivo)
- `bootstrap/cache/` → 775

### 3. Verificar la base de datos
- Asegúrate de que la base de datos `dbrepro` exista en iPage
- Si necesitas importar datos, exporta tu BD local y súbela via phpMyAdmin de iPage

### 4. Limpiar caché (si tienes SSH)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## URL Final
```
https://reproapp.szystems.com/
```

## Solución de problemas comunes

### Error 500
- Verificar permisos de `storage/` y `bootstrap/cache/`
- Revisar `storage/logs/laravel.log`

### Página en blanco
- Verificar que `.htaccess` esté correctamente subido
- Verificar que `mod_rewrite` esté habilitado

### Error de base de datos
- Verificar credenciales en `.env`
- Verificar que el host de BD sea correcto para iPage

### Assets no cargan (CSS/JS)
- Verificar que `public/assets/` esté subido
- Verificar que las rutas usen `asset()` helper

## Notas importantes

1. **APP_DEBUG=false** en producción por seguridad
2. **APP_ENV=production** para optimizaciones
3. El `.htaccess` de la raíz redirige a `/public/`
4. El `.htaccess` de `/public/` maneja las rutas de Laravel
