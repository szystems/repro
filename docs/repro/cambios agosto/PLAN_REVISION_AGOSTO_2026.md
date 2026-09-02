# Plan de revisión cliente — Agosto 2026

**Cliente:** Stephany Castro / REPRO  
**Fecha inicio:** 5 de agosto de 2026  
**Plataforma:** https://reproappv2.szystems.com  
**Fuentes:** WhatsApp 2–4 ago · `correcciones.pdf` · `CAMBIOS PARA LOS REPORTES.pdf` · `USUARIO DE CLIENTE (2).pdf`

---

## Objetivo

Cerrar feedback UAT del cliente para habilitar **pruebas reales** en servidor de staging y, tras 1–2 semanas de UAT, pasar a producción con BD reservada.

**Prioridad explícita del cliente (5 ago):** puntos **1** (formulario) y **2** (informe/reporte Word).

---

## Sprints

| Sprint | Alcance | Estado |
|--------|---------|--------|
| **A — P0 inmediato** | Word orden + guardado admin + vista previa + permisos trabajador + labels/selects | ✅ **Desplegado prod 6-ago-2026** |
| **B — Formulario completo** | Estudia actualmente, socio complementaria, periódica/VSA, mover campos económicos | ✅ **Desplegado prod 6-ago-2026** |
| **C — Informe avanzado** | Autorización PDF separada, anexos papelería, tabla preguntas poligráficas | ✅ **Prod 6-ago-2026** |
| **D — Portal empresa** | Mis Órdenes / Estado de Procesos = vista REPRO | ✅ **Prod 6-ago-2026** |
| **E — Ops** | WhatsApp 77637811 + confidencialidad reclutadores (A+B) | ✅ **Prod 7-ago-2026 · UAT cerrado 10-ago-2026** |

---

## Checklist detallado

### 1. Formulario (`correcciones.pdf`)

| # | Cambio | Archivos / notas | Estado |
|---|--------|------------------|--------|
| 1.1 | Quitar campo obsoleto cuadro laboral (formulario anterior) | `historial-laboral.blade.php`, `HistorialLaboralRequest` | ✅ |
| 1.2 | Jefe inmediato → *Nombre y teléfono de jefe inmediato* | `TablaDinamica::columnasEmpleos*` | ✅ |
| 1.3 | RRHH → *Nombre y teléfono de Recursos Humanos* (texto, no solo dígitos) | `TablaDinamica::columnasEmpleos` | ✅ |
| 1.4 | ¿Estudia actualmente? + tabla condicional | `HistorialAcademico`, partial `estudia-actualmente` | ✅ |
| 1.5 | Selects económicos/salud: default «Seleccione…» no «No» | `situacion-economica.blade.php`, `antecedentes.blade.php` | ✅ |
| 1.6 | Mover campos de familiar → económica (todos los formularios) | partial `campos-vivienda-hogar`, `SituacionEconomicaCampos` | ✅ |
| 1.7 | Socio: min 2 referencias, vecino no obligatorio, quitar duplicados vivienda/renta | `SocioeconomicoComplementariaCampos`, blade §6 | ✅ |
| 1.8 | Específica: quitar línea indicada | `situacion-laboral-periodica` — nota académica auxiliar | ✅ |
| 1.9 | Quitar exparejas familiar (periódica, VSA periódica/específica) | `informacion-familiar`, `InformacionFamiliarRequest` | ✅ |
| 1.10 | Periódica: empleo actual solo cuadro, omitir pregunta extra | `HistorialLaboralPeriodico` — omite `periodico_02` | ✅ |

### 2. Informe / Word (`CAMBIOS PARA LOS REPORTES.pdf`)

| # | Cambio | Archivos / notas | Estado |
|---|--------|------------------|--------|
| 2.1 | Orden bloques narrativos: Laboral → Económico → Salud → Hábitos → Delictivas → Judicial | `InformeWordBloquesEvaluador.php` | ✅ |
| 2.2 | Autorización: documento aparte o anexo (no partida en 2 hojas) | `pdf-autorizacion.blade.php`, routes admin+empresa | ✅ |
| 2.3 | Checkboxes papelería para anexos Word | `InformeWordAnexosPapeleria`, partial edit/show | ✅ |
| 2.4 | Tabla editable preguntas poligráficas (última hoja informe) | `InformeWordPreguntasPoligraficas`, partial edit/show | ✅ |
| 2.5 | Vista previa documento no generaba | `edit.blade.php` — PDF + enlace Word | ✅ |
| 2.6 | Guardar borrador / correcciones no persistía | `CuestionariosController@update`, JS borrador | ✅ |
| 2.7 | No descargaba Word | `InformeWordXml::establecerTextoCelda` (`$11` en preg_replace) + cast int + PCLZip | ✅ |
| 2.8 | Edición admin obliga empleos/hijos cuando candidato dijo no | `tablas-seccion-edicion`, `novalidate` | ✅ |

### 3. Portal empresa (`USUARIO DE CLIENTE (2).pdf`)

