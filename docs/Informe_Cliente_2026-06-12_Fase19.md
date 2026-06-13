# Informe de Cambios — REPRO Junio 2026 (Fase 19 — Ajustes confirmados por cliente)

**Actualización lista para despliegue — 12 de junio de 2026**  
**Plataforma:** https://reproappv2.szystems.com  
**Base:** Respuestas del PDF *REPRO propuesta de cambios y preguntas 11 Junio 2026*

---

Este documento describe los cambios de **Fase 19**, implementados según las respuestas formales del cliente del 11/06/2026. Complementa el informe de Fase 18 (10/06/2026). Cada sección explica **qué cambió**, **por qué** y **cómo comprobarlo**.

---

## Contexto — ¿Qué pidió el cliente?

Tras el despliegue de Fase 18, el cliente revisó el sistema y confirmó **9 temas + ajustes menores**, con estas decisiones clave:

| Tema | Decisión del cliente |
|------|----------------------|
| Bloqueo formulario → **En Proceso** | **Mantener** — sigue siendo requisito |
| Bloqueo formulario → **Programar Virtual** | **Quitar** — ya no exige formulario al agendar |
| Bloqueo cita → **En Proceso** | **Mantener** — debe haber estado Programado |
| Capacidad de sede | Usar campo **Capacidad** editable en Sedes |
| Mismo evaluador, misma hora | **Permitido** — se quitó el límite por poligrafista |
| Eliminar órdenes | **Archivar** (no borrar expediente) |
| Historial para empresa | **Visible con observaciones** (interruptor ON por defecto) |
| Historial por DPI | También buscar por **nombre y apellidos** |
| Duplicación al editar orden | **Corregir** (caso ORD-2026-0010) |

**Solicitud adicional:** búsqueda por DPI o nombre en el **dashboard del cliente**.

---

## 1. Corrección: duplicación al editar orden

### ¿Qué cambió?

Al editar una orden y modificar datos de un candidato (nombre, DPI, dirección, modalidad Presencial ↔ Virtual, etc.), el sistema **ya no crea un candidato duplicado**. Conserva estados, cuestionario completado y tokens del registro original.

### ¿Por qué?

El cliente reportó el caso `ORD-2026-0010` (*PRUEBA NUMERO PRUEBA DUPLICACIÓN*): al guardar cambios aparecía un segundo registro del mismo candidato.

### ¿Cómo comprobarlo?

1. Abrir una orden con candidatos en distintos estados.
2. Editar nombre, DPI o modalidad de un candidato existente.
3. Guardar y verificar que **sigue habiendo un solo registro** con los estados anteriores intactos.

---

## 2. Calendario: capacidad por sede

### ¿Qué cambió?

El límite de citas simultáneas ahora depende del campo **Capacidad** de cada sede (editable en **Sedes**), no del poligrafista asignado.

- Si la sede tiene capacidad 3, pueden existir **3 citas a la misma hora** en esa sede.
- La **4.ª cita** en el mismo horario muestra un mensaje claro de capacidad agotada.
- Un **mismo evaluador** puede tener varias citas a la misma hora (ya no se bloquea por poligrafista).

### ¿Por qué?

El cliente configura la capacidad por sede según sus instalaciones y necesita flexibilidad para asignar el mismo poligrafista en varios bloques.

### ¿Cómo comprobarlo?

1. En **Sedes**, poner capacidad = 3 en una sede de prueba.
2. Programar 3 candidatos a las 9:00 en esa sede → deben aceptarse.
3. Intentar una 4.ª a las 9:00 → debe rechazarse con mensaje de capacidad.
4. Programar 2 citas a la misma hora con el **mismo poligrafista** → deben aceptarse si la sede tiene capacidad.

---

## 3. Virtual: programar sin formulario completado

### ¿Qué cambió?

Para candidatos en modalidad **Virtual**, ya **no** se exige tener el formulario en *Completado y recibido* para **programar** la cita en el calendario.

### ¿Qué NO cambió (importante)?

- Pasar la evaluación a **En Proceso** **sigue exigiendo** formulario completado (regla S4).
- Pasar a **En Proceso** **sigue exigiendo** haber estado **Programado** (regla S5).
- Los selectores de estado siguen mostrando **solo el siguiente paso válido**.

### ¿Cómo comprobarlo?

1. Candidato Virtual con formulario *Link Enviado* (sin completar).
2. Programar cita → debe **permitirse**.
3. Intentar cambiar Evaluación a *En Proceso* sin formulario → debe **bloquearse**.

---

## 4. Etiquetas “Estado de…” en listados y reportes

### ¿Qué cambió?

Columnas y filtros ambiguos ahora usan nombres explícitos:

- **Estado de Orden**
- **Estado de Formulario**
- **Estado de Programación**
- **Estado de Evaluación**

Aplica en listado de órdenes, reportes, cuestionarios, dashboard y exportaciones PDF.

### ¿Cómo comprobarlo?

Revisar **Órdenes**, **Reportes → Evaluaciones**, **Gestión de Cuestionarios** y PDFs: las columnas deben mostrar los nombres anteriores.

---

## 5. Gestión de Cuestionarios y reportes

### ¿Qué cambió?

- **Gestión de Cuestionarios** lista candidatos activos con las 3 columnas de estado + **progreso del cuestionario**.
- **Reporte de evaluaciones** alineado con la tabla ESTADOS.pdf (3 estados + teléfono).
- **Calendario:** badge de inasistencia corregido (usa estado de programación).
- **Menú:** Informes ubicado bajo Órdenes de Evaluación.
- **Programar cita:** poligrafista y responsable **opcionales**.
- **Ayuda en orden:** texto que indica que la modalidad se guarda al *Guardar orden* o al *Programar*.

