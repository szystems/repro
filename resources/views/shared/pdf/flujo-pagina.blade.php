{{-- Reglas DomPDF: flujo continuo sin bloques enormes que empujen secciones a la siguiente página. --}}
<style>
    .seccion,
    .candidato-bloque,
    .info-general,
    .bloque,
    .observaciones-box {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    .seccion-titulo,
    .candidato-header,
    .subseccion-titulo,
    .repro-header,
    tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    .firma-container {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* Usar solo cuando sea realmente necesario forzar salto */
    .page-break-force {
        page-break-before: always;
    }
</style>
