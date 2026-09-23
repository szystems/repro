@php
    $notasObservacion = $evaluado->observacionesParaMostrar();
    $puedeCorregirObservacion = auth()->check() && (int) auth()->user()->role_as >= 2;
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
                    <td style="white-space: pre-wrap;">{{ $nota->texto }}
                        @if($puedeCorregirObservacion)
                            <details class="mt-1">
                                <summary class="text-muted" style="cursor:pointer;">Editar</summary>
                                <form method="POST"
                                      action="{{ $nota->id
                                          ? route('evaluados.corregir-observacion', [$evaluado, $nota])
                                          : route('evaluados.corregir-observacion-inicial', $evaluado) }}"
                                      class="mt-1">
                                    @csrf
                                    @method('PATCH')
                                    <textarea class="form-control form-control-sm" name="observaciones" rows="2" maxlength="2000" required>{{ $nota->texto }}</textarea>
                                    <p class="text-muted small mb-1 mt-1">Corrige este comentario. Los demás y la fecha se quedan igual.</p>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Guardar corrección</button>
                                </form>
                            </details>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