### ¿Cómo comprobarlo?

1. **Cuestionarios** → ver listado con evaluados y progreso.
2. **Calendario** → candidato con inasistencia muestra borde/badge correcto.
3. Programar sin elegir poligrafista → debe permitirse.
4. Sidebar → **Informes** bajo Órdenes.

---

## 6. Historial visible para la empresa (cliente)

### ¿Qué cambió?

Las empresas (clientes) pueden ver el **historial de cambios de estado** de cada candidato en el detalle de su orden, **incluyendo observaciones/motivos**.

- Interruptor en **Configuración → Catálogos** (solo admin REPRO): *Historial visible para empresa*.
- **Por defecto: activado (ON)**.
- Si se desactiva, la empresa vuelve a no ver el historial (solo REPRO).

El historial de **Estado de Orden** interno no se muestra a la empresa.

### ¿Cómo comprobarlo?

1. Ingresar como admin → **Configuración** → verificar interruptor ON.
2. Ingresar como empresa → abrir una orden → expandir candidato → ver panel **Historial de estados** con fechas y observaciones.
3. Desactivar interruptor → la empresa ya no debe ver el panel.

---

## 7. Historial por DPI o nombre (admin)

### ¿Qué cambió?

En **Historial por DPI o nombre** (antes solo DPI), se puede buscar por:

- DPI de 13 dígitos
- Nombre y/o apellidos (mínimo 2 caracteres)

Resultados agrupados si hay homónimos.

### ¿Cómo comprobarlo?

1. Ir a **Historial por DPI o nombre** en el menú.
2. Buscar por nombre parcial (ej. *PRUEBA*) → deben aparecer coincidencias.
3. Buscar por DPI completo → mismo comportamiento que antes.

---

## 8. Archivar órdenes (solo administrador)

### ¿Qué cambió?

- El botón **Eliminar orden** fue reemplazado por **Archivar orden** (solo usuarios admin REPRO, `role_as >= 3`).
- Las órdenes archivadas **no se borran**: conservan candidatos, cuestionarios e historial.
- No aparecen en listados normales; el admin puede filtrar **Órdenes archivadas**.
- La **empresa no ve** sus órdenes archivadas.
- Se registra quién y cuándo archivó.

### ¿Cómo comprobarlo?

1. Como admin, abrir orden en proceso → **Archivar** → confirmar con código de orden.
2. Listado normal → la orden desaparece.
3. Filtro *Archivadas* → la orden reaparece.
4. Como empresa → la orden archivada no es accesible.
5. Colaborador REPRO → no puede archivar.

---

## 9. Búsqueda en dashboard del cliente

### ¿Qué cambió?

En el **dashboard de la empresa** (`/dashboard`), nuevo buscador:

- Por **DPI** (13 dígitos) o **nombre/apellidos**
- Busca en todas las órdenes activas de esa empresa
- Tabla de resultados con enlace a la orden correspondiente

### ¿Cómo comprobarlo?

1. Ingresar como usuario empresa.
2. En el dashboard, buscar un candidato por nombre o DPI.
3. Verificar resultados y enlace a la orden.
4. Confirmar que no aparecen candidatos de otras empresas.

---

## Resumen de cambios Fase 19

| # | Cambio | Estado |
|---|--------|--------|
| 1 | Fix duplicación al editar orden | ✅ Listo |
| 2 | Capacidad por sede; sin límite por poligrafista | ✅ Listo |
| 3 | Virtual: programar sin formulario (S2 eliminado) | ✅ Listo |
| 4 | S4/S5 activos (En Proceso exige formulario + cita) | ✅ Verificado |
| 5 | Etiquetas “Estado de…” | ✅ Listo |
| 6 | Cuestionarios: 3 estados + progreso | ✅ Listo |
| 7 | Reportes, calendario inasistencia, sidebar, campos opcionales | ✅ Listo |
| 8 | Historial visible empresa (default ON) | ✅ Listo |
| 9 | Historial búsqueda por nombre | ✅ Listo |
| 10 | Archivar órdenes (solo admin) | ✅ Listo |
| 11 | Búsqueda DPI/nombre en dashboard cliente | ✅ Listo |

---

## Checklist de verificación post-despliegue

- [ ] Editar orden (caso tipo ORD-2026-0010) sin duplicar candidato
- [ ] Sede cap=3 → 3 citas a las 9:00 OK; 4.ª con error claro
- [ ] Mismo evaluador, 2+ citas misma hora: permitido
- [ ] Virtual: programar sin formulario completado
- [ ] En Proceso: bloquea sin formulario y sin haber estado Programado
- [ ] Empresa ve historial con observaciones
- [ ] Admin archiva orden; empresa no la ve
- [ ] Historial por nombre y búsqueda en dashboard empresa
- [ ] Listados con “Estado de Orden”, “Estado de Formulario”, etc.

---

## Despliegue técnico (referencia interna)

**Migraciones nuevas** (ejecutar en producción):

```bash
php artisan migrate
```

Archivos:

- `2026_06_10_120000_add_historial_visible_empresa_to_configs_table.php`
- `2026_06_10_120001_add_archivada_fields_to_ordenes_table.php`

**Después del deploy:**

```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear
```

Guía FTP: `docs/deployment/IPAGE_DEPLOY.md`

---

*Fase 19 — Ajustes confirmados 11/06/2026 · Documento preparado 12/06/2026*
