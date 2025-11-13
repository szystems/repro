# API Documentation - REPRO

## 📡 Información General

**Base URL (Desarrollo):** `http://127.0.0.1:8000/api`
**Base URL (Producción):** TBD
**Autenticación:** Laravel Sanctum (Token-based)
**Formato de Respuesta:** JSON
**Versión API:** v1 (Futuro)

---

## 🔐 Autenticación

### Obtener Token de Acceso

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
    "email": "usuario@ejemplo.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "role_as": 1,
            "empresa_id": 5
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
    },
    "message": "Login exitoso"
}
```

**Response (401):**
```json
{
    "success": false,
    "message": "Credenciales inválidas",
    "errors": {
        "email": ["Las credenciales no coinciden con nuestros registros"]
    }
}
```

### Cerrar Sesión

**Endpoint:** `POST /api/logout`
**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "success": true,
    "message": "Sesión cerrada exitosamente"
}
```

---

## 👤 Endpoints de Usuarios

### Listar Usuarios

**Endpoint:** `GET /api/users`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `usuarios.ver`

**Query Parameters:**
- `page` (int): Número de página
- `per_page` (int): Registros por página (max: 100)
- `search` (string): Búsqueda por nombre o email
- `role_as` (int): Filtrar por rol (0-3)
- `empresa_id` (int): Filtrar por empresa

**Response (200):**
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "name": "Juan Pérez",
                "email": "juan@ejemplo.com",
                "role_as": 1,
                "role_name": "Empresa",
                "roles": ["empresa"],
                "empresa": {
                    "id": 5,
                    "nombre": "Empresa ABC"
                },
                "estado": 1,
                "created_at": "2025-01-15T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 20,
            "total": 150,
            "last_page": 8
        }
    }
}
```

### Obtener Usuario por ID

**Endpoint:** `GET /api/users/{id}`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `usuarios.ver`

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "telefono": "+502 1234-5678",
            "celular": "+502 9876-5432",
            "direccion": "Ciudad de Guatemala",
            "fecha_nacimiento": "1990-05-15",
            "fotografia": "1234567890.jpg",
            "role_as": 1,
            "roles": [
                {
                    "id": 3,
                    "name": "empresa",
                    "display_name": "Usuario Empresa"
                }
            ],
            "permissions": [
                {
                    "name": "ordenes.ver",
                    "display_name": "Ver Órdenes",
                    "module": "ordenes"
                }
            ],
            "empresa": {
                "id": 5,
                "nombre": "Empresa ABC",
                "nit": "123456789"
            },
            "cuestionario_completado": false,
            "created_at": "2025-01-15T10:30:00Z",
            "updated_at": "2025-01-20T15:45:00Z"
        }
    }
}
```

### Crear Usuario

**Endpoint:** `POST /api/users`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `usuarios.crear`

**Request Body:**
```json
{
    "name": "María García",
    "email": "maria@ejemplo.com",
    "telefono": "+502 1234-5678",
    "celular": "+502 9876-5432",
    "direccion": "Ciudad de Guatemala",
    "fecha_nacimiento": "1992-08-20",
    "role_as": 1,
    "empresa_id": 5,
    "cargo": "Gerente de RRHH",
    "roles": ["empresa"],
    "documento_identidad": "1234567890123",
    "tipo_documento": "DPI"
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "user": { ...user_object... },
        "temp_password": "Repro1234"
    },
    "message": "Usuario creado exitosamente. Se envió un email con las credenciales."
}
```

### Actualizar Usuario

**Endpoint:** `PUT /api/users/{id}`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `usuarios.editar`

