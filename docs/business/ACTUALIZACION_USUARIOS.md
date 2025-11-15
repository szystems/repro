# Actualización del Módulo de Usuarios - Sistema de Roles y Permisos

## ✅ Archivos Actualizados

### 1. **UserFormRequest.php** - Request de Validación
**Cambios:**
- ✅ Agregado validación para `documento_identidad` y `tipo_documento`
- ✅ Agregado validación para array de `roles` (nuevo sistema)
- ✅ Documento obligatorio para evaluados (role_as = 0)
- ✅ Tipos de documento: DPI, Pasaporte, Licencia

### 2. **UsersController.php** - Controlador Principal
**Cambios en métodos:**

#### `adduser()`:
- ✅ Carga lista de roles disponibles (`$roles`)
- ✅ Carga permisos agrupados por módulo (`$permissions`)
- ✅ Pasa ambas variables a la vista

#### `insertuser()`:
- ✅ Guarda `documento_identidad` y `tipo_documento`
- ✅ Asigna roles del nuevo sistema con `syncRoles()`
- ✅ Si no se especifican roles, asigna automáticamente basado en `role_as`
- ✅ Mapping automático: `0→evaluado`, `1→empresa`, `2→repro`, `3→admin`

#### `edituser()`:
- ✅ Carga usuario con sus roles actuales (`with('roles')`)
- ✅ Carga lista de roles y permisos
- ✅ Obtiene roles actuales del usuario (`$userRoles`)
- ✅ Pasa todas las variables a la vista

#### `updateuser()`:
- ✅ Actualiza `documento_identidad` y `tipo_documento`
- ✅ Sincroniza roles si el usuario actual es admin
- ✅ Usa `syncRoles()` para actualizar roles

---

## 📋 Nuevos Campos en la Tabla Users

| Campo | Tipo | Descripción | Requerido Para |
|-------|------|-------------|----------------|
| `documento_identidad` | VARCHAR(50) | DPI, Pasaporte, Licencia | Evaluados |
| `tipo_documento` | ENUM | DPI, Pasaporte, Licencia | Evaluados |
| `cuestionario_completado` | BOOLEAN | Si completó el cuestionario | Todos |
| `cuestionario_completado_at` | TIMESTAMP | Fecha de completado | Todos |

---

## 🎨 Cambios Necesarios en las Vistas

### Vista: `add.blade.php` (Crear Usuario)
**Agregar:**

```blade
{{-- Documento de Identidad (para evaluados) --}}
<div class="row" id="documento-fields" style="display: none;">
    <div class="col-md-6">
        <div class="form-group">
            <label for="tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
            <select name="tipo_documento" id="tipo_documento" class="form-control">
                <option value="">Seleccione...</option>
                <option value="DPI">DPI</option>
                <option value="Pasaporte">Pasaporte</option>
                <option value="Licencia">Licencia de Conducir</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="documento_identidad">Número de Documento <span class="text-danger">*</span></label>
            <input type="text" name="documento_identidad" id="documento_identidad" 
                   class="form-control" placeholder="Ej: 1234567890123">
        </div>
    </div>
</div>

{{-- Selector de Roles (Nuevo Sistema) --}}
@if(auth()->user()->isAdmin())
<div class="card mt-3">
    <div class="card-header">
        <h5>Roles y Permisos (Sistema Nuevo)</h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Seleccionar Roles:</label>
            @foreach($roles as $role)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" 
                       name="roles[]" value="{{ $role->name }}" 
                       id="role_{{ $role->id }}">
                <label class="form-check-label" for="role_{{ $role->id }}">
                    {{ $role->display_name }}
                    <small class="text-muted">- {{ $role->description }}</small>
                </label>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- JavaScript para mostrar/ocultar campos --}}
<script>
document.getElementById('role_as').addEventListener('change', function() {
    const roleAs = parseInt(this.value);
    const documentoFields = document.getElementById('documento-fields');
    
    // Mostrar campos de documento solo para evaluados (role_as = 0)
    if (roleAs === 0) {
        documentoFields.style.display = 'flex';
        document.getElementById('tipo_documento').required = true;
        document.getElementById('documento_identidad').required = true;
    } else {
        documentoFields.style.display = 'none';
        document.getElementById('tipo_documento').required = false;
        document.getElementById('documento_identidad').required = false;
    }
});
</script>
```

### Vista: `edit.blade.php` (Editar Usuario)
**Agregar:**

