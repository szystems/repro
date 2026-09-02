<div class="ayuda-articulo">
    <p class="lead">Cómo crear y editar usuarios REPRO, asignar permisos, editar roles y desactivar cuentas sin perder el historial.</p>

    <h5 id="acceso"><i class="bi bi-door-open me-2"></i>Dónde se administra</h5>
    <ul>
        <li><strong>Usuarios</strong> — menú Administración → Seguridad → Usuarios (<code>/users</code>).</li>
        <li><strong>Roles</strong> — Administración → Seguridad → Roles y Permisos (<code>/admin/roles</code>).</li>
        <li>Solo el <strong>administrador</strong> puede eliminar usuarios y gestionar roles del sistema.</li>
    </ul>
    <p>Los usuarios de empresas cliente <strong>no</strong> se gestionan aquí. El titular los administra en el portal cliente: <a href="{{ route('ayuda.show', 'usuarios-empresa') }}">Gestionar usuarios de mi empresa</a>.</p>

    <h5 id="tipos"><i class="bi bi-people me-2"></i>Tipos de usuario</h5>
    <table class="table table-bordered">
        <thead>
            <tr><th>Tipo</th><th>Para quién</th><th>Qué hace</th></tr>
        </thead>
        <tbody>
            <tr><td>Administrador</td><td>REPRO</td><td>Acceso total: usuarios, roles, archivar órdenes, configuración.</td></tr>
            <tr><td>Personal Repro</td><td>Empleados internos</td><td>Operación diaria. Los permisos se marcan por persona al editar el usuario.</td></tr>
            <tr><td>Usuario Empresa</td><td>Clientes</td><td>Portal de la empresa. El titular puede crear trabajadores con permisos limitados.</td></tr>
        </tbody>
    </table>

    <h5 id="editar-usuario"><i class="bi bi-pencil-square me-2"></i>Crear y editar un usuario</h5>
    <ol>
        <li>En Usuarios pulse <strong>Agregar</strong> o el lápiz del usuario.</li>
        <li>Complete nombre, correo y tipo de usuario.</li>
        <li>Si es Personal Repro, marque los <strong>permisos del usuario</strong> (órdenes, cuestionarios, calendario, etc.).</li>
        <li>Pulse <strong>Guardar cambios</strong>.</li>
    </ol>
    @include('ayuda.partials.callout', [
        'tipo' => 'info',
        'titulo' => 'Permisos individuales:',
        'contenido' => 'Al guardar un empleado REPRO, el sistema guarda sus checkboxes de forma interna. <strong>Eso no debe crear un rol visible</strong> en Gestión de Roles. Si un empleado necesita «Editar órdenes», márquelo aquí; ya podrá abrir la orden.',
    ])

    <h5 id="roles"><i class="bi bi-shield-check me-2"></i>Roles del sistema</h5>
    <p>En <strong>Gestión de Roles</strong> solo aparecen los roles del sistema (Administrador, Personal Repro, Usuario Empresa y los personalizados que usted cree, por ejemplo «Poligrafista Quetzaltenango»).</p>
    <ul>
        <li>Puede <strong>editar</strong> un rol y cambiar su matriz de permisos.</li>
        <li>Puede <strong>crear</strong> un rol nuevo y asignárselo a varios usuarios.</li>
        <li>Los permisos de una sola persona se editan en <strong>Usuarios</strong>, no aquí.</li>
    </ul>
    @include('ayuda.partials.callout', [
        'tipo' => 'warning',
        'titulo' => 'Si al editar un usuario «aparecía un rol nuevo»:',
        'contenido' => 'Era un rol interno de esa persona. Ya no se lista en Roles. Para cambiar lo que puede hacer ese empleado, ábralo en Usuarios y marque o desmarque los permisos.',
    ])

    <h5 id="eliminar"><i class="bi bi-person-x me-2"></i>Eliminar un usuario</h5>
    <p>Eliminar <strong>no borra el historial</strong> (órdenes, evaluaciones ni documentos). El usuario se <strong>desactiva</strong> y deja de poder iniciar sesión. El correo se libera para poder crear otra cuenta con el mismo email.</p>
    <p>Quién puede hacerlo: solo un <strong>administrador</strong>, con el botón Eliminar del listado.</p>
    <table class="table table-bordered">
        <thead>
            <tr><th>Caso</th><th>¿Se puede eliminar?</th></tr>
        </thead>
        <tbody>
            <tr><td>Empleado REPRO u otro administrador (si queda al menos uno)</td><td class="text-success">Sí — se desactiva</td></tr>
            <tr><td>Titular (usuario principal) de una empresa</td><td class="text-success">Sí — se desactiva desde este listado (no desde el portal cliente). Asigne otro titular si la empresa sigue operando.</td></tr>
            <tr><td>El último administrador del sistema</td><td class="text-danger">No</td></tr>
            <tr><td>Su propia cuenta</td><td class="text-danger">No</td></tr>
        </tbody>
    </table>

    <h5 id="permisos-ordenes"><i class="bi bi-file-earmark-text me-2"></i>Permiso «Editar órdenes»</h5>
    <p>Si el empleado tiene marcado <strong>Editar órdenes</strong> (y <strong>Ver órdenes</strong>), debe poder abrir la pantalla de edición. No hace falta que figure el rol llamado «repro» en la lista de roles: basta el tipo Personal Repro y el permiso marcado.</p>
</div>
