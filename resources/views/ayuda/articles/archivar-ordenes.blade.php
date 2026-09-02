<div class="ayuda-articulo">
    <p class="lead">Archivar oculta la orden y a sus evaluados de los listados de trabajo. El expediente se conserva; las órdenes no se eliminan.</p>

    <h5 id="quien"><i class="bi bi-shield-lock me-2"></i>Quién puede archivar</h5>
    <p>Solo el <strong>administrador</strong>. El botón está en el listado de órdenes y en el detalle de la orden.</p>
    <p>El sistema pedirá confirmación. Tras archivar, vuelve al listado de órdenes activas.</p>

    <h5 id="que-oculta"><i class="bi bi-eye-slash me-2"></i>Qué deja de verse</h5>
    <p>La orden y sus candidatos <strong>salen de las pantallas de trabajo</strong>:</p>
    <ul>
        <li>Listado de órdenes (filtro «Órdenes activas»).</li>
        <li>Gestión de Cuestionario – Candidatos.</li>
        <li>Historial por DPI o nombre.</li>
        <li>Calendario, reportes y listados de sedes que cuentan evaluados.</li>
    </ul>
    @include('ayuda.partials.callout', [
        'tipo' => 'success',
        'titulo' => 'Comportamiento correcto:',
        'contenido' => 'Si archiva una orden y el evaluado <strong>sigue</strong> saliendo en candidatos o en historial DPI, avise a soporte: eso ya no debe ocurrir.',
    ])

    <h5 id="consultar"><i class="bi bi-archive me-2"></i>Cómo ver órdenes archivadas</h5>
    <ol>
        <li>Vaya a <strong>Órdenes de Evaluación</strong>.</li>
        <li>En el filtro <strong>Vista</strong> elija <strong>Órdenes archivadas</strong>.</li>
        <li>Busque por código o empresa si lo necesita.</li>
    </ol>
    <p>El expediente (evaluados, documentos, cuestionario) se conserva. Archivar no es borrar.</p>

    <h5 id="no-confundir"><i class="bi bi-slash-circle me-2"></i>No confundir con</h5>
    <ul>
        <li><strong>Cancelar</strong> una orden o un evaluado — cambia el estado, pero la orden sigue en el listado activo.</li>
        <li><strong>Eliminar usuario</strong> — desactiva la cuenta; no archiva órdenes. Ver <a href="{{ route('ayuda.show', 'seguridad-usuarios') }}">Usuarios, roles y permisos</a>.</li>
    </ul>
</div>
