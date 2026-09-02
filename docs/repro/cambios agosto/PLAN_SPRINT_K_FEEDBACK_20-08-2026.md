# Sprint K — Feedback Stephany 20-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Evidencia:** `docs/repro/cambios agosto/Ultimos cambios 20-08-2026/`  
**Prod:** https://reproappv2.szystems.com · empresa de pruebas **PRUEBA 1**  
**No tocar:** fechas del formulario · reset BD / dominio · plantillas Word (lote P0/P1 después de K-A/B/C)

---

## Esta sesión (K-A / K-B / K-C)

| ID | Pedido | Qué hacemos |
|----|--------|-------------|
| **K-A** | PDF cuestionario no visible al cliente | Ocultar en Estado de procesos, detalle y reportes. 403 en la ruta. Quedan: detalle, PDF orden, PDF autorización, informe final. |
| **K-B** | Usuario empresa creado desde REPRO sin titular = trabajador | `principal=0` + JSON default trabajador. No gestiona usuarios. |
| **K-C** | Atajo Nueva Orden en menú REPRO | Ítem junto al listado si tiene `ordenes.crear` (admin siempre). |

**Extra alineado al chat (sin cambiar la regla):** lápiz de editar orden en listado usa `role_as`, no `hasAnyRole('repro')`. Cliente: solo **Orden Recibida**.

## P0 Word (prod + UAT 21-ago)

Pie CONFIDENCIAL del cuerpo (queda el footer), padres socio vía `PADRES:`, complementaria completa y sin filas de aspectos generales. Tests `InformeWordSprintKSocioP0Test`.

## P1 Word (prod 21-ago)

Expareja en texto (`DATOS DE EXPAREJA`), domicilio desde tabla 6, presupuesto en Q + filas/concepto extra, deudas + obs económicas, salud en fila Observaciones extra. Tests `InformeWordSprintKSocioP1Test`. Suite InformeWord 91 OK.

## Explicar (no es bug de portal cliente)

Desactivar titular: **REPRO → Usuarios** (tacho), no en «Usuarios de la empresa».
