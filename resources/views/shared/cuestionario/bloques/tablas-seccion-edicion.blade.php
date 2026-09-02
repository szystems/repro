{{-- Tablas dinámicas editables (admin REPRO / trabajador) — mismo motor que el formulario del candidato --}}
@props(['configs' => [], 'tablas' => [], 'slug' => '', 'respuestas' => []])

@foreach($configs as $config)
    @php
        $columnas = call_user_func([\App\Support\TablaDinamica::class, $config['metodo']]);
        $name = "respuestas_tablas[{$slug}][{$config['key']}]";
        $filas = $tablas[$config['key']] ?? [];
        $minFilas = 0;
        if ($config['key'] === 'empleos' || $config['key'] === 'empleo_actual') {
            $exp = $respuestas['experiencia_previa'] ?? $respuestas['tiene_experiencia'] ?? null;
            $minFilas = in_array((string) $exp, ['si', '1'], true) ? 1 : 0;
        } elseif ($config['key'] === 'hijos') {
            $minFilas = ($respuestas['tiene_hijos'] ?? '') === 'si' ? 1 : 0;
        } elseif ($config['key'] === 'deudas') {
            $minFilas = ($respuestas['tiene_deudas'] ?? '') === 'si' ? 1 : 0;
        }
    @endphp
    <x-tabla-dinamica
        :name="$name"
        :titulo="$config['titulo']"
        :columnas="$columnas"
        :filas="$filas"
        :minFilas="$minFilas"
        :textoAgregar="'Agregar '.strtolower($config['titulo'])"
        textoEliminar="Eliminar"
    />
@endforeach
