# Deploy quirúrgico iPage — Formularios 2026-08-01

**Producción:** https://reproappv2.szystems.com  
**Último deploy previo:** Fase 20 (`45c89dc5`, 13-jun-2026) — 5 archivos  
**Este deploy:** Fase F + legal + Word + §5 periódica/específica (`cda6239d`)

---

## 1. Resumen

| Concepto | Cantidad |
|----------|----------|
| Archivos a **subir/reemplazar** | **172** (ver manifiesto) |
| Archivos a **borrar** en FTP | **10** partials legacy |
| Migraciones nuevas | **8** |
| Dependencias composer nuevas | `phpoffice/phpword`, `nelexa/zip` |
| Seed producción | `DepartamentosMunicipiosSeeder` (catálogo GT) |

Manifiesto: [`Formularios_2026-08-01_deploy_manifest.txt`](Formularios_2026-08-01_deploy_manifest.txt)

---

## 2. Qué NO subir

```
tests/ docs/ scripts/ .git/ node_modules/
public/crear_orden_prueba.php
public/create_test_empresa_*.php
```

Los scripts `public/deploy_*.php` se suben **solo** para ejecutar migraciones y se auto-eliminan.

---

## 3. Pasos (FTP)

### 3.1 Backup
1. Export phpMyAdmin → `dbreprov2` (backup completo)
2. Descargar copia de `app/`, `resources/views/admin/cuestionarios/`, `routes/web.php`

### 3.2 Subir archivos del manifiesto
Usar FileZilla o script local:

```bash
bash scripts/deploy_Formularios_2026-08-01.sh
```

Sube cada ruta del manifiesto + `composer.json` + `composer.lock` + carpetas vendor indicadas.

### 3.3 Borrar partials legacy (importante)
Si quedan en el servidor, Laravel puede cargar vistas obsoletas:

```
resources/views/admin/cuestionarios/partials/editar_seccion_*.blade.php  (1–5)
resources/views/admin/cuestionarios/partials/seccion_*.blade.php         (1–5)
```

### 3.4 Config raíz (solo si cambió)
| Local | Remoto |
|-------|--------|
| `.env.ipage` | `.env` |
| `.htaccess_ipage_v2` | `.htaccess` |

No sobrescribir `.env` si en prod hay ajustes manuales posteriores — comparar antes.

### 3.5 Migraciones (sin SSH)
Abrir en navegador **una sola vez**:

```
https://reproappv2.szystems.com/deploy_migrate_formularios_2026.php?key=REPRO_DEPLOY_2026_SECURE_KEY
```

Debe mostrar 8 migraciones `[OK]` y conteos deptos/municipios > 0.

### 3.6 Permisos
- `storage/` → 775 recursivo
- `bootstrap/cache/` → 775

---

## 4. Verificación post-deploy

```bash
# Smoke HTTP
curl -sS -o /dev/null -w "login %{http_code}\n" https://reproappv2.szystems.com/login

# Tras subir script QA (opcional, no permanece en prod):
# php scripts/qa_flujo_tres_roles.php  # solo en local/docker
```

**Manual en prod (5 min):**
1. Login admin → `/cuestionarios` → abrir un cuestionario → pestaña §5 periódica solo judicial
2. Token evaluado de prueba (si hay demo) → verificar sección 5
3. Login empresa → `/empresa/cuestionarios` → sin campos integridad
4. `/reportes/evaluaciones` → export PDF

---

## 5. Rollback

1. Restaurar backup BD phpMyAdmin
2. Restaurar carpetas `app/`, `resources/`, `routes/` del backup FTP
3. Eliminar tablas nuevas solo si restauraste BD parcial (no recomendado — usar backup completo)

---

## 6. Diff vs Fase 19 (referencia)

El deploy Fase 19 (jun-2026) subió 58 archivos. **Este deploy no repite esos archivos** salvo que también cambiaron en commits posteriores (p. ej. `CuestionariosController`, `show.blade.php`, `web.php`).

Generar lista actualizada:

```bash
git diff --name-only 45c89dc5..HEAD -- app config database/migrations database/data public resources routes composer.json composer.lock \
  | grep -v '^database/seeders/' | grep -v '^database/factories/' | sort
```
