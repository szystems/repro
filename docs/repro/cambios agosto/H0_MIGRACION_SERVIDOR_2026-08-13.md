# H0 — Migración servidor / dominio REPRO

**Fecha:** 13 de agosto de 2026  
**Audiencia:** reunión sábado (Stephany + equipo técnico)  
**Estado:** ⛔ **SUPERSEDIDO** 31-ago-2026 → `PLAN_MIGRACION_HETZNER_COOLIFY_2026-08-31.md` (ejecución autorizada: Coolify + Hetzner + `reprogt.com` / `portal.reprogt.com`)

---

## Contexto

Stephany preguntó si la app debe vivir en el **servidor principal de REPRO** o en un **sitio/hosting nuevo**. Hoy prod está en:

| Item | Valor actual |
|------|----------------|
| URL | https://reproappv2.szystems.com |
| Hosting | iPage (FTP `66.96.147.159`, usuario `szrepro`) |
| Stack | Laravel + MySQL + almacenamiento local (`storage/app`) |

---

## Opciones

### A) Mantener hosting actual (recomendado corto plazo)

- **Pros:** cero downtime de migración; FTP/deploy ya probado; go-live lunes sin riesgo infra.
- **Contras:** dominio «v2»; facturación/hosting separado de REPRO si lo desean unificado.

### B) Migrar al servidor principal REPRO

- **Pros:** dominio corporativo (`app.repro…` o similar); un solo proveedor.
- **Contras:** requiere ventana de migración (DB, archivos, DNS, SSL, cron, permisos); riesgo para go-live lunes.

### C) Página nueva / micrositio

- Solo marketing o landing; la app sigue en subdominio actual hasta migración completa.

---

## Checklist técnico (si eligen B)

1. **Requisitos PHP:** 8.2+, extensiones `gd`, `zip` (o PCLZip fallback), `mbstring`, `pdo_mysql`.
2. **Base de datos:** export MySQL prod → import en destino; actualizar `.env`.
3. **Archivos:** `storage/app` (fotos, PDFs, Word), `public/uploads` si aplica.
4. **Cron:** `php artisan schedule:run` cada minuto.
5. **Permisos:** `storage/` y `bootstrap/cache/` escribibles.
6. **DNS + SSL:** apuntar subdominio; certificado Let's Encrypt o del host.
7. **Post-migración:** `php artisan config:cache`, `route:cache`, `view:cache`; smoke login + Word + PDF.
8. **Rollback:** mantener iPage activo 48–72 h con DNS TTL bajo.

---

## Preguntas para la reunión

1. ¿Dominio final deseado? (ej. `evaluaciones.repro.gt`)
2. ¿El servidor principal ya tiene PHP 8.2+ y MySQL accesible?
3. ¿Ventana de mantenimiento aceptable? (mínimo 2–4 h)
4. ¿Go-live lunes en iPage y migración la semana siguiente, o migrar antes del lunes?

---

## Recomendación

**Go-live lunes en prod actual**; planificar migración al servidor REPRO como **Sprint I** con ventana dedicada post-UAT, salvo que el cliente confirme infra lista este fin de semana.
