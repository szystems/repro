# Sprint J — Observaciones Stephany 19-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Fecha recepción:** 18–19 agosto 2026  
**Estado:** 🟡 UAT PROD 20-ago · J13 plantilla Puesto + J16 estudios extra desplegados · falta re-UAT Stephany (Rebeca/Reyna)  
**Prod:** https://reproappv2.szystems.com  
**Evidencia:** `docs/repro/cambios agosto/Observaciones 19-08-2026/`  
**WA:** 3 capturas (fechas OK · reset BD · Word socio + polígrafo/VSA + preview + roles pospuestos)

**Principio:** 1 ítem = 1 cambio acotado · test antes de deploy · no tocar fechas del formulario (cerradas).

---

## Fuentes

| Archivo | Qué es |
|---------|--------|
| `PRUEBA DE SOCIOECONOMICO PARA SISTEMA.docx` | Word socio Rebeca Mazariegos + notas Stephany |
| `PRUEBA SISTEMA 1.docx` | Word polígrafo/VSA Reyna Ixac + notas Stephany |
| `WhatsApp Image 2026-08-18 at 5.04.00 PM.jpeg` | Encabezado deseado (Empresa / Agencia / Puesto / Fecha en celeste) |
| WA 18-ago | Fechas OK · reset BD sin perder código · preview · botón Word en edición · roles pospuestos |

---

## Cerrado / no codear ahora

| Punto | Decisión |
|-------|----------|
| Fechas formulario | ✅ Cerrado por Stephany |
| Clasificación A/B/C socio | Dejar como está (ella lo confirma) |
| Roles/accesos | Ella lo pospone |
| Deploy / dominio / reset BD | Al final, cuando el código esté listo |

---

## Matriz verificada vs código

| ID | Pedido Stephany | Verificación código | Fix | Archivos | Prioridad |
|----|-----------------|---------------------|-----|----------|-----------|
| **J1** | Presupuesto/bienes/refs laborales: tabla REPRO en blanco, no editable | `$columnasMap` en `tablas-informe-preempleo.blade.php` **no incluye** `presupuesto`, `bienes`, `referencias_laborales` | Añadir las 3 columnas | `tablas-informe-preempleo.blade.php`, `InformePreempleo::columnasParaClaveInforme` | P0 |
| **J2** | No hay dirección de padre; no se pueden agregar hijos/hermanos | `informe-familiar.blade.php`: sin campo `direccion`; hijos/hermanos solo si `@if(!empty($filasTabla))` | Campo dirección + tablas siempre con agregar fila | `informe-familiar.blade.php` | P0 |
| **J3** | Complementaria laboral socio no pasa lo editado | `InformePreempleo::compilarTablas()` no incluye `integridad_01…19` | Clave `labor_complementaria` (socio + preempleo) | `InformePreempleo.php` | P0 |
| **J4** | Refs personales #2 trae datos laborales | `rellenarTablasReferenciasLaboralesSocio()` busca cualquier tabla con «Información brindada por el candidato» + «Empresa:» — choca con refs personales | Exigir también «Puesto que ocupó» / bloque laboral | `InformeWordRelleno.php` | P0 |
| **J5** | Empleos vacíos («los pasó arriba») | Consecuencia de J4 | Mismo fix J4 + relleno `INFORMACIÓN LABORAL` intacto | `InformeWordRelleno.php` | P0 |
| **J6** | Aspectos a considerar = Observaciones de 1ª hoja polígrafo | Encabezado v2 rellena `OBSERVACIONES` pero no `ASPECTOS A CONSIDERAR:` | Copiar `word_observaciones` al marcador socio | `InformeWordRelleno.php` | P0 |
| **J7** | Info complementaria con datos personales mezclados | `filasInformacionComplementaria` cae a secc. 1 si override vacío/mal alineado; `rellenarInformacionComplementariaTabla` escribe por índice | Preferir override `complementaria`; alinear por etiqueta | `InformeWordNarrativas.php`, `InformeWordRelleno.php` | P1 |
| **J8** | Recomendaciones jala notas judiciales internas | `compilarRecomendaciones()` usa `notas_poligrafo` o `antecedentes` | Nueva nota `word_recomendaciones` (opcional, no bloquea cierre) | `InformeWordBloquesEvaluador.php`, `InformeWordNarrativas.php`, `narrativas-word-evaluador.blade.php` | P1 |
| **J9** | Documentos adjuntos al final (después tatuajes) | I1 ya inserta tras `TATUAJES`. En polígrafo hay tatuajes en anexos al final — papelería queda a mitad | Insertar tras última tabla `TATUAJES` (la de anexos) | `InformeWordAnexos.php`, `InformeWordXml.php` | P1 |
| **J10** | Pareja/expareja no traslada; no agregar hijos | Compilación `ResumenFamiliar` OK; UI J2 era el bloqueo de agregar | J2 + verificar override `tiene`/`aplica` al guardar | `InformePreempleo::normalizarFamiliar` | P0 (junto J2) |
| **J11** | Vista previa sigue sin funcionar | Modal + mammoth ya existen (I3). Botón Word solo en el modal | Mantener preview; timeout/error visibles | `edit.blade.php` | P1 |
| **J12** | Descargar Word desde pantalla de edición, sin salir | `informe-word-borrador` existe pero el botón está **dentro del modal** | Botón en barra principal de edición | `edit.blade.php` | P1 |
| **J13** | Encabezado: Puesto + celdas celeste Empresa/Agencia/Fecha | `rellenarEncabezadoV2` solo Empresa/Agencia (fila 0) y Fecha (fila 1); no escribe Puesto | Escribir Puesto en fila 1 si la etiqueta existe | `InformeWordRelleno.php` | P1 |
| **J14** | Salud socio: dejar en blanco (no pasar respuestas candidato) | Narrativa salud cae a respuestas secc. 5 si `word_salud` vacío | En variante socio: solo texto evaluador | `InformeWordNarrativas.php` | P1 |
| **J15** | Layout tatuajes (tabla de referencia) | Plantilla Word; no mezclar con datos | Diferir a ajuste de plantilla si J9 no basta | plantilla `.docx` | P2 |
| **J16** | Académico: no agrega estudios extra | Admin ya tiene `x-tabla-dinamica` con agregar; H10 filtra a 2 niveles del formulario | Permitir filas extra del evaluador (no filtrar overrides) | `InformeWordRelleno::filasAcademicasVisibles` | P1 |
| **J17** | Estado civil editable si el candidato se confundió | Tabla «Información personal» ya tiene Estado civil | Visible; no código extra si J1 no lo oculta | — | P2 UX |
| **J18** | Al guardar aparecen campos de municipio nacimiento | Investigar mezcla personal/complementaria al persistir override | Guardar `personal` y `complementaria` por clave, no mezclar | `InformePreempleo::guardarDesdeRequest` | P1 |
| **Ops** | Reset BD + dominio | Fuera de este sprint de código | Al final | — | Ops |

