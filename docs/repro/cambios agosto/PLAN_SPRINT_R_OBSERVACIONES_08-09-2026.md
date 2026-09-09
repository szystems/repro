# Sprint R — Follow-up Stephany 8-sep-2026

**Cliente:** Stephany Castro / REPRO  
**Estado:** ✅ Cerrado prod 8-sep-2026 · **esperando si ella manda más observaciones**  
**Prod:** https://portal.reprogt.com  
**iPage:** `reproappv2.szystems.com` sigue 503 — no descongelar  
**PDF guía (fuente ella):** `docs/Guia de usuario/GUIA DE USUARIO SIGOR.pdf`  
**Servida en portal:** `resources/ayuda/guia-usuario-sigor.pdf`  
**Mensaje WA:** Otto ya tiene el texto en pasado (“ya quedó”). No reenviar como “vamos a…”.

**Sprint anterior:** Q cerrado prod 7-sep.  
**Siguiente:** no codear más hasta nueva lista de Stephany.

**No:** `migrate:fresh` · resetear claves de Stephany · regenerar Word NEVERIA/CORALSA/PERCO · Coolify Upgrade · tocar Asonata / Clínicas del Valle / ControClinic.

---

## Cómo lo dijo ella (inventario WA 8-sep)

| # | Pedido | Lectura / resultado |
|---|---------|---------------------|
| 1 | Quitar recuadro **“30 días restantes”** del correo | Quitado en `emails/recordatorio-cuestionario.blade.php` |
| 2 | Vigencia del enlace **15 días** | `Config::MIN_DIAS_VIGENCIA_ENLACE = 15`. BD `configs.dias_vigencia_token` **31 → 15** (tinker post-deploy 175). Enlaces **nuevos** y al rehabilitar = 15 días. **Los tokens ya emitidos conservan su vencimiento.** |
| 3 | Quitar **fecha límite** del correo | Quitada en recordatorio + `evaluado-asignado.blade.php` |
| 4 | Poner **puesto que solicita** | En el correo al candidato |
| 5 | WhatsApp al candidato en el listado | Icono + botón verde en `admin/cuestionarios/index` y show empresa |
| 6–8 | Guía SIGOR en Centro de Ayuda, ver en navegador, no auto-descarga | Artículo `guia-usuario-sigor`. PDF `inline`. Descargar solo si eligen. |
| 9 | PDF usuarios falla; quiere **Excel** | `GET /excel-users` (`users.excel`). PDF se dejó. |
| 10 | No pueden subir foto usuario/empresa | `PerfilImagenSupport` escribe a `public_path('assets/imgs/...')`. El `move('assets/...')` viejo fallaba en Docker (CWD). Código en prod; no se hizo humo con file picker real. |
| 11 | ¿Qué es **Empresa QZ Temporal**? ¿Lo dejo? ¿Por qué no lo borro? | **Solo explicación.** Rol Spatie `empresa_qa_temporal`. El panel REPRO lo abre `users.role_as` (3 admin, 2 REPRO, 1 empresa). No se borra porque tiene **1 usuario**. Reasignar a `empresa` y luego sí. No mover usuarios de PRUEBA 1 que ya funcionan. |
| 12 | Al crear usuario **desde la empresa** no llega correo | `EmpresaController::guardarUsuario` manda `UserMail` + asigna Spatie `empresa` si existe. |

Banner URGENTE el **último día** del token se dejó (sin countdown grande). Recordatorios: cron 08:00 `notificaciones:recordatorios --dias=3,1 --despues-alta=1`. Se saltan si completó / enlace off / orden cancelada.

---

## Matriz Sprint R

| ID | Pedido | Estado |
|----|--------|--------|
| **R-M1** | Correo candidato: sin “30 días”, sin fecha límite, sí puesto | ✅ prod · commit `d07339d7` · deploy Coolify **175** |
| **R-V1** | Enlace 15 días | ✅ prod · min 15 + BD 15 |
| **R-W1** | WhatsApp en listado candidatos | ✅ prod · `d07339d7` |
| **R-X1** | Excel usuarios | ✅ prod · `GET /excel-users` 200 ~11 KB |
| **R-F1** | Fotos usuario/empresa | ✅ código prod · que ella pruebe de nuevo |
| **R-U1** | Correo al crear usuario desde empresa | ✅ prod |
| **R-G1** | Guía SIGOR en Centro de Ayuda (ver + descargar opcional) | ✅ prod · commit `fd5a40b3` · deploy Coolify **176** |
| **R-Q1** | Rol Empresa QZ Temporal | ℹ️ respuesta WA, sin código |

---

## Guía SIGOR (R-G1)

- Fuente: PDF que ella envió (20 pág., ~4.2 MB).
- Rutas (antes de `ayuda/{slug}`):
  - `GET /ayuda/archivo/guia-usuario-sigor.pdf` → `ayuda.guia-sigor` · `Content-Disposition: inline`
  - `GET /ayuda/archivo/guia-usuario-sigor/descargar` → `ayuda.guia-sigor.descargar` · `attachment`
- Artículo: `https://portal.reprogt.com/ayuda/guia-usuario-sigor` · audiencia repro + empresa · destacado.
- Vista: iframe + “Abrir en el navegador” + “Descargar PDF”.
- FAQ: “¿Dónde está la guía de usuario SIGOR?”

**Humo prod 8-sep:** `/ayuda` 200 con la tarjeta. Artículo 200. PDF `inline` + magic `%PDF-`. Descarga `attachment`.

---

## Deploy

| Commit | Coolify | Qué |
|--------|---------|-----|
| `d07339d7` | **175** | Correos 15 días, WhatsApp, Excel, fotos, UserMail empresa |
| `fd5a40b3` | **176** | Guía SIGOR |

App **repro-portal** UUID `ot2bqftjjqp9d2l2ez7awoha` (id **5**). Tras deploy: `php artisan view:clear` en el contenedor `ot2bqft*`.

**UAT navegador:** `uat.g1.browser@repro.local` / `UAT.G1Word2026!` · user **id 271** · `role_as=3` · “UAT G1 Browser”. No es Stephany. El `fill` del password a veces manda vacío: escribir lento o setear por JS.

---

## Qué NO reabrir

- Tokens ya emitidos: no se recortan a 15 días.
- PDF de usuarios: se dejó; ella pidió Excel.
- Rol QZ Temporal: no borrar ni “arreglar” con código.
- iPage 503 / freeze: no quitar.
- Coolify Upgrade / `force_docker_cleanup` / auto-update.

---

## Centro de Ayuda (revisión 8-sep, post-cierre)

Actualizado contra el portal (sin capturas de candidatos reales):

- Artículo nuevo `correos-automaticos` (inventario Q-C1 + lote R).
- Flujo: 15 días + WhatsApp (ya no «30 días»).
- Artículos tocados: inicio REPRO/empresa, cuestionarios, usuarios empresa, seguridad (Excel/foto/QZ), empresas, detalle orden (Programó/Encargado/Entrevistó + Quill), calendario Excel, reportes sede, estado de procesos, seguimiento, crear orden, resultados, Word 12 pt, config.
- FAQ/glosario alineados.

## Siguiente paso

Esperar observaciones nuevas de Stephany. Si llegan, abrir **Sprint S** (no reabrir R ni Q). No `migrate:fresh`.
