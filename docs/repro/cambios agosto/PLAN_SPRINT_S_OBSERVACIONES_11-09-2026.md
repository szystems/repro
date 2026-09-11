# Sprint S — Correos de resultados / reclutador (11-sep-2026)

**Cliente:** Stephany Castro / REPRO  
**Estado:** 🔧 Código en rama · falta deploy Coolify  
**Prod:** https://portal.reprogt.com  
**iPage:** `reproappv2.szystems.com` sigue 503 — no descongelar  

**Sprint anterior:** R cerrado prod 8-sep.  
**No:** `migrate:fresh` · resetear claves de Stephany · regenerar Word NEVERIA/CORALSA/PERCO · Coolify Upgrade.

---

## Cómo lo dijo ella

| # | Pedido | Lectura |
|---|---------|---------|
| 1 | Hay reclutador → correo solo a él, aunque no sea confidencial | Destinatario ≠ visibilidad |
| 2 | Sin reclutador y la creó la empresa → solo quien la creó | `tipo_creador=empresa` + `creado_por` |
| 3 | La creó REPRO y no hay reclutador → “la empresa” | Titular (`principal=1`). Si no hay, `empresas.email` |
| 4 | Si es confidencial, no avisar a quien no puede ver el proceso | Filtro `puedeVerOrden` encima de 1–3 |
| 5 | Reclutador y confidencial independientes | Ya lo eran en SIGOR. El hueco era el blast de correo |
| 6 | El gerente no debe llevarse correo automático si no es el responsable | El gerente sigue viendo en el portal |
| 7 | “No me llegan correos” | **Ops:** tope Resend 100/día. No es esta matriz. Subir plan o SMTP iPage |

Ejemplo de ella: orden normal, todos la ven en SIGOR, hay reclutador, resultados/informe **solo** a esa persona.

---

## Matriz Sprint S

| ID | Pedido | Cambio | Estado |
|----|--------|--------|--------|
| **S-M1** | Correo/campana de resultados al responsable | `DestinatariosCorreoEmpresaSupport` en `notificarResultadosDisponibles` + preliminar | 🔧 |
| **S-V1** | Campos independientes | Copy del form + Centro de Ayuda. No se toca `puedeVerOrden` | 🔧 |
| **S-C1** | No filtrar mal campanas | Orden creada / evaluado / cuestionario: `usuariosVisiblesEmpresa` | 🔧 |

**No se toca:** correo al candidato, cita, recordatorio, `NuevaOrdenSedeMail`, visibilidad de listados.

---

## Verificación

PHPUnit: `DestinatariosCorreoEmpresaTest` + Centro de Ayuda + `NotificacionesInAppTest`.  
UAT: PRUEBA 1. Liberar resultados con reclutador asignado → un solo correo. Gerente ve la orden, no el mail.

**UAT navegador:** `uat.g1.browser@repro.local` / `UAT.G1Word2026!` · user **271**.
