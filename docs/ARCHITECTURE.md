# Arquitectura del Sistema REPRO

## 📐 Vista General

REPRO utiliza una arquitectura **MVC (Model-View-Controller)** con Laravel 12 como framework base, implementando patrones adicionales para mantener la escalabilidad y mantenibilidad.

---

## 🏛️ Capas de la Aplicación

```
┌─────────────────────────────────────────────┐
│           CAPA DE PRESENTACIÓN              │
│  (Blade Templates, JavaScript, Bootstrap)   │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         CAPA DE CONTROLADORES               │
│     (HTTP Controllers, Form Requests)       │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│         CAPA DE LÓGICA DE NEGOCIO           │
│    (Services, Policies, Middleware)         │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│           CAPA DE DATOS                     │
│    (Models, Eloquent, Query Builder)        │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│          BASE DE DATOS (MySQL)              │
└─────────────────────────────────────────────┘
```

---

## 📦 Estructura de Directorios

```
repro/
├── app/
│   ├── Console/                    # Comandos Artisan
│   ├── Exceptions/                 # Manejo de excepciones
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Controladores administrativos
│   │   │   │   ├── AdminController.php
│   │   │   │   ├── UsersController.php
│   │   │   │   ├── EmpresasController.php
│   │   │   │   └── ConfigController.php
│   │   │   └── Auth/              # Controladores de autenticación
│   │   ├── Middleware/            # Middlewares personalizados
│   │   │   ├── CheckRole.php
│   │   │   ├── CheckPermission.php
│   │   │   ├── AdminMiddleware.php
│   │   │   └── RedirectBasedOnRole.php
│   │   └── Requests/              # Form Requests
│   │       ├── UserFormRequest.php
│   │       ├── EmpresaFormRequest.php
│   │       └── ConfigFormRequest.php
│   ├── Mail/                      # Clases de correo
│   │   ├── UserMail.php
│   │   └── UserResetPasswordMail.php
│   ├── Models/                    # Modelos Eloquent
│   │   ├── User.php
│   │   ├── Empresa.php
│   │   ├── Config.php
│   │   ├── Role.php
│   │   └── Permission.php
│   ├── Providers/                 # Service Providers
│   └── Traits/                    # Traits reutilizables
│       └── HasRolesAndPermissions.php
├── bootstrap/                     # Bootstrap de la aplicación
├── config/                        # Archivos de configuración
├── database/
│   ├── factories/                 # Model Factories
│   ├── migrations/                # Migraciones de BD
│   └── seeders/                   # Seeders
│       └── RolesAndPermissionsSeeder.php
├── docs/                          # Documentación del proyecto
│   ├── PRD.md
│   ├── ARCHITECTURE.md
│   └── API.md
├── public/                        # Archivos públicos
│   ├── assets/
│   │   ├── imgs/
│   │   │   ├── users/            # Fotos de usuarios
│   │   │   └── logos/            # Logos de empresas
│   │   └── css/
│   └── dashboardtemplate/
├── resources/
│   ├── css/
│   ├── js/
│   ├── lang/                      # Traducciones
│   └── views/
│       ├── admin/                 # Vistas administrativas
│       │   ├── user/             # CRUD de usuarios
│       │   ├── empresa/          # CRUD de empresas
│       │   └── config/           # Configuración
│       ├── auth/                  # Vistas de autenticación
│       └── layouts/               # Layouts base
│           ├── admin.blade.php
│           ├── incempresa/       # Layout para empresas
│           └── incevaluado/      # Layout para evaluados
├── routes/
│   ├── web.php                    # Rutas web
│   ├── api.php                    # Rutas API
│   └── channels.php               # Broadcasting
├── storage/                       # Almacenamiento
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/                         # Tests
│   ├── Feature/
│   └── Unit/
├── .env                           # Variables de entorno
├── composer.json                  # Dependencias PHP
├── package.json                   # Dependencias JS
└── phpunit.xml                    # Configuración de tests
```

---

## 🔐 Flujo de Autenticación y Autorización

### Autenticación

