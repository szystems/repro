<h5 class="mt-4 mb-3" id="referencia-botones"><i class="bi bi-ui-checks me-2"></i>Referencia de botones</h5>
<div class="table-responsive">
    <table class="table table-sm table-bordered ayuda-botones-tabla">
        <thead class="table-light">
            <tr>
                <th style="width:50px">#</th>
                <th style="width:35%">Botón / acción</th>
                <th>Qué hace</th>
            </tr>
        </thead>
        <tbody>
            @foreach($botones as $btn)
            <tr>
                <td class="text-center fw-bold text-danger">{{ $btn['numero'] ?? '—' }}</td>
                <td>
                    @if(!empty($btn['clase']))
                        <button type="button" class="{{ $btn['clase'] }}" tabindex="-1" disabled>
                            @if(!empty($btn['icono']))<i class="bi {{ $btn['icono'] }}"></i>@endif
                            {{ $btn['nombre'] }}
                        </button>
                    @else
                        {{ $btn['nombre'] }}
                    @endif
                </td>
                <td>{{ $btn['accion'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
