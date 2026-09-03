# Migración REPRO → Hetzner + Coolify + reprogt.com

**Fecha:** 31 de agosto de 2026  
**Autorización:** Otto (hoy) — Stephany ya dio permiso de pasar a servidor propio.  
**Estado:** 📋 **M3 humo OK** (sslip.io `/login` 200, 2-sep) · **M4 en repo** · **siguiente = freeze M5** · no cortar iPage.  
**Supersede:** `H0_MIGRACION_SERVIDOR_2026-08-13.md` (era decisión de negocio; ahora es ejecución).  
**Prod actual:** https://reproappv2.szystems.com (iPage, LiteSpeed, sin `ZipArchive` / XMLWriter / Imagick).  
**Repo app:** https://github.com/szystems/repro · `master`

---

## Destino acordado

| Pieza | URL / host | Qué es |
|-------|------------|--------|
| Sitio web (hoy `reproxela.com`) | **https://reprogt.com** | Marketing / institucional. Se compra el dominio y se mueve o replica lo de reproxela. |
| Aplicación Laravel | **https://portal.reprogt.com** | Login REPRO + empresa. Enlaces de evaluados: `https://portal.reprogt.com/cuestionario/{token}` |
| Panel Coolify (interno) | p.ej. `https://coolify.reprogt.com` o IP:8000 | Solo Otto. No es público para Stephany. |
| VPS | Hetzner proyecto **szystems** · `ubuntu-8gb-hil-1` **CPX31** · 4 vCPU / 8 GB / **160 GB SSD** · Hillsboro | Capacidad OK para 600–700 eval/mes. **Backups VPS hoy apagados.** |

**No es login de evaluados.** El candidato no tiene usuario. En la web: botón **Empresas / Ingresar** → `/login` y botón **Soy evaluado** → texto + “use el enlace del correo” (y contacto). Opcional después: página `/soy-evaluado` en el portal.

---

## Por qué Coolify + Hetzner (y no FTP otra vez)

- iPage: workers LiteSpeed, sin `ext-zip` / XMLWriter / Imagick, cron dudoso, 503 con fotos grandes.  
- En Hetzner con Docker: PHP con `zip`, `gd`, `xmlwriter` nativos → Word/Excel reales, no PCLZip / HTML `.xls`.  
- Coolify: deploy desde GitHub, SSL Let’s Encrypt, cron, volúmenes, rollback por commit.  
- iPage se queda **48–72 h** como rollback (DNS TTL bajo).

---

## Inventario de credenciales

Marcar ✅ cuando Otto confirme que las tiene a mano. **No escribir contraseñas en este archivo.**

### Lo que SÍ está documentado / en uso

| Ítem | Dónde / valor conocido | ¿Basta para migrar? |
|------|------------------------|---------------------|
| FTP iPage | `scripts/deploy_ftp.sh` · host `66.96.147.159` · user `szrepro` | ✅ dump archivos `storage/` |
| Ruta iPage | `/hermes/.../ipg.szclinicascom/reproappv2/` | ✅ |
| MySQL iPage | host `szclinicascom.ipagemysql.com` · BD `dbreprov2` · user `szreprov2` | ⚠️ falta **password** (está en `.env` del server, no en el repo) |
| URL / cache key | `https://reproappv2.szystems.com` · `REPRO_DEPLOY_2026_SECURE_KEY` | ✅ scripts one-shot si hace falta |
| GitHub | `szystems/repro` · `master` | ⚠️ falta **GitHub App / deploy key** en Coolify |
| Hetzner VPS | consola Otto · CPX31 identificado 24-ago | ⚠️ **SSH root desde este WSL = permission denied** |
| UAT | `uat.g1.browser@repro.local` / user 223 · cliente PRUEBA 1 user 224 | ✅ smoke post-cutover. **No resetear passwords de Stephany** |

### Lo que FALTA (bloquea el arranque)