```
┌──────────┐     ┌──────────────┐     ┌─────────────┐
│  Usuario │────▶│ LoginRequest │────▶│   Session   │
└──────────┘     └──────────────┘     └─────────────┘
                        │
                        ▼
                ┌───────────────┐
                │ RateLimiter   │
                │ (5 intentos)  │
                └───────────────┘
                        │
                        ▼
                ┌───────────────┐
                │ Authenticate  │
                │ Middleware    │
                └───────────────┘
```

### Autorización (Sistema Dual)

```
┌─────────────────────────────────────────┐
│           Request Entrante              │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│       Authenticate Middleware           │
│       (Verificar sesión activa)         │
└─────────────────────────────────────────┘
                    ↓
        ┌───────────────────────┐
        │  ¿Requiere permisos?  │
        └───────────────────────┘
          │                   │
     SI   │                   │  NO
          ↓                   ↓
┌──────────────────┐   ┌──────────────┐
│ CheckRole        │   │  Continuar   │
│ o                │   │  Request     │
│ CheckPermission  │   └──────────────┘
└──────────────────┘
          │
          ↓
┌──────────────────────────────────┐
│ Verificación en Modelo User:     │
│ 1. hasRole() o hasPermission()   │
│ 2. Consulta tabla user_role      │
│ 3. Consulta role_permission      │
└──────────────────────────────────┘
          │
    ┌─────┴─────┐
    │           │
Autorizado  No Autorizado
    │           │
    ↓           ↓
Ejecutar     HTTP 403
Controlador  Forbidden
```

---

## 🗄️ Modelo de Datos (ERD Simplificado)

```
┌──────────────┐
│   configs    │
│──────────────│
│ id (PK)      │
│ logo         │
│ email        │
│ timezone     │
└──────────────┘

┌──────────────┐        ┌──────────────┐
│   empresas   │◄───┐   │    users     │
│──────────────│    │   │──────────────│
│ id (PK)      │    └──▷│ id (PK)      │
│ nombre       │        │ name         │
│ nit          │        │ email        │
│ direccion    │        │ password     │
│ logo         │        │ role_as      │◄──┐
│ estado       │        │ empresa_id   │   │ Sistema Legacy
└──────────────┘        │ principal    │   │
                        │ estado       │   │
                        │ documento    │   │
                        │ permisos(JSON)   │
                        └──────────────┘   │
                                │          │
                  ┌─────────────┴─────┐    │
                  │                   │    │
                  ▼                   ▼    │
        ┌──────────────┐    ┌──────────────┐
        │  user_role   │    │    roles     │
        │──────────────│    │──────────────│
        │ user_id (FK) │───▶│ id (PK)      │
        │ role_id (FK) │    │ name         │
        └──────────────┘    │ display_name │
                            └──────────────┘
                                  │
                    ┌─────────────┴──────────┐
                    │                        │
                    ▼                        ▼
        ┌────────────────────┐   ┌──────────────────┐
        │  role_permission   │   │   permissions    │
        │────────────────────│   │──────────────────│
        │ role_id (FK)       │──▶│ id (PK)          │
        │ permission_id (FK) │   │ name             │
        └────────────────────┘   │ display_name     │
                                 │ module           │
                                 └──────────────────┘

┌────────────────────────────────────────────────────────────┐
│                     TABLAS FUTURAS                          │
│                  (Módulo de Órdenes y Evaluaciones)         │
└────────────────────────────────────────────────────────────┘

┌──────────────┐        ┌────────────────────┐
│   empresas   │        │      ordenes       │
│──────────────│        │────────────────────│
│ id (PK)      │◄──────▷│ id (PK)            │
└──────────────┘        │ empresa_id (FK)    │
                        │ codigo_orden       │
                        │ tipo_servicio      │◄── (poligrafo/vsa/socioeconomico)
                        │ tipo_formulario    │◄── (preempleo/periodica/especifica)
                        │ cantidad_evals     │
                        │ estado             │
                        │ creado_por (FK)    │◄── user_id (empresa o repro)
                        │ poligrafista_id(FK)│
                        │ fecha_solicitud    │
                        │ fecha_limite       │
                        └────────────────────┘
                                  │
                                  │ 1:N
                                  │
                                  ▼
                        ┌────────────────────┐
                        │  evaluados_orden   │◄─── ⚠️ NO SON USUARIOS
                        │────────────────────│
                        │ id (PK)            │
                        │ orden_id (FK)      │
                        │ nombre             │
                        │ email              │
                        │ telefono           │
                        │ dpi                │◄─── Identificador único
                        │ tipo_documento     │
                        │ token_unico        │◄─── Para acceso sin login
                        │ token_expira_at    │
                        │ cuestionario_completado │
                        │ completado_at      │
                        │ firma_digital      │
                        │ ip_completado      │
                        └────────────────────┘
                                  │
                                  │ 1:1
                                  │
                                  ▼
                        ┌────────────────────┐
                        │   cuestionarios    │
                        │────────────────────│
                        │ id (PK)            │
                        │ evaluado_orden_id  │
                        │ tipo_formulario    │◄── Heredado de orden
                        │ respuestas (JSON)  │◄── Todas las respuestas
                        │ seccion_actual     │
                        │ bloqueado          │
                        └────────────────────┘
                                  │
                                  │ 1:N
                                  │
                                  ▼
                        ┌────────────────────┐
                        │   evaluaciones     │
                        │────────────────────│
                        │ id (PK)            │
                        │ cuestionario_id(FK)│
                        │ poligrafista_id(FK)│
                        │ tipo_servicio      │
                        │ fecha_evaluacion   │
                        │ resultados (JSON)  │
                        │ archivos (JSON)    │
                        │ observaciones      │
                        │ estado             │
                        └────────────────────┘
                                  │
                                  │ 1:1
                                  │
                                  ▼
                        ┌────────────────────┐
                        │    resultados      │
                        │────────────────────│
                        │ id (PK)            │
                        │ evaluacion_id (FK) │
                        │ pdf_path           │
                        │ conclusion         │
                        │ recomendacion      │
                        │ generado_at        │
                        │ descargado_por(JSON)│◄─── Log de descargas
                        └────────────────────┘
│ cuestionarios      │
│ resultados         │
│ documentos         │
└────────────────────┘
```

