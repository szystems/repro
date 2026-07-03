# Cotización de desarrollos adicionales — REPRO Guatemala

**Fecha:** 17 de junio de 2026  
**Para:** Licda. Estephany Castro — REPRO Guatemala  
**De:** Otto Szarata · Szystems  
**Referencia:** Mejoras solicitadas junio 2026  

---

## Resumen en una página

| | |
|---|---|
| **Proyecto original (plataforma completa)** | Q 22,000.00 |
| **Saldo pendiente del proyecto original** | Q 10,000.00 |
| **Esta cotización** | Desarrollos **nuevos y opcionales** — ustedes eligen qué contratar |

### Importante: el sistema ya se puede usar hoy

La plataforma **https://reproappv2.szystems.com** está **en producción**. Órdenes, formularios, estados, calendario, notificaciones y PDFs **ya funcionan**.

Esta cotización es solo para **mejoras opcionales** que pidieron después. **No necesitan esperar** estos desarrollos para empezar a operar; pueden usar el sistema ahora y los extras se van activando cuando estén listos.

### Opciones adicionales (elija las que necesite)

| # | Desarrollo | Inversión | Tiempo de desarrollo* |
|---|------------|----------:|:--------------------:|
| **1A** | Agregar servicio a orden ya creada *(versión práctica)* | **Q 2,800** | **1–2 semanas** |
| **1B** | Agregar servicio a orden ya creada *(versión completa)* | **Q 5,200** | **3–4 semanas** |
| **2** | Informe para la empresa cliente en **Word editable** | **Q 1,600** | **3–5 días hábiles** |
| **3** | Notificaciones automáticas por **WhatsApp** | **Q 3,800** | **1–2 semanas** + aprobación Meta† |

\*Tiempo desde anticipo y definición de alcance, hasta instalación en producción.  
†WhatsApp: la programación toma 1–2 semanas; Meta puede tardar **días adicionales** en aprobar plantillas (no depende de nosotros).

### Paquete sugerido (descuento por contratar los 3 juntos)

Si contratan **Agregar servicio (1A) + Word (2) + WhatsApp (3)** en un solo paquete:

| Concepto | Precio individual | **Paquete** |
|----------|------------------:|:-----------:|
| 1A + 2 + 3 | Q 8,200 | **Q 7,500** |

*(Ahorro de Q 700 al tomar el paquete operativo completo.)*

### ⚠️ Ítem 1: elija **1A o 1B** — no son dos servicios aparte

| Pregunta | Respuesta |
|----------|-----------|
| ¿Son 1A y 1B dos desarrollos distintos que se suman? | **No.** Es **un solo desarrollo** (“Agregar servicio”) con **dos niveles** de alcance. |
| ¿Puedo contratar los dos? | **No.** Marque **solo una** opción: **1A** *o* **1B**. |
| ¿1B incluye lo de 1A? | **Sí.** La versión **completa (1B)** ya trae todo lo de la **práctica (1A)** y además reutiliza datos del candidato y unifica el expediente. |
| ¿Cuál elijo? | **1A** si quieren la solución **más rápida y económica**. **1B** si la prioridad es **no hacer repetir** al candidato el formulario completo. |

Los ítems **2 (Word)** y **3 (WhatsApp)** son **independientes**: pueden contratarse solos o junto con **1A** o **1B**.

> Los Q 10,000 del proyecto original son **independientes** de esta cotización.

---

## Por qué estos montos (en relación al proyecto de Q 22,000)

El sistema completo que ya tienen incluye: órdenes, evaluados, formularios en línea, estados, notificaciones, calendario, sedes, PDFs, portal empresa, roles, despliegue en producción y cientos de pruebas. Eso justificó **Q 22,000**.

Los tres puntos que nos pidieron ahora son **módulos puntuales**, no un sistema nuevo. Por eso **no equivalen a la mitad del proyecto** (Q 11,000), sino a una fracción acorde al trabajo real:

| Desarrollo | Comparación simple | Inversión |
|------------|------------------|----------:|
| Agregar servicio (práctico) | Menor que el módulo de Sedes (Q 3,500) | Q 2,800 |
| Informe Word | Feature acotada, no un módulo entero | Q 1,600 |
| WhatsApp API | Similar en esfuerzo al calendario, pero sin UI tan grande | Q 3,800 |

Los precios cubren: análisis, programación, pruebas, despliegue en su servidor y 30 días de garantía por errores del entregable.

---

## Ítem 1 — Agregar servicio a una orden ya creada

> **Recuerde:** **1A** y **1B** son **excluyentes**. Contrata **una u otra**, nunca las dos. **1B** sustituye a **1A** (no se apilan).

### Qué problema resuelve

