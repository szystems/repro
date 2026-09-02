{{-- Tabla editable de preguntas poligráficas (última hoja informe Word) --}}
@if(($puedeGestionarNotasEvaluador ?? false) && ($informeWordPoligrafico ?? false))
@php
    $filas = old('preguntas_poligraficas', $preguntasPoligraficas ?? []);
    $usaPuntuacion = $preguntasPoligraficasUsaPuntuacion ?? true;
    $colspanVacio = ($soloLectura ?? false)
        ? ($usaPuntuacion ? 5 : 4)
        : ($usaPuntuacion ? 6 : 5);
@endphp
<div class="card mt-4 border-primary">
    <div class="card-header bg-primary bg-opacity-10">
        <h6 class="mb-0">
            <i class="bi bi-table"></i> Preguntas {{ $usaPuntuacion ? 'poligráficas' : 'VSA' }} (informe Word)
        </h6>
        <small class="text-muted d-block mt-1">
            Complete la tabla de la última hoja del informe. La columna <strong>Respuesta</strong> (No / Sí) se copia al Word; no la deje vacía.
            @if($usaPuntuacion)
                Columnas: pregunta, respuesta del evaluado, resultado (NDI/DI/INCONCLUSO) y puntuación.
            @else
                Columnas: pregunta, respuesta del evaluado y resultado (NDI/DI/INCONCLUSO). VSA no usa puntuación.
            @endif
        </small>
    </div>
    <div class="card-body p-0">
        @if(!($soloLectura ?? false))
            <input type="hidden" name="_preguntas_poligraficas" value="1">
        @endif
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="tablaPreguntasPoligraficas">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Pregunta</th>
                        <th style="width:120px;">Respuesta</th>
                        <th style="width:100px;">Resultado</th>
                        @if($usaPuntuacion)
                            <th style="width:90px;">Puntuación</th>
                        @endif
                        @if(!($soloLectura ?? false))
                            <th style="width:50px;"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($filas as $i => $fila)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                @if($soloLectura ?? false)
                                    <div class="p-2">{{ $fila['pregunta'] ?? '' }}</div>
                                @else
                                    <textarea class="form-control form-control-sm"
                                              name="preguntas_poligraficas[{{ $i }}][pregunta]"
                                              rows="2"
                                              required>{{ $fila['pregunta'] ?? '' }}</textarea>
                                @endif
                            </td>
                            <td>
                                @php
                                    $respuestaFila = $fila['respuesta'] ?? 'No';
                                    if ($respuestaFila === '' || $respuestaFila === '—') {
                                        $respuestaFila = 'No';
                                    }
                                    $opcionesRespuesta = \App\Support\InformeWordPreguntasPoligraficas::RESPUESTAS;
                                    if (! in_array($respuestaFila, $opcionesRespuesta, true)) {
                                        $opcionesRespuesta[] = $respuestaFila;
                                    }
                                @endphp
                                @if($soloLectura ?? false)
                                    {{ $respuestaFila }}
                                @else
                                    <select class="form-control form-control-sm"
                                            name="preguntas_poligraficas[{{ $i }}][respuesta]">
                                        @foreach($opcionesRespuesta as $opt)
                                            <option value="{{ $opt }}" @selected($respuestaFila === $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td>
                                @if($soloLectura ?? false)
                                    {{ $fila['resultado'] ?? '—' }}
                                @else
                                    <select class="form-control form-control-sm"
                                            name="preguntas_poligraficas[{{ $i }}][resultado]">
                                        @foreach(['', 'NDI', 'DI', 'INCONCLUSO'] as $opt)
                                            <option value="{{ $opt }}" @selected(($fila['resultado'] ?? '') === $opt)>{{ $opt === '' ? '—' : $opt }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            @if($usaPuntuacion)
                                <td>
                                    @if($soloLectura ?? false)
                                        {{ $fila['puntuacion'] ?? '—' }}
                                    @else
                                        <input type="text" class="form-control form-control-sm"
                                               name="preguntas_poligraficas[{{ $i }}][puntuacion]"
                                               value="{{ $fila['puntuacion'] ?? '' }}">
                                    @endif
                                </td>
                            @elseif(!($soloLectura ?? false))
                                <td class="d-none"><input type="hidden" name="preguntas_poligraficas[{{ $i }}][puntuacion]" value=""></td>
                            @endif
                            @if(!($soloLectura ?? false))
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-pregunta-poligrafica" title="Quitar fila">&times;</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr class="fila-vacia-poligrafica">
                            <td colspan="{{ $colspanVacio }}" class="text-muted text-center py-3">Sin preguntas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!($soloLectura ?? false))
            <div class="p-3 border-top">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarPreguntaPoligrafica">
                    <i class="bi bi-plus-lg"></i> Agregar pregunta
                </button>
            </div>
        @endif
    </div>
</div>

@if(!($soloLectura ?? false))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('#tablaPreguntasPoligraficas tbody');
    const btnAgregar = document.getElementById('btnAgregarPreguntaPoligrafica');
    const usaPuntuacion = @json($usaPuntuacion);
    const colspanVacio = {{ $colspanVacio }};
    if (!tbody || !btnAgregar) return;

    function reindexFilas() {
        tbody.querySelectorAll('tr').forEach(function (tr, index) {
            if (tr.classList.contains('fila-vacia-poligrafica')) return;
            const num = tr.querySelector('td:first-child');
            if (num) num.textContent = String(index + 1);
            tr.querySelectorAll('[name^="preguntas_poligraficas"]').forEach(function (el) {
                el.name = el.name.replace(/preguntas_poligraficas\[\d+\]/, 'preguntas_poligraficas[' + index + ']');
            });
        });
    }

    btnAgregar.addEventListener('click', function () {
        const vacia = tbody.querySelector('.fila-vacia-poligrafica');
        if (vacia) vacia.remove();
        const index = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        let html = `
            <td class="text-muted">${index + 1}</td>
            <td><textarea class="form-control form-control-sm" name="preguntas_poligraficas[${index}][pregunta]" rows="2" required></textarea></td>
            <td><select class="form-control form-control-sm" name="preguntas_poligraficas[${index}][respuesta]"><option value="No" selected>No</option><option value="Sí">Sí</option></select></td>
            <td><select class="form-control form-control-sm" name="preguntas_poligraficas[${index}][resultado]"><option value="">—</option><option value="NDI" selected>NDI</option><option value="DI">DI</option><option value="INCONCLUSO">INCONCLUSO</option></select></td>`;
        if (usaPuntuacion) {
            html += `<td><input type="text" class="form-control form-control-sm" name="preguntas_poligraficas[${index}][puntuacion]"></td>`;
        } else {
            html += `<input type="hidden" name="preguntas_poligraficas[${index}][puntuacion]" value="">`;
        }
        html += `<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-quitar-pregunta-poligrafica" title="Quitar fila">&times;</button></td>`;
        tr.innerHTML = html;
        tbody.appendChild(tr);
    });

    tbody.addEventListener('click', function (e) {
        if (!e.target.classList.contains('btn-quitar-pregunta-poligrafica')) return;
        e.target.closest('tr')?.remove();
        if (tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = '<tr class="fila-vacia-poligrafica"><td colspan="' + colspanVacio + '" class="text-muted text-center py-3">Sin preguntas registradas.</td></tr>';
        } else {
            reindexFilas();
        }
    });
});
</script>
@endpush
@endif
@endif
