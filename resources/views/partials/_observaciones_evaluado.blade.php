@php
    $notasObservacion = $evaluado->observacionesParaMostrar();
@endphp
@if($notasObservacion->isNotEmpty())
<div class="mb-3">
    <small class="text-muted d-block">Observaciones del evaluado</small>
    <div class="table-responsive mt-1">
        <table class="table table-sm table-bordered small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notasObservacion as $nota)
                <tr>
                    <td class="text-nowrap">
                        @if($nota->sin_fecha_original || ! $nota->created_at)
                            —
                        @else
                            {{ $nota->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                        @endif
                    </td>
                    <td>{{ $nota->usuario?->name ?? '—' }}</td>
                    <td style="white-space: pre-wrap;">{{ $nota->texto }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
