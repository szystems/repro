{{-- Tablas dinámicas en modo lectura --}}
@props(['configs' => [], 'tablas' => []])

@foreach($configs as $config)
    @if(!empty($tablas[$config['key']]))
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ $config['titulo'] }}</h6></div>
            <div class="card-body">
                @php $columnas = call_user_func([\App\Support\TablaDinamica::class, $config['metodo']]); @endphp
                @include('admin.cuestionarios.partials.tabla-dinamica-resumen', [
                    'filas' => $tablas[$config['key']],
                    'columnas' => $columnas,
                ])
            </div>
        </div>
    @endif
@endforeach