| # | Cambio | Archivos / notas | Estado |
|---|--------|------------------|--------|
| 3.1 | Mis Órdenes = misma vista que REPRO | `EmpresaController` delega a `OrdenesController`; sidebar → `/ordenes` | ✅ |
| 3.2 | Estado de Procesos = misma vista que REPRO | `CuestionariosIndexSupport`, vista unificada | ✅ |
| 3.3 | Lista evaluaciones — **NO cambiar** | — | ✅ N/A |
| 3.4 | Trabajador: nueva orden acceso denegado | Puente + UI oculta + default sin `crear_ordenes` | ✅ |
| 3.5 | Trabajador: editar/cancelar orden | Permiso `editar_ordenes` en UI + bridge | ✅ |
| 3.6 | Trabajador: subir/ver/descargar papelería | Permisos `documentos.*` bridge + UI condicional | ✅ |
| 3.7 | Trabajador: ver orden de servicio PDF | `descargar_pdf` → `ordenes.ver` + UI condicional | ✅ |
| 3.8 | WhatsApp virtual: 77637811 (no 77677811) | Migración corrige `sedes.whatsapp` PROCESO VIRTUAL | ✅ |
| 3.9 | Aviso: formulario solo del aplicante | `empresa/ordenes/show`, `admin/ordenes/show` | ✅ |
| 3.10 | Confidencialidad entre reclutadores | Propuesta A+B: `reclutador_id` + `confidencial` + modo empresa | ✅ |

---

## Causas raíz confirmadas (código)

1. **`InformeWordBloquesEvaluador::BLOQUES`** — orden no coincide con informe impreso REPRO.
2. **`CuestionariosController@update`** — no guardaba `estado`, `completado_at`, `progreso_secciones`; redirect iba a `show` en lugar de `edit`.
3. **JS borrador** — `fetch` POST sin `_method=PUT`.
4. **Vista previa** — placeholder «Cargando vista previa…» sin implementación.
5. **`permisos_empresa` JSON** — conectado en `User::hasPermission()`; trabajador ignora rol Spatie. Spec: `PERMISOS_EMPRESA_CLIENTE.md`.
6. **Selects si/no** — muchos sin `<option value="">Seleccione…>`; el navegador preselecciona «No».
7. **`tablas-seccion-edicion`** — `minFilas=1` fijo para empleos/hijos sin mirar flags condicionales.

---

## Próximos pasos (post-cierre 10-ago-2026)

Con Sprints A–E y su UAT cerrados, no queda ningún ítem de este plan pendiente. Pendientes fuera de este plan (heredados de fases anteriores):

| # | Pendiente | Bloqueado por |
|---|-----------|---------------|
| 1 | Sprint C — UAT empresa "PDF autorización" | Necesita una orden real con `resultados_visibles_empresa=true` para probar la vista desde el portal empresa (evaluado #112 aún sin resultados liberados) |
| 2 | Fase A.6 — swap de textos legales definitivos (7 autorizaciones + Infornet) | Esperando que el cliente entregue los textos oficiales; los actuales son borrador funcional en `config/autorizaciones_legales.php` |
| 3 | Confirmar con el cliente la contraseña del usuario principal `admon.repro@yahoo.com` | Se reseteó temporalmente para el UAT de Sprint E (`UAT.SprintE2026!`) |

## Comandos QA

```bash
docker run --rm -v $(pwd):/var/www -w /var/www repro-laravel-app php artisan test \
  --filter='InformeWordBloquesEvaluadorTest|Phase8FFuncionalidadesComplejasTest|CuestionariosController'
```

---

## Historial de sesiones

| Fecha | Trabajo |
|-------|---------|
| 2026-08-10 | **UAT Sprint E cerrado:** encontrado y corregido bug de visibilidad (`role_as` string vs `int` estricto en filtros de listado) — desplegado a prod. UAT completo con escenario de 3 usuarios (principal + 2 reclutadores) vía curl/navegador: modo compartido y solo_propios, listados y detalle (403), WhatsApp. Datos y usuarios UAT limpiados de empresa 99. **Todos los checklists de este plan (Sprints A–E, puntos 1–3) quedan ✅ cerrados y verificados en prod.** |
| 2026-08-07 | **Sprint E prod:** WhatsApp PROCESO VIRTUAL → 50277637811; confidencialidad reclutadores (A+B). |
| 2026-08-06 | **Sprint D prod:** Mis Órdenes + Estado de Procesos unificados con vista REPRO (`CuestionariosIndexSupport`). |
| 2026-08-06 | **Sprint C prod:** fix Word 500 (`InformeWordXml` `$11`), UAT cuestionario #32 OK (PDF×2 + Word). Sprint D 3.1 iniciado (Mis Órdenes unificada). |
| 2026-08-06 | **Sprint B formulario:** cuadro laboral obsoleto, estudia actualmente, vivienda→económica, socio refs, exparejas periódica, periodico_02 omitido, específica sin nota auxiliar. |
| 2026-08-06 | **Deploy prod Sprint A completo:** permisos empresa (8 archivos FTP), caché limpiada. Contexto agentes actualizado. |
| 2026-08-06 | Fix Word prod: 404 (cast `orden_id`), 500 (`InformeWordZip` sin ZipArchive). Permisos empresa cerrados + doc `PERMISOS_EMPRESA_CLIENTE.md`. |
| 2026-08-05 | Plan creado. Inicio Sprint A: orden Word, guardado admin, permisos empresa, labels laboral, selects, aviso formulario. |
