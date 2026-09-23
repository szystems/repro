{{-- Cinco preguntas de preempleo de esta empresa. Vacío = las cinco generales. --}}
@php
    $registroPreguntas = isset($empresa) ? $empresa->preguntasPreempleo : null;
    $valoresPrincipales = old('preguntas_preempleo', $registroPreguntas ? $registroPreguntas->espaciosPrincipales() : ['', '', '', '', '']);
    $valoresPuesto = old('preguntas_preempleo_puesto', $registroPreguntas ? $registroPreguntas->espaciosPuesto() : ['', '', '', '', '']);
    $nombrePuesto = old('preguntas_puesto_nombre', $registroPreguntas->puesto_nombre ?? '');
@endphp
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Preguntas de preempleo</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small">
            Si las deja vacías, las órdenes nuevas de preempleo usan las cinco preguntas generales.
            Si las escribe, esas salen al final del informe en polígrafo y en VSA.
            Periódica y específica siguen en blanco. Las órdenes ya creadas no cambian.
        </p>
        @foreach(range(0, 4) as $i)
            <div class="mb-2">
                <label class="form-label" for="preguntas_preempleo_{{ $i }}">Pregunta {{ $i + 1 }}</label>
                <textarea class="form-control" id="preguntas_preempleo_{{ $i }}" name="preguntas_preempleo[{{ $i }}]" rows="2" maxlength="500">{{ $valoresPrincipales[$i] ?? '' }}</textarea>
            </div>
        @endforeach

        <hr>
        <h6 class="mb-2">Segundo juego por puesto</h6>
        <p class="text-muted small">
            Solo si esta empresa pide otras preguntas según el puesto. En la orden se elige cuál de los dos usar.
            Si lo deja vacío, ese paso no aparece.
        </p>
        <div class="mb-2">
            <label class="form-label" for="preguntas_puesto_nombre">Nombre del puesto</label>
            <input type="text" class="form-control @error('preguntas_puesto_nombre') is-invalid @enderror" id="preguntas_puesto_nombre" name="preguntas_puesto_nombre" maxlength="100" value="{{ $nombrePuesto }}">
            @error('preguntas_puesto_nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @foreach(range(0, 4) as $i)
            <div class="mb-2">
                <label class="form-label" for="preguntas_preempleo_puesto_{{ $i }}">Pregunta del puesto {{ $i + 1 }}</label>
                <textarea class="form-control" id="preguntas_preempleo_puesto_{{ $i }}" name="preguntas_preempleo_puesto[{{ $i }}]" rows="2" maxlength="500">{{ $valoresPuesto[$i] ?? '' }}</textarea>
            </div>
        @endforeach
    </div>
</div>
