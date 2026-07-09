{{-- Sección 6: Información Socioeconómica Complementaria (solo formulario socio) --}}
@php
    use App\Support\SocioeconomicoComplementariaCampos;
    use App\Support\TablaDinamica;

    $tablas = $tablas ?? [];
    $soloEmpresa = $soloEmpresa ?? false;
@endphp
<div class="section-content">
    @if(!($ocultarEstadoSeccion ?? false))
        @if($completada ?? false)
            <div class="alert alert-success mb-3"><i class="bi bi-check-circle-fill"></i> Sección completada</div>
        @else
            <div class="alert alert-warning mb-3"><i class="bi bi-exclamation-triangle"></i> Sección pendiente o incompleta</div>
        @endif
    @endif

    <h5 class="section-title mb-4">
        <i class="bi bi-pie-chart"></i> {{ $nombreSeccion ?? 'Información Socioeconómica Complementaria' }}
    </h5>

    @if(!empty($tablas['referencias_familiares']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Referencias familiares</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['referencias_familiares'],
                    'columnas' => TablaDinamica::columnasReferenciasFamiliares(),
                ])
            </div>
        </div>
    @endif

    @if(!empty($tablas['referencias_personales']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Referencias personales</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['referencias_personales'],
                    'columnas' => TablaDinamica::columnasReferenciasPersonales(),
                ])
            </div>
        </div>
    @endif

    @if(!$soloEmpresa && !empty($tablas['referencias_vecinales']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Referencias vecinales</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['referencias_vecinales'],
                    'columnas' => TablaDinamica::columnasReferenciasVecinales(),
                ])
            </div>
        </div>
    @endif

    @if(!empty($tablas['referencias_laborales']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Referencias laborales</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['referencias_laborales'],
                    'columnas' => TablaDinamica::columnasReferenciasLaborales(),
                ])
            </div>
        </div>
    @endif

    @if(!empty($tablas['bienes']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Bienes y pertenencias</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['bienes'],
                    'columnas' => TablaDinamica::columnasBienes(),
                ])
                @if(!empty($respuestas['bienes_total']))
                    <p class="mb-0 mt-2"><strong>Total estimado:</strong> Q{{ number_format((float) $respuestas['bienes_total'], 2) }}</p>
                @endif
            </div>
        </div>
    @endif

    @if(!empty($tablas['presupuesto']))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Presupuesto personal</h6></div>
            <div class="card-body">
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas['presupuesto'],
                    'columnas' => TablaDinamica::columnasPresupuesto(),
                ])
                @if(!empty($respuestas['presupuesto_total']))
                    <p class="mb-0 mt-2"><strong>Total mensual:</strong> Q{{ number_format((float) $respuestas['presupuesto_total'], 2) }}</p>
                @endif
            </div>
        </div>
    @endif

    @if(!$soloEmpresa && collect($respuestas ?? [])->keys()->contains(fn ($k) => str_starts_with($k, 'viv_')))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Información de vivienda</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    @if(!empty($respuestas['viv_tiempo_residencia']))
                    <tr><td class="fw-bold">Tiempo en domicilio:</td><td>{{ $respuestas['viv_tiempo_residencia'] }}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_tipo_vivienda']))
                    <tr><td class="fw-bold">Tipo de vivienda:</td><td>{{ SocioeconomicoComplementariaCampos::tiposVivienda()[$respuestas['viv_tipo_vivienda']] ?? $respuestas['viv_tipo_vivienda'] }}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_propietario']))
                    <tr><td class="fw-bold">Propietario:</td><td>{{ $respuestas['viv_propietario'] }}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_monto_alquiler']))
                    <tr><td class="fw-bold">Alquiler mensual:</td><td>Q{{ number_format((float) $respuestas['viv_monto_alquiler'], 2) }}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_num_habitantes']))
                    <tr><td class="fw-bold">Habitantes:</td><td>{{ $respuestas['viv_num_habitantes'] }}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_refs_ubicacion']))
                    <tr><td class="fw-bold">Referencias ubicación:</td><td>{!! nl2br(e($respuestas['viv_refs_ubicacion'])) !!}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_zona_riesgo']))
                    <tr><td class="fw-bold">Zona de riesgo:</td><td>{{ $respuestas['viv_zona_riesgo'] === 'si' ? 'Sí' : 'No' }}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_detalle_zona_riesgo']))
                    <tr><td class="fw-bold">Detalle zona riesgo:</td><td>{!! nl2br(e($respuestas['viv_detalle_zona_riesgo'])) !!}</td></tr>
                    @endif
                    @if(!empty($respuestas['viv_direcciones_anteriores']))
                    <tr><td class="fw-bold">Direcciones anteriores:</td><td>{!! nl2br(e($respuestas['viv_direcciones_anteriores'])) !!}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    @endif

    @if(empty($tablas) && empty(array_filter($respuestas ?? [])))
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle"></i> No hay información registrada en esta sección.
        </div>
    @endif
</div>