Les pasa seguido: un cliente pide **Polígrafo o VSA** y después el **mismo candidato** necesita **Estudio Socioeconómico**. Hoy deben manejar eso con workarounds; quieren hacerlo **dentro de la misma orden**, con claridad operativa.

### Qué haremos (explicado sin tecnicismos)

**Opción 1A — Versión práctica (Q 2,800)**

1. Botón visible en la orden: **“Agregar servicio”**.
2. Eligen Polígrafo, VSA o Socioeconómico para el **mismo candidato** (mismo DPI).
3. El sistema **prellena** nombre, DPI, correo, teléfono, empresa, etc.
4. Se crea el nuevo servicio con **su propia autorización, estados e informe** — independiente del anterior.
5. El candidato recibe el flujo del **nuevo servicio** (autorización + formulario que corresponda).

**Limitación honesta:** el candidato **puede tener que completar de nuevo** partes del formulario si el nuevo servicio lo requiere. Es la opción **más rápida y económica**.

---

**Opción 1B — Versión completa (Q 5,200)** — *alternativa a 1A, no un complemento*

Incluye **todo lo de la opción 1A**, más:

1. **Reutilizar** lo que el candidato ya llenó (datos personales, familia, laboral, etc.) — no pedirlo otra vez.
2. Solo solicitar **autorización del nuevo servicio** + **preguntas extra** (por ejemplo, bloque socioeconómico).
3. Pantalla donde REPRO ve **todos los servicios del mismo candidato** en un solo lugar (expediente unificado en la orden).

**Cuándo conviene:** si quieren **no repetirle** al candidato formularios largos y unificar la operación. Requiere más programación y pruebas; por eso cuesta más y tarda más.

### Por qué cuesta Q 2,800 / Q 5,200

| Trabajo incluido | 1A | 1B |
|------------------|:--:|:--:|
| Diseño de pantallas y botones | ✓ | ✓ |
| Reglas de negocio (misma orden, mismo DPI, servicios distintos) | ✓ | ✓ |
| Estados independientes por servicio | ✓ | ✓ |
| Copiar datos ya capturados | — | ✓ |
| Lógica “solo pedir lo que falta” | — | ✓ |
| Pruebas en producción | ✓ | ✓ |

**Resumen:** si elige **1B**, **no necesita** contratar **1A** por separado — ya está incluido dentro de 1B.

---

## Ítem 2 — Informe para la empresa cliente en Word editable

### Qué problema resuelve

Ustedes confirmaron: **no necesitan Word del formulario del candidato** (ese en PDF está bien).

Necesitan Word del **informe que REPRO entrega a la empresa cliente** — con resultados y conclusiones — para **editarlo**, agregar lo que el sistema no contempla y entregarlo.

### Qué haremos

1. Botón **“Descargar informe en Word”** en la orden (por candidato o consolidado, según definamos al iniciar).
2. El sistema **genera un archivo .docx** con la información que hoy sale en el PDF de informe (datos del candidato, servicio, estados, preliminar si existe, observaciones, etc.).
3. Ustedes **abren en Word**, editan libremente y, si desean, **suben la versión final** como ya hacen hoy (Resultado Final — acepta Word o PDF).

### Qué implica técnicamente (por qué no es “gratis”)

| PDF (hoy) | Word (nuevo) |
|-----------|--------------|
| Una librería que convierte HTML a PDF | **Otra librería y otra programación** |
| Plantilla ya hecha | Hay que **maquetar de nuevo** el documento en formato editable |
| Solo lectura | Tablas, párrafos y estilos editables en Word |

No es pegar un botón encima del PDF: es **construir el informe en un formato distinto**, probarlo y mantenerlo cuando cambien datos del sistema.

### Por qué cuesta Q 1,600

Es un desarrollo **acotado** (**3–5 días hábiles** de programación), menor que un módulo como Calendario o Sedes, pero con trabajo real de integración y pruebas. El precio está **por debajo** de módulos anteriores del proyecto por ese alcance acotado.

---

## Ítem 3 — Notificaciones automáticas por WhatsApp

### Qué problema resuelve

Hoy contactan candidatos y empresas sobre todo por **correo** y enlaces manuales. Quieren **WhatsApp automático** (enlace del formulario, recordatorios, avisos importantes).

### Qué haremos

1. Conectar REPRO con **WhatsApp Business API** (Meta o proveedor autorizado).
2. Enviar mensajes automáticos en eventos acordados (ej.: enlace al formulario, recordatorio de vencimiento, resultados disponibles).
3. Panel básico en REPRO: activar/desactivar, ver historial de envíos.
4. Pruebas con el número de REPRO.

### Qué NO incluye el precio (importante)

| Incluido (Q 3,800) | **No incluido — paga REPRO directo al proveedor** |
|---------------------|--------------------------------------------------|
| Programación e integración | Cuota mensual Meta / BSP |
| Configuración técnica | Costo por cada mensaje enviado |
| Estructura para plantillas | Tiempo de aprobación de plantillas por Meta |