```blade
{{-- Documento de Identidad --}}
<div class="row" id="documento-fields" style="display: {{ $user->role_as == 0 ? 'flex' : 'none' }};">
    <div class="col-md-6">
        <div class="form-group">
            <label for="tipo_documento">Tipo de Documento</label>
            <select name="tipo_documento" id="tipo_documento" class="form-control">
                <option value="">Seleccione...</option>
                <option value="DPI" {{ $user->tipo_documento == 'DPI' ? 'selected' : '' }}>DPI</option>
                <option value="Pasaporte" {{ $user->tipo_documento == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                <option value="Licencia" {{ $user->tipo_documento == 'Licencia' ? 'selected' : '' }}>Licencia</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="documento_identidad">Número de Documento</label>
            <input type="text" name="documento_identidad" id="documento_identidad" 
                   class="form-control" value="{{ $user->documento_identidad }}">
        </div>
    </div>
</div>

{{-- Roles Actuales --}}
@if(auth()->user()->isAdmin())
<div class="card mt-3">
    <div class="card-header">
        <h5>Roles Asignados</h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            @foreach($roles as $role)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" 
                       name="roles[]" value="{{ $role->name }}" 
                       id="role_{{ $role->id }}"
                       {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                <label class="form-check-label" for="role_{{ $role->id }}">
                    {{ $role->display_name }}
                    <small class="text-muted">- {{ $role->description }}</small>
                </label>
            </div>
            @endforeach
        </div>
        
        <div class="alert alert-info mt-3">
            <strong>Permisos asociados a los roles seleccionados:</strong>
            <div id="permisos-info" class="mt-2">
                {{-- Se llenará dinámicamente con JavaScript --}}
            </div>
        </div>
    </div>
</div>
@endif
```

### Vista: `show.blade.php` (Ver Usuario)
**Agregar:**

```blade
{{-- Información de Documento --}}
@if($user->role_as == 0)
<div class="row mb-3">
    <div class="col-md-6">
        <strong>Tipo de Documento:</strong>
        <p>{{ $user->tipo_documento ?? 'No especificado' }}</p>
    </div>
    <div class="col-md-6">
        <strong>Número de Documento:</strong>
        <p>{{ $user->documento_identidad ?? 'No especificado' }}</p>
    </div>
</div>

@if($user->cuestionario_completado)
<div class="alert alert-success">
    <i class="fa fa-check-circle"></i>
    Cuestionario completado el {{ $user->cuestionario_completado_at->format('d/m/Y H:i') }}
</div>
@else
<div class="alert alert-warning">
    <i class="fa fa-exclamation-triangle"></i>
    Cuestionario pendiente
</div>
@endif
@endif

{{-- Roles Asignados (Nuevo Sistema) --}}
<div class="card mt-3">
    <div class="card-header">
        <h5>Roles y Permisos</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>Roles:</strong>
            @if($user->roles->count() > 0)
                @foreach($user->roles as $role)
                    <span class="badge bg-primary">{{ $role->display_name }}</span>
                @endforeach
            @else
                <span class="text-muted">Sin roles asignados</span>
            @endif
        </div>
        
        <div>
            <strong>Permisos:</strong>
            @php
                $permisos = $user->getAllPermissions();
            @endphp
            @if($permisos->count() > 0)
                <ul class="list-unstyled">
                    @foreach($permisos->groupBy('module') as $module => $modulePermisos)
                        <li>
                            <strong>{{ ucfirst($module) }}:</strong>
                            @foreach($modulePermisos as $permiso)
                                <span class="badge bg-secondary">{{ $permiso->display_name }}</span>
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            @else
                <span class="text-muted">Sin permisos asignados</span>
            @endif
        </div>
    </div>
</div>
```

### Vista: `index.blade.php` (Listado)
**Agregar columna de roles:**

```blade
<thead>
    <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Rol Antiguo</th>
        <th>Roles Nuevos</th> {{-- NUEVA COLUMNA --}}
        <th>Empresa</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
    @foreach($users as $user)
    <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->getRoleName() }}</td>
        <td>
            @foreach($user->roles as $role)
                <span class="badge bg-info">{{ $role->display_name }}</span>
            @endforeach
        </td>
        <td>{{ $user->empresa->nombre ?? 'N/A' }}</td>
        <td>
            {{-- Botones de acción --}}
        </td>
    </tr>
    @endforeach
</tbody>
```

---

## 🔧 JavaScript Recomendado

Para mejorar la experiencia en el formulario de edición:

```javascript
// Mostrar permisos según roles seleccionados
document.querySelectorAll('input[name="roles[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        updatePermisosDisplay();
    });
});

function updatePermisosDisplay() {
    const selectedRoles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked'))
        .map(cb => cb.value);
    
    // Aquí podrías hacer una llamada AJAX para obtener los permisos
    // o tenerlos precargados en un objeto JavaScript
    console.log('Roles seleccionados:', selectedRoles);
}
```

---

## 🧪 Testing Recomendado

Después de implementar estos cambios, prueba:

1. ✅ Crear un usuario evaluado con documento
2. ✅ Crear un usuario empresa con roles
3. ✅ Editar un usuario y cambiar sus roles
4. ✅ Verificar que los permisos se apliquen correctamente
5. ✅ Probar filtros en el listado de usuarios

---

## 📝 Notas Importantes

1. **Compatibilidad**: El sistema mantiene `role_as` para compatibilidad con código existente
2. **Migración**: Los usuarios existentes necesitarán roles asignados
3. **Permisos**: Solo admins pueden gestionar roles
4. **Validación**: El documento es obligatorio solo para evaluados

---

## 🚀 Siguiente Paso

Actualiza las vistas siguiendo los ejemplos de código proporcionados arriba.