---

## 🔄 Flujo de Request-Response

### Request de Creación de Usuario

```
1. POST /insert-user
   │
   ▼
2. Route Middleware
   ├─ auth (autenticado?)
   └─ role:admin,repro (tiene rol?)
   │
   ▼
3. UsersController@insertuser
   │
   ▼
4. UserFormRequest (validación)
   ├─ Validar campos básicos
   ├─ Validar empresa_id si role_as=1
   ├─ Validar documento si role_as=0
   └─ Validar roles array
   │
   ▼
5. Crear modelo User
   ├─ Guardar en BD
   ├─ Upload de imagen
   └─ Asignar roles
   │
   ▼
6. Enviar email con credenciales
   │
   ▼
7. Redirect con mensaje de éxito
   │
   ▼
8. Vista users/index
```

---

## 🧩 Patrones de Diseño Utilizados

### 1. Repository Pattern (Futuro)
**Propósito:** Abstracción de la capa de datos
**Implementación:** Pendiente para módulos complejos

```php
interface OrdenRepositoryInterface {
    public function create(array $data);
    public function findByEmpresa(int $empresaId);
    public function updateStatus(int $ordenId, string $status);
}

class OrdenRepository implements OrdenRepositoryInterface {
    // Implementación
}
```

### 2. Service Layer Pattern
**Propósito:** Lógica de negocio compleja fuera de controladores

```php
class EvaluacionService {
    public function crearEvaluacion(Orden $orden, array $data)
    {
        // Validar que orden esté en estado correcto
        // Crear evaluación
        // Notificar a poligrafista
        // Actualizar estado de orden
    }
}
```

### 3. Form Request Pattern ✅
**Propósito:** Validación centralizada
**Estado:** Implementado

```php
class UserFormRequest extends FormRequest
{
    public function rules() { ... }
    public function messages() { ... }
}
```

### 4. Middleware Pattern ✅
**Propósito:** Interceptar requests
**Estado:** Implementado

```php
Route::middleware(['auth', 'role:admin'])->group(...);
```

### 5. Observer Pattern (Futuro)
**Propósito:** Eventos de modelos

```php
class OrdenObserver {
    public function created(Orden $orden) {
        // Enviar notificación
    }
}
```

---

## 🔌 Integraciones Externas

### Email (SMTP)
**Librería:** Laravel Mail
**Uso:**
- Envío de credenciales a nuevos usuarios
- Reset de contraseña
- Notificaciones de órdenes

