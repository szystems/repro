@php
    $historial = ($evaluado->relationLoaded('historialEstados')
        ? $evaluado->historialEstados
        : $evaluado->historialEstados()->with('usuario')->get());

    if ($paraEmpresa ?? false) {
        $historial = $historial->where('campo', '!=', 'estado_orden')->values();
    }
@endphp
@if($historial->isNotEmpty())
<div class="mb-3">
    <details>
        <summary class="text-muted small" style="cursor:pointer;">
            <i class="bi bi-clock-history"></i> Historial de cambios ({{ $historial->count() }})
        </summary>
        <div class="table-responsive mt-2">
            <table class="table table-sm table-bordered small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Campo</th>
                        <th>Anterior</th>
                        <th>Nuevo</th>
                        <th>Usuario</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historial as $entrada)
                    <tr>
                        <td class="text-nowrap">{{ $entrada->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @php
                                $campoLabel = match($entrada->campo) {
                                    'estado_evaluacion'   => 'Estado de Evaluación',
                                    'estado_formulario'   => 'Estado de Formulario',
                                    'estado_programacion' => 'Estado de Programación',
                                    'estado_orden'        => 'Estado de Orden',
                                    'modalidad'           => 'Modalidad',
                                    default               => ucfirst($entrada->campo),
                                };
                            @endphp
                            <span class="badge bg-light text-dark border">{{ $campoLabel }}</span>
                        </td>
                        <td class="text-muted">{{ $entrada->estado_anterior ?: '—' }}</td>
                        <td><strong>{{ $entrada->estado_nuevo }}</strong></td>
                        <td>{{ $entrada->usuario?->name ?? 'Sistema' }}</td>
                        <td class="text-muted">{{ $entrada->observacion ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
</div>
@endif
