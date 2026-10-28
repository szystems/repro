@if(($evaluado->tipo_servicio ?? '') === 'poligrafo')
    @php
        $firmaActual = \App\Support\InformeWordFirmas::claveEfectiva($evaluado);
    @endphp
    <form action="{{ route('evaluados.firma-informe', $evaluado) }}" method="POST" class="d-inline-flex align-items-center gap-2">
        @csrf
        <label class="small text-muted mb-0" for="firma-informe-{{ $evaluado->id }}">Firma</label>
        <select name="firmante" id="firma-informe-{{ $evaluado->id }}" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
            <option value="todas" @selected($firmaActual === null)>Las tres firmas</option>
            @foreach(\App\Support\InformeWordFirmas::FIRMANTES as $clave => $datos)
                <option value="{{ $clave }}" @selected($firmaActual === $clave)>{{ $datos['nombre'] }}</option>
            @endforeach
        </select>
    </form>
@endif