### PDF Generation
**Librería:** barryvdh/laravel-dompdf
**Uso:**
- Listados de usuarios y empresas
- Resultados de evaluaciones
- Reportes

### Excel Export
**Librería:** maatwebsite/excel
**Uso:**
- Exportar listados
- Reportes estadísticos

### Laravel Boost (MCP)
**Propósito:** Asistencia de IA para desarrollo
**Herramientas:**
- Consulta de BD en tiempo real
- Lectura de logs
- Tinker para debugging
- Búsqueda en documentación

---

## ⚠️ Reglas de Negocio Críticas

### 1. Evaluados SIN Cuenta de Usuario
**Regla:** Los evaluados NO son usuarios del sistema
- ❌ No tienen tabla `users` entry
- ❌ No pueden hacer login
- ❌ No tienen dashboard
- ✅ Acceso temporal por token único
- ✅ Identificación por DPI

**Implementación:**
```php
// ❌ INCORRECTO - No crear usuario para evaluado
User::create([
    'name' => 'Evaluado',
    'email' => 'evaluado@ejemplo.com',
    'role_as' => 0
]);

// ✅ CORRECTO - Crear registro en evaluados_orden
EvaluadoOrden::create([
    'orden_id' => $orden->id,
    'nombre' => 'Juan Pérez',
    'email' => 'juan@ejemplo.com',
    'dpi' => '1234567890123',
    'token_unico' => Str::random(64),
    'token_expira_at' => now()->addDays(30)
]);
```

### 2. Privacidad del Historial por DPI
**Regla:** Solo Repro y Admin pueden consultar historial completo

| Usuario | Ver Historial DPI | Ver Resultados Propios | Crear Órdenes |
|---------|-------------------|------------------------|---------------|
| Admin | ✅ Sí, todo | ✅ Sí, todo | ✅ Sí |
| Repro | ✅ Sí, todo | ✅ Sí, asignados | ✅ Sí, elige empresa |
| Empresa | ❌ NO | ✅ Solo sus órdenes | ✅ Sí, auto-asignado |
| Evaluado | ❌ NO | ❌ NO | ❌ NO |

**Implementación:**
```php
// Consultar historial de evaluaciones por DPI
public function getHistorialByDPI(string $dpi)
{
    // Solo permitido para admin y repro
    if (!auth()->user()->hasAnyRole(['admin', 'repro'])) {
        abort(403, 'No autorizado para ver historial');
    }
    
    return EvaluadoOrden::where('dpi', $dpi)
        ->with(['orden', 'cuestionario', 'evaluacion'])
        ->orderBy('created_at', 'desc')
        ->get();
}
```

### 3. Asignación Automática de Órdenes
**Regla:** La creación de órdenes depende del tipo de usuario

```php
public function crearOrden(Request $request)
{
    $orden = new Orden($request->validated());
    
    // Asignación automática según usuario
    if (auth()->user()->hasRole('empresa')) {
        // Usuario empresa: auto-asignar a su empresa
        $orden->empresa_id = auth()->user()->empresa_id;
    } elseif (auth()->user()->hasAnyRole(['admin', 'repro'])) {
        // Admin/Repro: debe seleccionar empresa manualmente
        $orden->empresa_id = $request->empresa_id;
    }
    
    $orden->creado_por = auth()->id();
    $orden->save();
}
```

### 4. Tipos de Servicio y Formularios
**Regla:** Cada servicio tiene formularios específicos

| Servicio | Formularios Disponibles | Modalidad |
|----------|-------------------------|-----------|
| **Polígrafo** | Pre-empleo, Periódica, Específica | Presencial |
| **VSA** | Pre-empleo, Periódica, Específica | Virtual |
| **Socioeconómico** | Pre-empleo + campos extras | Presencial/Virtual |

**Implementación:**
```php
// Validar combinación servicio-formulario
public function rules()
{
    return [
        'tipo_servicio' => 'required|in:poligrafo,vsa,socioeconomico',
        'tipo_formulario' => [
            'required',
            Rule::in($this->getFormulariosDisponibles())
        ]
    ];
}

private function getFormulariosDisponibles()
{
    if ($this->tipo_servicio === 'socioeconomico') {
        return ['preempleo']; // Solo pre-empleo con extras
    }
    
    return ['preempleo', 'periodica', 'especifica'];
}
```

