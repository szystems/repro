<div class="ayuda-articulo">
    <p class="lead">Listado centralizado de cuestionarios completados o en proceso, con acceso a consulta, edición y exportación.</p>

    <h5>Listado (<code>/cuestionarios</code>)</h5>
    <ul>
        <li>Filtros por empresa, sede, estado, tipo de formulario y fechas.</li>
        <li>Cada fila muestra candidato, <strong>puesto</strong>, empresa, sede, tipo de servicio y estado.</li>
        <li>En <strong>Contacto</strong> hay icono de WhatsApp junto al teléfono. En acciones, el botón verde <strong>WhatsApp al candidato</strong> abre el chat (solo si hay número).</li>
        <li>Los evaluados de órdenes <strong>archivadas</strong> no aparecen. Ver <a href="{{ route('ayuda.show', 'archivar-ordenes') }}">Archivar órdenes</a>.</li>
    </ul>
    <p class="mb-3">
        <span class="btn btn-sm btn-success disabled"><i class="bi bi-whatsapp"></i></span>
        <span class="small text-muted ms-1">Así se ve el botón verde en el listado (REPRO y portal cliente).</span>
    </p>

    <h5>Vista de consulta</h5>
    <ul>
        <li>Respuestas organizadas por secciones (pestañas).</li>
        <li>Descarga de PDF del cuestionario.</li>
        <li>Foto del candidato si fue capturada.</li>
    </ul>

    <h5>Edición (<code>/cuestionarios/{id}/editar</code>)</h5>
    <ul>
        <li>Modificar respuestas del candidato (requiere permiso <em>evaluaciones.editar</em>).</li>
        <li>Vista previa y descarga de informe Word.</li>
        <li>Marcar cuestionario como completado.</li>
        <li>Notas del evaluador en campos específicos.</li>
    </ul>

    <p>El portal cliente ve una versión de solo lectura en <strong>Estado de Procesos</strong>, filtrada a su empresa.</p>
</div>
