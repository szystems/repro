{{--
  Vista de lectura compartida (portal empresa + admin). Usa presentación unificada alineada al PDF.
  Variables: $cuestionario, $numeroSeccion, $nombreSeccion
--}}
@include('shared.cuestionario.seccion-contenido', [
    'cuestionario' => $cuestionario,
    'numeroSeccion' => $numeroSeccion,
    'nombreSeccion' => $nombreSeccion,
    'soloEmpresa' => true,
    'completada' => (bool) ($cuestionario->completado ?? $cuestionario->estado === 'completado'),
    'ocultarEstadoSeccion' => true,
    'fotoCandidatoUrl' => null,
])