| ID | Qué | Quién | Notas |
|----|-----|-------|-------|
| **C1** | Comprar **reprogt.com** | Otto | ✅ 1-sep-2026 |
| **C2** | Zona DNS en **Cloudflare** + nameservers | Otto | ✅ 1-sep-2026. Zona **Active**. NS `casey` + `jewel`. Correo **iPage** (casillas creadas ahí): MX `mx.ipage.com` + SPF `ip4:66.96.128.0/18` + DMARC `p=none`. SMTP/IMAP: `smtp.ipage.com:465` / `imap.ipage.com:993` (cliente/app, no van en CF). **No** Email Routing. `@`/`www` A → `66.96.147.159` (proxied). **No** crear `portal` hasta Coolify. |
| **C3** | SSH o consola Hetzner al CPX31 | Otto | WSL: permission denied. Coolify Terminal OK. Agregar pubkey `szystems@gmail.com` ed25519 a root. |
| **C4** | ¿Coolify ya está instalado? URL + user admin | Otto | ✅ v4.1.2 · `http://5.78.235.235:8000/` · proyecto **REPRO** (vacío, env production). |
| **C5** | Conectar Coolify ↔ GitHub (`szystems/repro`) | Otto | ✅ App **repro-portal** Running (Dockerfile.coolify). Humo `/login` 200 sslip.io. MySQL 8 vacío. No `portal` DNS. |
| **C6** | Password MySQL iPage + copia del `.env` prod | Otto | **Obligatorio copiar el mismo `APP_KEY`.** Si se regenera, se rompen sesiones y datos cifrados. |
| **C7** | SMTP de la app | Otto | Casillas y SMTP en **iPage**: `noreply@reprogt.com` → `smtp.ipage.com:465` SSL. No Cloudflare Email Routing. |
| **C8** | Acceso al sitio **reproxela.com** (FTP/cPanel/WordPress) | Otto / Stephany | No está en este repo. Hace falta para moverlo a `reprogt.com` y poner los links. |
| **C9** | Encender **backups** del VPS en Hetzner | Otto | ✅ ya estaban ON (Disable Backups visible). 7 slots automáticos. |
| **C10** | Casillas `@reprogt.com` | Otto / Stephany | ✅ creadas en **iPage** (`info@` y `noreply@`). Contraseñas en `.env.reprogt-mail` (local, no git). |

---

## Arquitectura objetivo (Coolify)

```
Internet
  ├─ reprogt.com / www     → sitio web (estático o WP en Coolify o el host actual)
  └─ portal.reprogt.com    → Coolify app "repro-portal"
         ├─ servicio PHP 8.3+ (nginx + php-fpm o Nixpacks)
         ├─ MySQL 8 (servicio Coolify, volumen persistente)
         ├─ volumen: storage/app + storage/logs
         ├─ cron: `php artisan schedule:run` cada minuto
         └─ SSL Let’s Encrypt (Coolify / Traefik)
```

**PHP obligatorio:** `pdo_mysql`, `mbstring`, `gd`, `zip`, `xml`/`xmlwriter`, `bcmath`, `exif`, `pcntl`.  
**Límites:** `upload_max_filesize=20M` `post_max_size=20M` `memory_limit=512M` (Word + fotos).  
**Dockerfile actual** (`Dockerfile` en repo): solo **php-fpm :9000**, sin nginx. En Coolify hay que:

- usar **Docker Compose** (app + nginx, como `docker-compose.yml` local), o  
- un Dockerfile de producción (nginx+fpm), o  
- Nixpacks PHP de Coolify con document root `public/`.

`TrustProxies`: hoy `$proxies` está vacío. Detrás de Traefik hay que poner `$proxies = '*'` (o `TRUSTED_PROXIES=*`) **antes** del go-live o HTTPS/cookies fallan.

**No** compartir cookie entre `reprogt.com` y `portal` (`SESSION_DOMAIN` no poner `.reprogt.com`).

---

## Fases (ejecutar en orden)

### M0 — Cerrar huecos (esta semana, antes de tocar prod)

1. Completar C1–C9 de la tabla.  
2. Encender backups VPS.  
3. Bajar TTL DNS cuando exista la zona.  
4. Decidir: ¿Coolify nuevo en el CPX31 o ya hay uno?  
5. Copiar `.env` iPage a un sitio seguro (1Password / nota de Otto). **No commitear.**

### M1 — Dominio y DNS (sin cortar iPage) · **C2 cerrado 1-sep**

`reprogt.com` ya está comprado. DNS lo maneja **Cloudflare**. El correo **no** se crea en Cloudflare: casillas en **Network Solutions**.

#### 1) Agregar el dominio en Cloudflare

1. Add site → `reprogt.com` → plan Free.  
2. Cloudflare muestra 2 nameservers (`xxx.ns.cloudflare.com`).  
3. En el **registrar** donde compró el dominio, cambiar nameservers a esos dos.  
4. Esperar Active (minutos a unas horas).  
5. **No** activar *Email Routing* de Cloudflare (rompe el correo de NS).

#### 2) Correo Network Solutions (obligatorio, nube gris)

En Cloudflare → DNS → Records. Todo lo de mail en **DNS only** (nube **gris**, nunca naranja).

Cloud Mail de NS (producto actual, post 13-jun-2025) — confirmar en el panel NS si su producto es este:

| Tipo | Nombre | Contenido | Prioridad |
|------|--------|-----------|-----------|
| MX | `@` | `mx001.netsol.xion.oxcs.net` | 10 |
| MX | `@` | `mx002.netsol.xion.oxcs.net` | 10 |
| MX | `@` | `mx003.netsol.xion.oxcs.net` | 10 |
| MX | `@` | `mx004.netsol.xion.oxcs.net` | 10 |
| TXT | `@` | `v=spf1 include:spf.cloudus.oxcs.net ~all` | — |

**Un solo SPF.** Si NS muestra otros MX (producto viejo o Microsoft 365), copiar **exactamente** los del panel NS, no inventar.

Opcional: DKIM/autodiscover que NS liste (CNAME o TXT) → mismos valores, nube gris.  
DMARC cuando el correo ya reciba: TXT `_dmarc` = `v=DMARC1; p=none; rua=mailto:info@reprogt.com`

Crear en NS, cuando el MX esté activo: `info@`, `noreply@` (la app), las de Stephany.

#### 3) Web y portal — **aún no apuntar** a Hetzner

No crear `portal` ni el A de `@` hasta tener la IP de Coolify. Si se pone ahora, el dominio abre vacío o mal.  
Cuando exista la IP del VPS:

| Nombre | Tipo | Destino | Proxy |
|--------|------|---------|-------|
| `@` | A | IP VPS (sitio) | naranja OK |
| `www` | CNAME | `reprogt.com` | naranja OK |
| `portal` | A o CNAME | Coolify / Traefik | naranja OK (app) |
| `coolify` | A | IP VPS | **gris** (panel interno) |

TTL 300 s antes del cutover.

| Nombre | Tipo | Destino |
|--------|------|---------|
| `@` | A | IP VPS (sitio) **o** el host actual de reproxela si el web se queda ahí un tiempo |
| `www` | CNAME | `reprogt.com` |
| `portal` | A / CNAME | Coolify / Traefik (app) |
| `coolify` | A | IP VPS (opcional, panel) |
| MX / SPF / DKIM | — | cuando exista correo @reprogt.com |

Mientras `portal.reprogt.com` no tenga SSL + app, **iPage sigue siendo la URL que usa Stephany**.

### M2 — Hetzner + Coolify

1. SSH al CPX31. `apt update`, firewall: 22, 80, 443, (8000 solo si el panel no tiene dominio aún).  
2. Instalar Coolify si falta: script oficial `https://cdn.coollabs.io/coolify/install.sh`.  
3. Crear proyecto **REPRO**.  
4. Servicio **MySQL 8** (`repro_portal`, user dedicado, password fuerte). Volumen persistente.  
5. (Opcional) Redis — hoy `CACHE_DRIVER=file` / `QUEUE_CONNECTION=sync`; no es bloqueante.

### M3 — App en Coolify (vacía, para probar stack)

1. Resource: Application ← GitHub `szystems/repro` branch `master`.  
2. Build: Compose o Nixpacks; docroot `public`.  
3. Env (sin datos de prod todavía): `APP_ENV=production` `APP_DEBUG=false` `APP_URL=https://portal.reprogt.com` + DB del paso M2 + `APP_KEY` **temporal** solo para humo.  
4. Dominio `portal.reprogt.com` + SSL.  
5. Persistent storage → `storage`.  
6. Cron: `* * * * * php artisan schedule:run`.  
7. Humo: `/login` 200, `php -m` tiene `zip` y `xmlwriter`.

**Aún no** importar la BD ni avisar a Stephany.

### M4 — Código que hay que tocar en este repo (antes del cutover)

Cambios chicos, en una rama `migracion/portal-reprogt`:

| Archivo | Qué |
|---------|-----|
| `app/Http/Middleware/TrustProxies.php` | Confiar en el proxy de Coolify |
| `config/cuestionario_ayuda.php` | Texto `portal.reprogt.com/cuestionario/…` |
| `app/Http/Controllers/CuestionarioController.php` | Mismo texto de ayuda (hoy hardcodea `reproappv2.szystems.com`) |
| (opcional) vista mínima `soy-evaluado` | Para el link de la web |
| (opcional) `Dockerfile` / `docker-compose.prod.yml` | Nginx+fpm listo para Coolify |

No hace falta reescribir Word/PCLZip: en el VPS `ZipArchive` existirá; el fallback se queda por si acaso.

### M5 — Copiar datos (ventana 2–4 h, **copia exacta**)

**No empezar el dump hasta que Otto confirme el freeze.** El agente avisa a Otto; Otto avisa a Stephany **o** se desconecta / pone mantenimiento en iPage.