Meta cobra aparte según volumen; eso es **ajeno a Szystems** y varía mes a mes.

### Por qué cuesta Q 3,800

Es comparable en esfuerzo a conectar un **servicio externo** (como el calendario), con registro de envíos, manejo de errores y cumplimiento de reglas de WhatsApp (plantillas aprobadas, opt-in, etc.). El precio quedó **por debajo** del módulo Calendario (Q 4,500) porque no incluye una interfaz visual tan grande, pero la integración API es exigente.

---

## Tabla de combinaciones

| Si contratan… | Total |
|---------------|------:|
| Solo Word (2) | **Q 1,600** |
| Solo Agregar servicio práctico (1A) | **Q 2,800** |
| Solo WhatsApp (3) | **Q 3,800** |
| 1A + Word | **Q 4,400** |
| 1A + Word + WhatsApp *(paquete)* | **Q 7,500** |
| 1B + Word + WhatsApp | **Q 10,600** |

---

## Cronograma sugerido

Si aprueban el **paquete 1A + 2 + 3 (Q 7,500)**:

| Plazo | Entregable |
|-------|------------|
| **Semana 1** | Informe Word en producción *(prioridad — entrega más rápida)* |
| **Semana 1–2** | Agregar servicio (1A) en producción |
| **Semana 2–3** | WhatsApp integrado *(si Meta ya aprobó plantillas; si no, se activa en cuanto aprueben)* |

**Tiempo total estimado del paquete: 2–3 semanas** para Word y Agregar servicio. WhatsApp puede sumar unos días si Meta tarda en aprobar.

**Workaround mientras tanto (sin costo):** hoy ya pueden agregar un **segundo evaluado** en la **misma orden** (mismo DPI, otro servicio) desde Editar orden. No es tan ágil como el botón “Agregar servicio”, pero **permite operar ya** sin esperar el desarrollo.

WhatsApp requiere que REPRO tenga **cuenta Meta Business** lista; eso lo gestionan ustedes con Meta.

---

## Forma de pago

### Proyecto original

| Concepto | Monto |
|----------|------:|
| Saldo pendiente plataforma | **Q 10,000.00** |

Según acuerdo previo con ustedes.

### Desarrollos de esta cotización

| Concepto | Monto |
|----------|------:|
| Ítems que marquen abajo | *Según selección* |
| **Anticipo** | **50%** al aprobar |
| **Saldo** | **50%** al entregar e instalar en producción |

---

## Condiciones

1. Precios en **quetzales (Q)**.  
2. Cotización válida **30 días**.  
3. **Garantía 30 días** post-entrega: corrección de errores del desarrollo entregado.  
4. No incluye: hosting, dominio, costos Meta/WhatsApp, capacitación extendida.  
5. Alcance limitado a lo descrito; cualquier extra se cotiza aparte.

---

## Trabajo relacionado (no incluido aquí)

Conversamos en reunión otros temas que **no están en esta cotización**:

- Integración de las **7 autorizaciones legales + Infornet** en el formulario del candidato.  
- **Campos nuevos** de formularios (Pre-empleo / Socioeconómico) — pendiente documento de REPRO.  
- Corrección menor formulario Específica = Periódica — puede acordarse como ajuste si lo desean.

Si quieren, preparamos una **cotización aparte** o incluimos autorizaciones legales en un siguiente paquete.

---

## Para aprobar

### Paso 1 — Agregar servicio *(marque solo UNA opción)*

- [ ] **1A** — Versión práctica — **Q 2,800**  
- [ ] **1B** — Versión completa *(incluye todo 1A + reutilización de datos)* — **Q 5,200**  
- [ ] **Ninguna** — no contrato agregar servicio por ahora  

### Paso 2 — Otros desarrollos *(opcionales, sí puede marcar varios)*

- [ ] **2** — Informe Word para empresa cliente — **Q 1,600**  
- [ ] **3** — WhatsApp API — **Q 3,800**  

### Paso 3 — Paquete con descuento *(solo si marcó 1A en el paso 1)*

- [ ] **Paquete 1A + 2 + 3** — **Q 7,500** *(en lugar de Q 8,200)*  

> Si marcó **1B** en el paso 1, el total sería **1B + ítems 2 y/o 3** según la tabla de combinaciones (no aplica el paquete Q 7,500, que es solo con 1A).

**Total aprobado:** Q ______________  

Nombre: _________________________  
Fecha: _________________________  
Firma: _________________________  

---

**Atentamente,**

**Otto Szarata**  
Szystems · Tel. 4215-3288  
https://reproappv2.szystems.com  

---

*Documento para el cliente · Junio 2026*