### 5. Token Único y Seguridad
**Regla:** Un token, un cuestionario, una vez

```php
// Generar token único
$token = Str::random(64);

// Verificar que no exista (probabilidad baja pero validar)
while (EvaluadoOrden::where('token_unico', $token)->exists()) {
    $token = Str::random(64);
}

// Acceso al cuestionario
public function mostrarCuestionario($token)
{
    $evaluado = EvaluadoOrden::where('token_unico', $token)
        ->where('token_expira_at', '>', now())
        ->where('cuestionario_completado', false)
        ->firstOrFail();
    
    // Token válido y no usado
    return view('cuestionario.show', compact('evaluado'));
}

// Bloquear después de completar
public function completarCuestionario($token)
{
    $evaluado = EvaluadoOrden::where('token_unico', $token)->firstOrFail();
    
    $evaluado->update([
        'cuestionario_completado' => true,
        'completado_at' => now(),
        'ip_completado' => request()->ip()
    ]);
    
    // Token ya no puede usarse
}
```

---

## 🚀 Deployment

### Entorno de Desarrollo
**Servidor:** Laragon (Windows)
**PHP:** 8.3.16
**MySQL:** Incluido en Laragon
**URL:** http://127.0.0.1:8000

### Entorno de Producción (Futuro)
**Stack Recomendado:**
- **Servidor:** Ubuntu 22.04 LTS
- **Webserver:** Nginx
- **PHP:** 8.3 (PHP-FPM)
- **Base de Datos:** MySQL 8.0
- **SSL:** Let's Encrypt
- **Deploy:** Laravel Forge o manual

### CI/CD (Futuro)
- **GitHub Actions:** Tests automáticos
- **Deploy automático** a staging
- **Rollback** automático si tests fallan

---

## 📊 Performance y Escalabilidad

### Optimizaciones Actuales
- ✅ Eloquent con eager loading (`with()`)
- ✅ Índices en campos de búsqueda frecuente
- ✅ Paginación en listados

### Optimizaciones Futuras
- ⏳ Redis para cache de sesiones
- ⏳ Cache de permisos de usuario
- ⏳ Queue para emails y PDFs
- ⏳ CDN para assets estáticos
- ⏳ Database read replicas

### Límites de Escalabilidad
**Usuarios concurrentes:** ~500 (con optimizaciones básicas)
**Órdenes por mes:** ~10,000
**Archivos almacenados:** Depende de storage

---

## 🔒 Seguridad

### Medidas Implementadas
- ✅ Password hashing (Bcrypt)
- ✅ CSRF protection
- ✅ SQL injection prevention (Eloquent/Query Builder)
- ✅ XSS protection (Blade escaping)
- ✅ Rate limiting en login
- ✅ Middleware de autorización

### Medidas Futuras
- ⏳ Two-factor authentication (2FA)
- ⏳ Logs de auditoría completos
- ⏳ Encriptación de datos sensibles
- ⏳ Security headers (helmet)
- ⏳ Penetration testing

---

## 📈 Monitoreo y Logging

### Logs de Aplicación
**Ubicación:** `storage/logs/laravel.log`
**Niveles:** debug, info, warning, error, critical

### Herramientas de Monitoreo (Futuro)
- **Laravel Telescope:** Debugging en desarrollo
- **Sentry:** Error tracking en producción
- **New Relic:** Performance monitoring

---

## 🧪 Testing Strategy

### Tests Unitarios
**Cobertura objetivo:** 70%
**Herramienta:** PHPUnit 11.5.43

```php
class UserTest extends TestCase {
    public function test_can_assign_role()
    public function test_has_permission()
}
```

### Tests de Feature
```php
class UserManagementTest extends TestCase {
    public function test_admin_can_create_user()
    public function test_empresa_cannot_create_admin()
}
```

### Tests E2E (Futuro)
**Herramienta:** Laravel Dusk
**Escenarios:** Flujo completo de orden → evaluación → resultado

---

## 📚 Referencias

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHP The Right Way](https://phptherightway.com/)
- [Laravel Boost](https://boost.laravel.com/)

---

**Última actualización:** 8 de noviembre de 2025
**Versión:** 1.0