---

## Orden de implementación (sin deploy)

```
Fase 1 — Admin (desbloquea edición REPRO)
  J1, J2, J10, J3

Fase 2 — Mapeo Word corrupto
  J4, J5, J6

Fase 3 — Polígrafo/VSA + narrativas
  J7, J8, J9, J14, J16

Fase 4 — UX edición
  J12, J11, J13

Fase 5 — Diferido
  J15 plantilla tatuajes · J17 UX · Ops reset/dominio
```

## UAT producción 20-ago

Casos usados (últimos completados en prod, no Rebeca/Reyna): socio **#90** Yengxi Licema · polígrafo **#101** Juan Francisco Cañiz.

| ID | Resultado UAT |
|----|----------------|
| J1 | ✅ Presupuesto/bienes/refs laborales editables en «Tablas para informe» |
| J2/J10 | ✅ Dirección padre/madre · Agregar hijo / Agregar hermano |
| J3/J18 | ✅ Complementaria y complementaria laboral por clave aparte |
| J4/J5 | ✅ Tras hotfix: empleos en EMPLEOS, no en refs personales #2 |
| J6 | ✅ Marcador ASPECTOS A CONSIDERAR presente (vacío si no hay `word_observaciones`) |
| J8 | ✅ Casilla Recomendaciones opcional · Word no copia notas internas |
| J12 | ✅ Botón «Descargar borrador Word» en barra de edición (socio y polígrafo) |
| J13 | ✅ Plantilla con `Puesto:` + etiquetas celeste (7× v2) · relleno en la celda siguiente |
| J16 | ✅ Override académico ya no se filtra (H10 se mantiene sin override) · extras a «Otros:» |
| J11 | ⏸️ Vista previa no re-probada a fondo |
| J15/J17/Ops | ⏸️ Diferidos |

## Regresión a no romper

- Fechas nacimiento / fechas laboradas (formulario)
- Word Franklin #132 / Edgar #131 / papelería I1
- Bloques Word: los 6 actuales siguen obligatorios; recomendaciones es **extra opcional**
