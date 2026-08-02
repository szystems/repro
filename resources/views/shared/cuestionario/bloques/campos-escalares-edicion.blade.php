{{-- Edición de campos escalares --}}
@props(['campos' => [], 'respuestas' => [], 'slug' => ''])

@if(count($campos) > 0)
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">Datos de la sección</h6></div>
        <div class="card-body">
            <div class="row">
                @foreach($campos as $campo)
                    @php $valor = old('respuestas_campo.'.$slug.'.'.$campo['key'], $respuestas[$campo['key']] ?? ''); @endphp
                    <div class="col-md-6 form-group mb-3">
                        <label class="form-label">{{ $campo['label'] }}</label>
                        <textarea class="form-control"
                                  name="respuestas_campo[{{ $slug }}][{{ $campo['key'] }}]"
                                  rows="2">{{ $valor }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
