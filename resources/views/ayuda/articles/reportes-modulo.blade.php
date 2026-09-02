<div class="ayuda-articulo">
    <p class="lead">Estadísticas y exportaciones de evaluaciones y empresas.</p>

    <h5>INFORMES DE EMPRESAS (<code>/reportes/evaluaciones</code>)</h5>
    <ul>
        <li>Filtros por empresa, sede, estado, fechas y tipo de servicio.</li>
        <li>Exportar a <strong>PDF</strong> o <strong>Excel</strong> (en servidores sin XMLWriter baja <code>.xls</code> HTML, no XLSX).</li>
        <li>El Excel de informes y del listado de órdenes se controla en Roles con <strong>Descargar Excel de informes y órdenes</strong> (<code>reportes.generar</code>). Los clientes bajan solo lo suyo (procesos confidenciales filtrados).</li>
        <li>Disponible para REPRO y clientes (solo sus propios datos).</li>
        @if(Auth::user() && Auth::user()->role_as >= 3)
        <li>Las órdenes archivadas no entran en los conteos. Ver <a href="{{ route('ayuda.show', 'archivar-ordenes') }}">Archivar órdenes</a>.</li>
        @endif
    </ul>

    <h5>Reporte de empresas (<code>/reportes/empresas</code>)</h5>
    <ul>
        <li>Solo REPRO — resumen de actividad por empresa cliente.</li>
        <li>Excel sujeto al mismo permiso <code>reportes.generar</code>.</li>
    </ul>

    <h5>Padrón de empresas (<code>/empresas</code>)</h5>
    <ul>
        <li>PDF y Excel del listado: solo administradores, o roles con <strong>Descargar padrón de empresas</strong> (<code>empresas.exportar</code>).</li>
    </ul>
</div>