1. **Freeze (obligatorio):** Stephany no crea órdenes ni edita registros, **o** iPage queda inaccesible (mantenimiento / apagar app) para que no escriban mientras copiamos.  
2. Backup iPage: `mysqldump` `dbreprov2` (copia exacta, no schema vacío) + tarball `storage/app` (fotos, papelería, Word, PDFs).  
3. Importar **ese** dump en MySQL Coolify. No `migrate:fresh`. No seeders.  
4. Copiar `storage/app` al volumen. Permisos `www-data`.  
5. Pegar **el mismo** `APP_KEY` y `MAIL_*` del `.env` iPage. `APP_URL=https://portal.reprogt.com`.  
6. `php artisan migrate --force` (solo migraciones pendientes si las hay).  
7. `config:cache` `route:cache` `view:cache`.  
8. Humo interno: login UAT, Word #255 PRUEBA 1, PDF, subida foto, correo de prueba.  
9. Cutover DNS (M6) solo cuando el humo pase **y** iPage ya no reciba escrituras.

### M6 — Cutover DNS

1. `portal.reprogt.com` → app (si no estaba).  
2. En iPage: redirect 301 de `reproappv2.szystems.com/*` → `https://portal.reprogt.com/$1` para que los enlaces viejos de correo sigan vivos.  
3. Humo desde red real: login Stephany (ella), 1 Word, 1 enlace evaluado **nuevo**.  
4. iPage **no se apaga** 48–72 h.

### M7 — Sitio web `reprogt.com`

El marketing **no está en este repo**. Pasos:

1. Con C8: copiar reproxela → `reprogt.com` (mismo host o estático en Coolify).  
2. Redirect `reproxela.com` → `reprogt.com` (cuando Stephany lo pida).  
3. En el menú / hero, dos links claros:

| Texto | Destino |
|-------|---------|
| Ingresar / Empresas | `https://portal.reprogt.com/login` |
| Soy evaluado | `https://portal.reprogt.com/cuestionario` no sirve sin token → página corta “Revise su correo” + WhatsApp sede, **o** `https://portal.reprogt.com/soy-evaluado` cuando exista |

SSO desde la web **no** está previsto. Un `<a href>` basta.

### M8 — Cierre

1. Cron: un recordatorio de prueba y `formulario:auto-transiciones` en log.  
2. Quitar FTP como deploy diario; de acá en adelante **push a `master` → Coolify**.  
3. Apagar iPage a los 7 días si no hay rollback.  
4. Actualizar `CONTEXTO_AGENTES.md` URL de prod.

---

## Rollback

1. DNS `portal` / CNAME de vuelta o bajar TTL y apuntar otra vez a iPage.  
2. Quitar el 301 en iPage.  
3. App iPage intacta (no se borra hasta M8).  
4. Coolify se deja como staging.

---

## Qué no hacer

- `migrate:fresh` / seeders demo en prod.  
- Regenerar `APP_KEY`.  
- Resetear usuarios reales de Stephany.  
- Regenerar Word de NEVERIA / CORALSA / PERCO.  
- Mostrar Encargado / quién programó al portal empresa.  
- Poner el panel Coolify en `reprogt.com` apex.  
- Cortar iPage el mismo día del cutover.

---

## Humo post-cutover (checklist)

- [ ] `https://portal.reprogt.com/login` 200 + candado SSL  
- [ ] Login UAT + login Stephany  
- [ ] Word socio PRUEBA 1 #255 abre sin “recuperar archivo”  
- [ ] Excel calendario (en Hetzner puede ser `.xlsx` real)  
- [ ] Enlace `cuestionario/{token}` de una orden de prueba  
- [ ] Foto / papelería se ven  
- [ ] Correo “enlace enviado” llega  
- [ ] `reprogt.com` muestra la web + los dos links  
- [ ] URL vieja `reproappv2.szystems.com/login` redirige  

---

## Seguimiento

| Fase | Estado | Fecha |
|------|--------|-------|
| M0 Huecos C1–C9 | 🟡 C3 SSH WSL pendiente; C6–C8 faltan | 2-sep-2026 |
| M1 Dominio/DNS | ✅ zona CF Active; no `portal` aún | 1-sep-2026 |
| M2 Coolify + MySQL | ✅ v4.1.2 + repro-mysql | 1-sep-2026 |
| M3 App vacía + SSL | 🟡 login 200 http sslip.io; HTTPS pendiente de cert | 2-sep-2026 |
| M4 Código TrustProxies / textos | 🟡 en working tree (host de APP_URL) | 2-sep-2026 |
| M5 Dump + storage | ⬜ freeze Otto | |
| M6 Cutover + 301 iPage | ⬜ | |
| M7 Web + links | ⬜ | |
| M8 Cierre | ⬜ | |

**Siguiente paso humano:** Otto confirma freeze M5 (Stephany no escribe **o** iPage en mantenimiento). Luego dump `dbreprov2` + `storage/app`. No `portal` DNS hasta M6.
