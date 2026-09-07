# Sprint Q — Observaciones Stephany 7-sep-2026

**Cliente:** Stephany Castro / REPRO  
**Estado:** 🟡 En curso  
**Prod:** https://portal.reprogt.com  
**iPage:** `reproappv2.szystems.com` sigue 503 — no descongelar  
**Evidencia:** `docs/repro/cambios agosto/Observaciones 07-09-2026/WA_09-46_lista_stephany.jpg`  
**Viabilidad (7-sep):** no `migrate:fresh` · no reciclar `poligrafista_id` (Programó) ni `responsable_id` (Encargado) · no resetear claves de Stephany · no regenerar Word NEVERIA/CORALSA/PERCO

**Sprint anterior:** P en prod. Migración M6 + Resend ya live.

---

## Cómo lo dijo ella

| # | Pedido WA | Lectura |
|---|-----------|---------|
| 1 | Autoasignarse para **2 personas** (una entrevista, otra evalúa) | Tercer rol. No cabe en Programó/Encargado. |
| 2 | Informes de empresas: filtros **Sede** y **Fechas** | Fechas ya están. Falta combo Sede en `/reportes/evaluaciones`. |
| 3 | Word: Información complementaria de 10 a **12** | Plantilla está en 11 pt. Subir a 12 **solo** esa tabla. |
| 4 | Correo de nueva orden **solo si la crea el cliente** | Hoy también avisa si la crea REPRO. Gate: `tipo_creador === 'empresa'`. |
| 5 | Historial: columna **Entrevistó** junto a Encargado | Mismo campo que #1. Sin columna vacía hasta que exista. |
| 6a | Informe preliminar: **color** de letra | Quill; el sanitizado hoy quita color. |
| 6b | Informe preliminar: **tablas** | Quill + ampliar sanitizado (no abrir a todo HTML). |
| 6c | Título con **nombre del candidato** | Capturas que mandan a empresas. |
| 7 | Texto de instrucción en historial de empleos (párrafo que ella escribió) | Solo Blade. Candidatos ponen solo el último empleo. |
| 8 | Inventario de correos automáticos | No es código. Queda documentado abajo. |

---

## Lo que NO es

| Se podría leer mal | Lo correcto |
|--------------------|-------------|
| Reusar Encargado o Programó para “quien entrevistó” | Rompe citas y reportes de agosto. Campo nuevo `entrevistador_id`. |
| Filtro sede en “Reporte de Empresas” (`/reportes/empresas`) | Ella dijo **INFORMES DE EMPRESAS** = `/reportes/evaluaciones`. Ahí faltaba el combo. |
| Cambiar fuente de todo el Word a 12 | Solo tabla Información complementaria (`w:sz` 24). |
| Quitar el correo al candidato | El de alta/enlace sigue. Solo se corta `NuevaOrdenSedeMail` cuando crea REPRO. |
| Pintar columna Entrevistó vacía antes del campo | Va junto con Q-A1. |
| Abrir Quill a cualquier HTML | Color + tablas, sanitizado acotado. |
| `migrate:fresh` | Nunca. Q-A1 es migración **aditiva** nullable. |

---

## Matriz Sprint Q

| ID | Pedido | Cambio | Riesgo | Orden |
|----|--------|--------|--------|-------|
| **Q-I1** | Sede + fechas en informes | `<select name="sede_id">` en `evaluaciones.blade.php`. Backend ya filtra. | Bajo | 1 · tests OK |
| **Q-E1** | Texto historial de empleos | Párrafo de ella en `historial-laboral.blade.php` (y peri si aplica) | Bajo | 2 |
| **Q-T1** | Nombre en título preliminar | Header en `ordenes/show.blade.php` | Bajo | 3 |
| **Q-W1** | Complementaria Word 12 | `forzarTamanoFuenteTabla(..., 24)` solo en esa tabla | Bajo | 4 |
| **Q-M1** | Mail nueva orden solo cliente | `notificarUsuariosSede()` si `tipo_creador === 'empresa'` | Bajo | 5 |
| **Q-Q1** | Quill color + tablas | Toolbar + `sanitizarHtmlInforme()` | Medio | 6 |
| **Q-A1** | Dos personas + columna Entrevistó | `entrevistador_id` nullable + Autoasignarme entrevista + historial/Excel | Medio · aditivo | 7 |
| **Q-C1** | Inventario de correos | Documentado aquí. Sin código. | — | Hecho |

---

## Q-C1 — Correos automáticos (lo que pidió)

Remitente prod: **`noreply@reprogt.com`**. Destino = correo de cada ficha. Nadie lee `noreply@`.

| Cuándo | Quién recibe | Canal |
|--------|----------------|-------|
| Cliente crea orden **con sede** (tras Q-M1; hoy también si crea REPRO) | Personal REPRO de esa sede | Correo `NuevaOrdenSedeMail` |
| Se crea/agrega evaluado con email | El candidato | Correo `EvaluadoAsignadoMail` |
| “Reenviar correo” | El candidato | Correo |
| Resultados / preliminar visibles a la empresa | Usuarios de esa empresa | Correo `ResultadosDisponiblesMail` |
| Cron 8:00, faltan 3 o 1 días de enlace | Candidato (cuestionario incompleto) | Correo `RecordatorioCuestionarioMail` |
| Alta de usuario / reset de clave | Ese usuario | Correo `UserMail` / `UserResetPasswordMail` |
| Orden creada | REPRO + empresa | Solo campana |
| Candidato terminó cuestionario | REPRO + empresa | Solo campana (`CuestionarioCompletadoMail` ya no se manda) |

---

## Verificación por ítem

Cada ID: tests PHPUnit → commit/push `master` (Coolify **repro-portal**) → humo en `https://portal.reprogt.com` (navegador). No `migrate:fresh`. Q-A1: `php artisan migrate` en Coolify (aditiva).

**UAT:** PRUEBA 1. No tocar NEVERIA / CORALSA / PERCO.