**Request Body:** Similar a crear, pero todos los campos son opcionales

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": { ...user_object... }
    },
    "message": "Usuario actualizado exitosamente"
}
```

### Eliminar Usuario

**Endpoint:** `DELETE /api/users/{id}`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `usuarios.eliminar`

**Response (200):**
```json
{
    "success": true,
    "message": "Usuario eliminado exitosamente"
}
```

---

## 🏢 Endpoints de Empresas

### Listar Empresas

**Endpoint:** `GET /api/empresas`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `empresas.ver`

**Response (200):**
```json
{
    "success": true,
    "data": {
        "empresas": [
            {
                "id": 1,
                "nombre": "Empresa ABC",
                "nit": "123456-7",
                "direccion": "Ciudad de Guatemala",
                "telefono": "+502 2222-3333",
                "email": "contacto@empresaabc.com",
                "logo": "empresa_abc.png",
                "estado": 1,
                "usuarios_count": 15,
                "ordenes_count": 45
            }
        ]
    }
}
```

---

## 📋 Endpoints de Órdenes (Futuro)

### Listar Órdenes

**Endpoint:** `GET /api/ordenes`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `ordenes.ver`

**Query Parameters:**
- `estado` (string): pendiente, en_proceso, completada, cancelada
- `empresa_id` (int): Filtrar por empresa
- `fecha_desde` (date): Filtro de fecha inicio
- `fecha_hasta` (date): Filtro de fecha fin

**Response (200):**
```json
{
    "success": true,
    "data": {
        "ordenes": [
            {
                "id": 1,
                "codigo": "ORD-2025-001",
                "empresa": {
                    "id": 5,
                    "nombre": "Empresa ABC"
                },
                "cantidad_evaluaciones": 10,
                "completadas": 5,
                "pendientes": 5,
                "estado": "en_proceso",
                "fecha_solicitud": "2025-01-15",
                "fecha_limite": "2025-02-15",
                "poligrafista_asignado": {
                    "id": 8,
                    "name": "Dr. Luis Rodríguez"
                },
                "created_at": "2025-01-15T10:00:00Z"
            }
        ],
        "pagination": { ... }
    }
}
```

### Crear Orden

**Endpoint:** `POST /api/ordenes`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `ordenes.crear`

**Request Body:**
```json
{
    "empresa_id": 5,
    "cantidad_evaluaciones": 10,
    "fecha_limite": "2025-02-15",
    "notas": "Evaluaciones para nuevo personal",
    "evaluados": [
        {
            "nombre": "Carlos López",
            "email": "carlos@ejemplo.com",
            "telefono": "+502 1234-5678",
            "documento": "1234567890123"
        }
    ]
}
```

**Response (201):**
```json
{
    "success": true,
    "data": {
        "orden": {
            "id": 1,
            "codigo": "ORD-2025-001",
            "empresa_id": 5,
            "cantidad_evaluaciones": 10,
            "estado": "pendiente",
            "codigos_cuestionario": [
                "CUE-ABC123",
                "CUE-DEF456"
            ]
        }
    },
    "message": "Orden creada exitosamente. Se enviaron los links de cuestionario por email."
}
```

---

## 📝 Endpoints de Cuestionarios (Futuro)

### Obtener Cuestionario (Sin Auth)

**Endpoint:** `GET /api/cuestionario/{codigo}`
**Headers:** Ninguno (acceso público)

**Response (200):**
```json
{
    "success": true,
    "data": {
        "cuestionario": {
            "codigo": "CUE-ABC123",
            "evaluado": {
                "nombre": "Carlos López",
                "email": "carlos@ejemplo.com"
            },
            "empresa": {
                "nombre": "Empresa ABC"
            },
            "completado": false,
            "secciones": [
                {
                    "id": 1,
                    "titulo": "Datos Personales",
                    "preguntas": [
                        {
                            "id": 1,
                            "tipo": "text",
                            "pregunta": "Nombre completo",
                            "requerido": true
                        }
                    ]
                }
            ]
        }
    }
}
```

### Guardar Respuestas de Cuestionario (Sin Auth)

**Endpoint:** `POST /api/cuestionario/{codigo}/respuestas`
**Headers:** Ninguno

**Request Body:**
```json
{
    "seccion_id": 1,
    "respuestas": [
        {
            "pregunta_id": 1,
            "valor": "Carlos López García"
        },
        {
            "pregunta_id": 2,
            "valor": "1990-05-15"
        }
    ],
    "draft": true
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Respuestas guardadas como borrador"
}
```

### Completar Cuestionario (Sin Auth)

**Endpoint:** `POST /api/cuestionario/{codigo}/completar`

**Request Body:**
```json
{
    "firma_digital": "data:image/png;base64,iVBORw0KGgoAAAANS...",
    "acepta_terminos": true
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Cuestionario completado exitosamente",
    "data": {
        "fecha_completado": "2025-01-16T14:30:00Z"
    }
}
```

---

## 📊 Endpoints de Reportes (Futuro)

### Dashboard Statistics

**Endpoint:** `GET /api/dashboard/stats`
**Headers:** `Authorization: Bearer {token}`
**Permisos:** `reportes.ver`

**Response (200):**
```json
{
    "success": true,
    "data": {
        "ordenes": {
            "total": 150,
            "pendientes": 20,
            "en_proceso": 45,
            "completadas": 85
        },
        "evaluaciones": {
            "total_mes": 120,
            "promedio_diario": 4
        },
        "empresas_activas": 35,
        "ultimas_ordenes": [ ... ]
    }
}
```

---

## 🔧 Códigos de Estado HTTP

| Código | Significado | Uso |
|--------|-------------|-----|
| 200 | OK | Solicitud exitosa |
| 201 | Created | Recurso creado exitosamente |
| 204 | No Content | Eliminación exitosa |
| 400 | Bad Request | Datos inválidos |
| 401 | Unauthorized | No autenticado |
| 403 | Forbidden | Sin permisos |
| 404 | Not Found | Recurso no encontrado |
| 422 | Unprocessable Entity | Errores de validación |
| 429 | Too Many Requests | Rate limit excedido |
| 500 | Internal Server Error | Error del servidor |

---

## ⚠️ Manejo de Errores

### Formato de Error Estándar

```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        "campo": [
            "El campo es obligatorio"
        ]
    },
    "code": "VALIDATION_ERROR"
}
```

### Códigos de Error Personalizados

| Código | Descripción |
|--------|-------------|
| `AUTH_FAILED` | Fallo de autenticación |
| `VALIDATION_ERROR` | Error de validación |
| `PERMISSION_DENIED` | Sin permisos |
| `RESOURCE_NOT_FOUND` | Recurso no encontrado |
| `BUSINESS_LOGIC_ERROR` | Error de lógica de negocio |
| `RATE_LIMIT_EXCEEDED` | Límite de solicitudes excedido |

---

## 🚦 Rate Limiting

**Límites:**
- Endpoints públicos: 60 requests / minuto
- Endpoints autenticados: 120 requests / minuto
- Endpoints de login: 5 intentos / minuto

**Headers de Respuesta:**
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1642345678
```

---

## 📝 Paginación

### Formato de Paginación

Todos los endpoints que retornan listas incluyen paginación:

```json
{
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8,
        "from": 1,
        "to": 20,
        "links": {
            "first": "/api/users?page=1",
            "last": "/api/users?page=8",
            "prev": null,
            "next": "/api/users?page=2"
        }
    }
}
```

---

## 🧪 Testing de API

### Postman Collection
**Ubicación:** `docs/postman/REPRO_API.postman_collection.json`

### Ejemplos con cURL

**Login:**
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@repro.com","password":"password"}'
```

**Listar Usuarios:**
```bash
curl -X GET http://127.0.0.1:8000/api/users \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## 🔮 Endpoints Futuros

### V2 (Planificado)
- WebSockets para notificaciones en tiempo real
- Upload de archivos por chunks
- GraphQL endpoint alternativo
- Webhooks para integraciones

---

## 📚 Referencias

- [Laravel Sanctum Docs](https://laravel.com/docs/12.x/sanctum)
- [REST API Best Practices](https://restfulapi.net/)
- [HTTP Status Codes](https://httpstatuses.com/)

---

**Estado:** 🚧 En Construcción
**Última actualización:** 8 de noviembre de 2025
**Versión:** 0.1 (Draft)
