<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\Orden;

/** Rellena encabezado y tablas de plantillas Word oficiales preservando diseño tabular. */
class InformeWordRelleno
{
    /**
     * @param  array{variante?: string, layout?: string}  $config
     */
    public static function aplicar(string $xml, Orden $orden, EvaluadoOrden $evaluado, array $config): string
    {
        $valores = InformeWordDatos::encabezado($orden, $evaluado);
        $tablas = InformeWordDatos::tablas($evaluado);
        $variante = $config['variante'] ?? InformeWordPlantillas::VARIANTE_PREEMPLEO;
        $layout = $config['layout'] ?? (
            str_contains($xml, 'DATOS GENERALES')
                ? InformeWordPlantillas::LAYOUT_V2
                : InformeWordPlantillas::LAYOUT_LEGACY
        );

        $xml = $layout === InformeWordPlantillas::LAYOUT_V2
            ? self::rellenarEncabezadoV2($xml, $valores, $evaluado)
            : self::rellenarEncabezadoLegacy($xml, $valores, $evaluado);
        $xml = self::rellenarTablas($xml, $tablas, $variante, $evaluado, $valores);
        $xml = self::rellenarMotivoProcedimiento($xml, $evaluado);
        $xml = self::rellenarNarrativas($xml, $orden, $evaluado, $variante);
        $xml = InformeWordXml::evitarTraslapePiePrimeraPagina($xml);
        $xml = InformeWordXml::quitarAvisoConfidencialDuplicadoDelCuerpo($xml);

        return self::optimizarLayoutSecciones($xml, $variante);
    }

    private static function finalizarTablaAspectoEconomico(string $xml): string
    {
        $anchoReferencia = InformeWordXml::anchoTablaPorMarcador($xml, 'INFORMACIÓN LABORAL')
            ?? InformeWordXml::anchoTablaPorMarcador($xml, 'EMPLEOS:')
            ?? 10790;

        $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'ASPECTO ECONÓMICO', function (string $tabla) use ($anchoReferencia): string {
            $tabla = InformeWordXml::podarSeccionDeudasVacia($tabla);
            $tabla = InformeWordXml::eliminarFilasVaciasOPlaceholder($tabla);
            $tabla = InformeWordXml::expandirTablaAnchoPagina($tabla, $anchoReferencia);
            $tabla = InformeWordXml::extenderFilasDeUnaCeldaAlGrid($tabla);
            $tabla = InformeWordXml::forzarTamanoFuenteFilasPorAncho($tabla, 24, 22, 5);

            return $tabla;
        });

        foreach (['ASPECTO LABORAL', 'INFORMACIÓN COMPLEMENTARIA LABORAL', 'INFORMACIÓN COMPLEMENTARIA', 'ASPECTO ECONÓMICO'] as $marcador) {
            $xml = InformeWordXml::insertarFragmentoTrasTabla(
                $xml,
                $marcador,
                InformeWordXml::parrafoEspacio(160, 80)
            );
        }

        return $xml;
    }

    /**
     * @param  array<string, string>  $valores
     */
    private static function rellenarEncabezadoLegacy(string $xml, array $valores, EvaluadoOrden $evaluado): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'Proceso:', function (string $tabla) use ($valores, $evaluado): string {
            $tabla = self::actualizarEtiquetaProceso($tabla, $valores['proceso'], $evaluado);

            $filas = InformeWordXml::filasTabla($tabla);
            if (isset($filas[0])) {
                $filas[0] = InformeWordXml::establecerCeldasFila($filas[0], [
                    1 => $valores['proceso'],
                    3 => $valores['fecha'],
                ]);
            }

            $mapaFilas = [
                1 => [$valores['nombre']],
                2 => [$valores['puesto']],
                3 => [$valores['empresa']],
                4 => [$valores['agencia']],
                5 => [$valores['dpi']],
                6 => [$valores['telefono']],
                7 => [$valores['lugar_fecha_nacimiento']],
                8 => [$valores['edad']],
                9 => [$valores['direccion']],
                10 => [$valores['resultado']],
                11 => [$valores['observaciones']],
            ];

            foreach ($mapaFilas as $indice => $textos) {
                if (! isset($filas[$indice])) {
                    continue;
                }

                $filas[$indice] = InformeWordXml::establecerFila($filas[$indice], $textos, 1);
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /**
     * Plantillas cliente ago-2026: cabecera Empresa/Agencia/Fecha + DATOS GENERALES + resultado/obs.
     *
     * @param  array<string, string>  $valores
     */
    private static function rellenarEncabezadoV2(string $xml, array $valores, EvaluadoOrden $evaluado): string
    {
        // Marcador estable: en la plantilla cliente "Empresa"/"Fecha" llegan partidos en varios <w:t>.
        $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'Agencia/Sede:', function (string $tabla) use ($valores): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (isset($filas[0])) {
                $filas[0] = InformeWordXml::establecerCeldasFila($filas[0], [
                    1 => $valores['empresa'],
                    3 => $valores['agencia'],
                ]);
            }
            if (isset($filas[1])) {
                $filas[1] = self::establecerValorTrasEtiqueta($filas[1], 'Puesto:', $valores['puesto']);
                $filas[1] = self::establecerValorTrasEtiqueta($filas[1], 'Fecha:', $valores['fecha']);
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });

        $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'DATOS GENERALES', function (string $tabla) use ($valores): string {
            $filas = InformeWordXml::filasTabla($tabla);
            $mapa = [
                1 => $valores['nombres'] ?: $valores['nombre'],
                2 => $valores['apellidos'],
                3 => $valores['edad'],
                4 => $valores['fecha_nacimiento'],
                5 => $valores['lugar_nacimiento'],
                6 => $valores['dpi'],
                7 => $valores['nacionalidad'],
                8 => $valores['estado_civil'],
                9 => $valores['direccion'],
                10 => $valores['telefono'],
                11 => $valores['telefono_emergencia'],
                12 => $valores['correo'],
                13 => $valores['igss'],
                14 => $valores['nit'],
                15 => $valores['licencia'],
            ];

            foreach ($mapa as $indice => $texto) {
                if (! isset($filas[$indice])) {
                    continue;
                }
                $filas[$indice] = InformeWordXml::establecerFila($filas[$indice], [$texto], 1);
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });

        if (! InformeWordResultado::esSocio($evaluado)) {
            $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'RESULTADO', function (string $tabla) use ($valores, $evaluado): string {
                return self::rellenarCuadroResultado($tabla, $valores, $evaluado);
            });
        } else {
            $xml = InformeWordXml::reemplazarTablaPorMarcador(
                $xml,
                InformeWordResultado::MARCADOR_CLASIFICACION_SOCIO,
                fn (string $tabla): string => self::marcarOpcionesTabla(
                    $tabla,
                    InformeWordResultado::opcionMarcadaSocio($evaluado),
                    [InformeWordResultado::class, 'opcionDeTextoSocio'],
                    false
                )
            );
        }

        $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'OBSERVACIONES', function (string $tabla) use ($valores): string {
            // Evitar la fila "Observaciones:" dentro de DATOS FAMILIARES (tabla corta).
            $filas = InformeWordXml::filasTabla($tabla);
            if (count($filas) < 2 || count($filas) > 3) {
                return $tabla;
            }
            if (isset($filas[1])) {
                $filas[1] = InformeWordXml::establecerFila($filas[1], [$valores['observaciones']], 0);
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });

        $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'ASPECTOS A CONSIDERAR', function (string $tabla) use ($valores): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (! isset($filas[1]) || count($filas) > 4) {
                return $tabla;
            }

            $filas[1] = InformeWordXml::establecerFila($filas[1], [$valores['observaciones']], 0);

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });

        return $xml;
    }

    /**
     * N-R1 / N-R2: en el Word solo queda la fila de la opción elegida (con su color de plantilla),
     * sin el prefijo [ X ]. El select de la UI no se toca. Si aún no hay resultado, se deja el cuadro completo.
     *
     * @param  array<string, string>  $valores
     */
    private static function rellenarCuadroResultado(string $tabla, array $valores, EvaluadoOrden $evaluado): string
    {
        $filas = InformeWordXml::filasTabla($tabla);
        if (! isset($filas[0])) {
            return $tabla;
        }

        $opciones = [];
        foreach ($filas as $indice => $fila) {
            if ($indice === 0) {
                continue;
            }

            $opcion = InformeWordResultado::opcionDeTexto(InformeWordXml::textoFila($fila));
            if ($opcion !== null) {
                $opciones[$indice] = $opcion;
            }
        }

        // Plantillas legacy sin cuadro de opciones: se mantiene el valor en una sola fila.
        if ($opciones === []) {
            $filaValor = $filas[1] ?? $filas[0];
            $tabla = InformeWordXml::reconstruirTabla($tabla, [
                $filas[0],
                InformeWordXml::establecerFila($filaValor, [$valores['resultado']], 0),
            ]);

            return InformeWordXml::expandirTablaAnchoPagina($tabla);
        }

        $marcada = InformeWordResultado::opcionMarcada($evaluado);
        $detalles = InformeWordResultado::detalles($evaluado->id);
        $autoDi = InformeWordPreguntasPoligraficas::textoConclusionDi(
            InformeWordPreguntasPoligraficas::filasGuardadas($evaluado->id)
        );

        foreach ($opciones as $indice => $opcion) {
            if ($opcion !== $marcada) {
                continue;
            }

            $celdas = InformeWordXml::celdasFila($filas[$indice]);
            if ($celdas === []) {
                continue;
            }

            $celdas[0] = InformeWordXml::reemplazarTexto($celdas[0], InformeWordResultado::MARCA, '');
            if ($opcion === InformeWordResultado::OPCION_APROBADO) {
                $celdas[0] = InformeWordXml::reemplazarTexto($celdas[0], ' / SIN OBSERVACIONES', '');
                $celdas[0] = InformeWordXml::reemplazarTexto($celdas[0], '/ SIN OBSERVACIONES', '');
                $celdas[0] = InformeWordXml::reemplazarTexto($celdas[0], ' / Sin Observaciones', '');
            }

            if (isset($celdas[1])) {
                $detalle = match ($opcion) {
                    InformeWordResultado::OPCION_NO_APROBADO => ($autoDi !== '' ? $autoDi : $detalles['mentira']),
                    InformeWordResultado::OPCION_EXCEPCION => $detalles['excepcion'],
                    default => '',
                };

                if ($detalle !== '') {
                    $celdas[1] = InformeWordXml::prefijarTextoCeldaFinal($celdas[1], ' ' . $detalle);
                }
            }

            preg_match('/<w:tr\b[^>]*>/', $filas[$indice], $apertura);
            $filas[$indice] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
        }

        return InformeWordXml::reconstruirTabla(
            $tabla,
            self::filasOpcionSeleccionada($filas, $marcada, [InformeWordResultado::class, 'opcionDeTexto'])
        );
    }

    private static function actualizarEtiquetaProceso(string $encabezado, string $proceso, EvaluadoOrden $evaluado): string
    {
        if ($evaluado->tipo_servicio === 'poligrafo') {
            return $encabezado;
        }

        return InformeWordXml::reemplazarTexto($encabezado, 'Polígrafo', match ($evaluado->tipo_servicio) {
            'vsa' => 'VSA',
            'socioeconomico' => 'Estudio Socioeconómico',
            default => ucfirst((string) $evaluado->tipo_servicio),
        });
    }

    /**
     * @param  array<string, mixed>  $tablas
     * @param  array<string, string>  $encabezado
     */
    private static function rellenarTablas(string $xml, array $tablas, string $variante, EvaluadoOrden $evaluado, array $encabezado = []): string
    {
        if ($tablas === []) {
            return $xml;
        }

        $familiar = is_array($tablas['familiar'] ?? null) ? $tablas['familiar'] : [];
        $xml = self::rellenarTablaFamiliar($xml, $familiar);

        // FORMATOS.pdf: periódica/específica omiten pareja y hermanos.
        if (InformeWordPlantillas::esVariantePreempleoLike($variante)) {
            $xml = self::rellenarTablaEstadoCivil($xml, $familiar, $encabezado['estado_civil'] ?? '');
            $xml = self::rellenarTablaExpareja($xml, $familiar);
            $xml = self::rellenarTablaHijos($xml, is_array($familiar['hijos'] ?? null) ? $familiar['hijos'] : []);
            $xml = self::rellenarTablaHermanos($xml, is_array($familiar['hermanos'] ?? null) ? $familiar['hermanos'] : []);
            $xml = self::rellenarTablaComplementaria($xml, is_array($tablas['complementaria'] ?? null) ? $tablas['complementaria'] : []);
        } else {
            $xml = self::rellenarTablaEstadoCivil($xml, $familiar, $encabezado['estado_civil'] ?? '');
            $xml = self::rellenarTablaHijos($xml, is_array($familiar['hijos'] ?? null) ? $familiar['hijos'] : []);
        }

        $xml = self::rellenarTablaAcademica(
            $xml,
            self::filasAcademicasVisibles($evaluado, is_array($tablas['academico'] ?? null) ? $tablas['academico'] : []),
            $variante,
            is_array($tablas['estudios_actuales'] ?? null) ? $tablas['estudios_actuales'] : [],
            self::estudiaActualmenteDesdeCuestionario($evaluado)
        );
        $xml = self::rellenarTablaLaboral($xml, is_array($tablas['laboral'] ?? null) ? $tablas['laboral'] : [], $variante);
        $xml = self::rellenarTablaDeudas($xml, is_array($tablas['deudas'] ?? null) ? $tablas['deudas'] : []);
        $xml = self::rellenarTablaTatuajes($xml, is_array($tablas['tatuajes'] ?? null) ? $tablas['tatuajes'] : []);

        if ($variante === InformeWordPlantillas::VARIANTE_SOCIO
            || ($evaluado->tipo_servicio ?? '') === 'socioeconomico'
            || ($evaluado->tipo_formulario ?? '') === 'socioeconomico') {
            $xml = self::rellenarTablaReferenciasSocio($xml, 'FAMILIARES:', is_array($tablas['referencias_familiares'] ?? null) ? $tablas['referencias_familiares'] : [], [
                'nombre', 'direccion', 'telefono', 'lugar_trabajo', 'parentesco',
            ]);
            $xml = self::rellenarTablaAmistadesSocio(
                $xml,
                is_array($tablas['referencias_personales'] ?? null) ? $tablas['referencias_personales'] : []
            );
            // M-S2: ella llena a mano la verificación laboral; no volcar el historial del candidato.
            $xml = self::rellenarTablaPresupuestoSocio($xml, is_array($tablas['presupuesto'] ?? null) ? $tablas['presupuesto'] : []);
            $xml = self::rellenarTablaBienesSocio($xml, is_array($tablas['bienes'] ?? null) ? $tablas['bienes'] : []);
            $xml = self::rellenarTablaDomicilioSocio($xml, is_array($tablas['domicilio'] ?? null) ? $tablas['domicilio'] : []);
            $xml = self::quitarFilasOtrosAspectosSocio($xml);
        }

        return $xml;
    }

    /**
     * @param  list<array<string, mixed>>  $filas
     * @param  list<string>  $claves
     */
    private static function rellenarTablaReferenciasSocio(string $xml, string $marcador, array $filas, array $claves): string
    {
        if ($filas === []) {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($filas, $claves): string {
            foreach (array_values($filas) as $indice => $fila) {
                $indiceFila = $indice + 2; // 0 título, 1 encabezados
                if ($indiceFila > 10) {
                    break;
                }
                $valores = [];
                foreach ($claves as $clave) {
                    $valores[] = self::texto($fila[$clave] ?? ($clave === 'lugar_trabajo' ? ($fila['ocupacion'] ?? '') : ''));
                }
                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indiceFila, $valores, 0);
            }

            return InformeWordXml::podarFilasDatosVacias($tabla, 2);
        });
    }

    /**
     * Amistades: el formulario no pide dirección ni ocupación. Columnas del Word:
     * Nombre, Teléfono, motivo, años de conocerlo.
     *
     * @param  list<array<string, mixed>>  $filas
     */
    private static function rellenarTablaAmistadesSocio(string $xml, array $filas): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'AMISTADES:', function (string $tabla) use ($filas): string {
            $indiceDireccion = InformeWordXml::indiceColumnaPorTexto($tabla, 'Dirección');
            if ($indiceDireccion !== null) {
                $tabla = InformeWordXml::eliminarColumnas($tabla, [$indiceDireccion]);
            }

            $filasTabla = InformeWordXml::filasTabla($tabla);
            if (isset($filasTabla[1])) {
                $filasTabla[1] = InformeWordXml::aplicarNegritaFila(InformeWordXml::establecerFila($filasTabla[1], [
                    'Nombre:',
                    'Teléfono:',
                    'Por qué motivo lo conoció:',
                    'Años de conocerlo:',
                ], 0));
                $tabla = InformeWordXml::reconstruirTabla($tabla, $filasTabla);
            }

            foreach (array_values($filas) as $indice => $fila) {
                $indiceFila = $indice + 2;
                if ($indiceFila > 10) {
                    break;
                }
                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indiceFila, [
                    self::texto($fila['nombre'] ?? ''),
                    self::texto($fila['telefono'] ?? ''),
                    self::texto($fila['relacion'] ?? ''),
                    self::texto($fila['anos_conocerlo'] ?? $fila['anios_conocerlo'] ?? ''),
                ], 0);
            }

            return InformeWordXml::podarFilasDatosVacias($tabla, 2);
        });
    }

    /** L-E7: esas filas ya están en DETALLE PATRIMONIAL. */
    private static function quitarFilasOtrosAspectosSocio(string $xml): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'OTROS ASPECTOS', function (string $tabla): string {
            return InformeWordXml::eliminarFilasPorEtiquetas($tabla, [
                'Bienes inmuebles',
                'Vehículos propios',
            ]);
        });
    }

    /**
     * Bloques "Información brindada por el candidato" (hasta 4) con datos del formulario.
     *
     * @param  list<array<string, mixed>>  $filas
     */
    private static function rellenarTablasReferenciasLaboralesSocio(string $xml, array $filas): string
    {
        if ($filas === []) {
            return $xml;
        }

        $filas = array_values($filas);
        $indiceFila = 0;
        $offset = 0;
        $len = strlen($xml);

        while ($indiceFila < count($filas) && $offset < $len) {
            if (preg_match('/<w:tbl\b[^>]*>/', $xml, $match, PREG_OFFSET_CAPTURE, $offset) !== 1) {
                break;
            }
            $inicio = (int) $match[0][1];
            $profundidad = 0;
            $pos = $inicio;
            $fin = null;
            while ($pos < $len) {
                if (substr($xml, $pos, 7) === '<w:tbl>') {
                    $profundidad++;
                    $pos += 7;
                    continue;
                }
                if (substr($xml, $pos, 8) === '</w:tbl>') {
                    $profundidad--;
                    $pos += 8;
                    if ($profundidad === 0) {
                        $fin = $pos;
                        break;
                    }
                    continue;
                }
                $pos++;
            }
            if ($fin === null) {
                break;
            }

            $tabla = substr($xml, $inicio, $fin - $inicio);
            $texto = InformeWordXml::textoTablaConcatenado($tabla);
            $esBloqueLaboral = str_contains($texto, 'Información brindada por el candidato')
                && str_contains($texto, 'Empresa:')
                && (str_contains($texto, 'Puesto que ocupó') || str_contains($texto, 'Motivo de Retiro'));
            $esReferenciaPersonal = str_contains($texto, 'Desde hace cuánto tiempo lo conoce')
                || str_contains($texto, '¿Desde hace cuánto tiempo lo conoce?');

            if ($esBloqueLaboral && ! $esReferenciaPersonal) {
                $nueva = self::rellenarUnaReferenciaLaboralSocio($tabla, $filas[$indiceFila]);
                $xml = substr($xml, 0, $inicio) . $nueva . substr($xml, $fin);
                $offset = $inicio + strlen($nueva);
                $len = strlen($xml);
                $indiceFila++;
                continue;
            }

            $offset = $fin;
        }

        return $xml;
    }

    /** @param array<string, mixed> $fila */
    private static function rellenarUnaReferenciaLaboralSocio(string $tabla, array $fila): string
    {
        $empresa = self::texto($fila['empresa'] ?? '');
        $telefono = self::texto($fila['telefono'] ?? '');
        $puesto = self::texto($fila['puesto'] ?? '');
        $contacto = self::texto($fila['contacto'] ?? '');

        $filasTabla = InformeWordXml::filasTabla($tabla);
        foreach ($filasTabla as $indice => $filaXml) {
            $etiqueta = InformeWordXml::textoFila($filaXml);
            if (str_contains($etiqueta, 'Empresa:') && str_contains($etiqueta, 'Teléfonos:')) {
                $filasTabla[$indice] = InformeWordXml::establecerCeldasFila($filaXml, [
                    1 => $empresa,
                    3 => $telefono,
                ]);
            } elseif (str_contains($etiqueta, 'Puesto que ocupó:') && str_contains($etiqueta, 'Dirección:')) {
                $filasTabla[$indice] = InformeWordXml::establecerCeldasFila($filaXml, [
                    3 => $puesto,
                ]);
            } elseif (str_contains($etiqueta, 'Nombre y puesto de quien brinda la referencia:')) {
                $filasTabla[$indice] = InformeWordXml::establecerCeldasFila($filaXml, [
                    1 => $contacto,
                    3 => $telefono,
                ]);
            }
        }

        return InformeWordXml::reconstruirTabla($tabla, $filasTabla);
    }

    /** @param list<array<string, mixed>> $filas */
    private static function rellenarTablaPresupuestoSocio(string $xml, array $filas): string
    {
        if ($filas === []) {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'PRESUPUESTO MENSUAL', function (string $tabla) use ($filas): string {
            $porClave = [];
            $extras = [];
            foreach ($filas as $fila) {
                $concepto = trim((string) ($fila['concepto'] ?? $fila['rubro'] ?? ''));
                $monto = self::moneda($fila['monto'] ?? $fila['cantidad'] ?? $fila['valor'] ?? '');
                $clave = self::clavePresupuesto($concepto);
                if ($clave !== '' && $clave !== 'totales' && ! isset($porClave[$clave])) {
                    $porClave[$clave] = $monto;
                    continue;
                }
                if ($concepto !== '' && $monto !== '') {
                    $extras[] = ['concepto' => $concepto, 'monto' => $monto];
                }
            }

            $filasTabla = InformeWordXml::filasTabla($tabla);
            $indiceTotales = null;
            $plantilla = null;
            $total = 0.0;
            foreach ($filasTabla as $indice => $filaXml) {
                $etiqueta = InformeWordXml::textoFila($filaXml);
                if (str_contains(mb_strtolower($etiqueta), 'totale')) {
                    $indiceTotales = $indice;
                    continue;
                }
                if ($indice === 0) {
                    continue;
                }
                if ($plantilla === null) {
                    $plantilla = $filaXml;
                }
                $clave = self::clavePresupuesto($etiqueta);
                if ($clave === '' || ! isset($porClave[$clave])) {
                    continue;
                }
                $filasTabla[$indice] = InformeWordXml::establecerFila($filaXml, [$porClave[$clave]], 1);
                $total += self::numeroMoneda($porClave[$clave]);
                unset($porClave[$clave]);
            }

            foreach ($porClave as $montoSuelto) {
                $total += self::numeroMoneda($montoSuelto);
            }
            foreach ($extras as $extra) {
                $total += self::numeroMoneda($extra['monto']);
                if ($plantilla === null || $indiceTotales === null) {
                    continue;
                }
                $nueva = InformeWordXml::establecerFila($plantilla, [$extra['concepto'], $extra['monto']], 0);
                array_splice($filasTabla, $indiceTotales, 0, [$nueva]);
                $indiceTotales++;
            }

            if ($indiceTotales !== null) {
                $filasTabla[$indiceTotales] = InformeWordXml::establecerFila(
                    $filasTabla[$indiceTotales],
                    [self::formatoQuetzales($total)],
                    1
                );
            }

            return InformeWordXml::reconstruirTabla($tabla, $filasTabla);
        });
    }

    /** @param array<string, string> $domicilio */
    private static function rellenarTablaDomicilioSocio(string $xml, array $domicilio): string
    {
        if ($domicilio === []) {
            return $xml;
        }

        $valores = [
            'direccion verificada' => $domicilio['direccion_verificada'] ?? '',
            'direccion reportada' => $domicilio['direccion_reportada'] ?? '',
            'tiempo de residencia' => $domicilio['tiempo_residencia'] ?? '',
            'propia o alquilada' => $domicilio['tipo_vivienda'] ?? '',
            'pago de renta' => isset($domicilio['pago_renta']) && $domicilio['pago_renta'] !== ''
                ? self::moneda($domicilio['pago_renta'])
                : '',
            'propietario' => $domicilio['propietario'] ?? '',
            'habitantes' => $domicilio['habitantes'] ?? '',
            'referencias de ubicacion' => $domicilio['refs_ubicacion'] ?? '',
            'area roja' => $domicilio['zona_roja'] ?? '',
            'zona roja' => $domicilio['zona_roja'] ?? '',
            'direcciones anteriores' => $domicilio['direcciones_anteriores'] ?? '',
        ];

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'VERIFICACIÓN DE RESIDENCIA', function (string $tabla) use ($valores): string {
            $filas = InformeWordXml::filasTabla($tabla);
            foreach ($filas as $indice => $filaXml) {
                if ($indice === 0) {
                    continue;
                }
                $etiqueta = self::normalizarEtiqueta(InformeWordXml::textoFila($filaXml));
                foreach ($valores as $clave => $valor) {
                    if ($valor === '' || ! str_contains($etiqueta, $clave)) {
                        continue;
                    }
                    $filas[$indice] = InformeWordXml::establecerFila($filaXml, [$valor], 1);
                    break;
                }
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /** @param list<array<string, mixed>> $filas */
    private static function rellenarTablaBienesSocio(string $xml, array $filas): string
    {
        if ($filas === []) {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'DETALLE PATRIMONIAL', function (string $tabla) use ($filas): string {
            $filasTabla = InformeWordXml::filasTabla($tabla);
            $plantilla = $filasTabla[2] ?? null;
            $indiceTotal = null;
            foreach ($filasTabla as $indice => $filaXml) {
                if (str_contains(InformeWordXml::textoFila($filaXml), 'Total')) {
                    $indiceTotal = $indice;
                    break;
                }
            }

            $total = 0.0;
            $filasDatos = [];
            foreach (array_values($filas) as $indice => $fila) {
                if ($indice >= 20 || $plantilla === null) {
                    break;
                }
                $valorRaw = preg_replace('/[^\d.]/', '', (string) ($fila['valor'] ?? $fila['valor_aproximado'] ?? '')) ?? '';
                $valorNum = is_numeric($valorRaw) ? (float) $valorRaw : 0.0;
                $total += $valorNum;
                $filasDatos[] = InformeWordXml::establecerFila($plantilla, [
                    self::texto($fila['bien'] ?? $fila['descripcion'] ?? $fila['pertenencia'] ?? ''),
                    $valorRaw !== '' ? ('Q '.$valorRaw) : '',
                ], 0);
            }

            $resultado = array_slice($filasTabla, 0, 2);
            $resultado = array_merge($resultado, $filasDatos);
            if ($indiceTotal !== null) {
                $filaTotal = $filasTabla[$indiceTotal];
                $resultado[] = InformeWordXml::establecerFila(
                    $filaTotal,
                    ['Q '.number_format($total, 2)],
                    max(1, count(InformeWordXml::celdasFila($filaTotal)) - 1)
                );
                if ($indiceTotal + 1 < count($filasTabla)) {
                    $resultado = array_merge($resultado, array_slice($filasTabla, $indiceTotal + 1));
                }
            }

            return InformeWordXml::reconstruirTabla($tabla, $resultado);
        });
    }

    /** FORMATOS: Motivo de la prueba (editable) desde la orden/evaluado. */
    private static function rellenarMotivoProcedimiento(string $xml, EvaluadoOrden $evaluado): string
    {
        $motivo = trim((string) ($evaluado->motivo_hecho_evaluacion ?? ''));
        if ($motivo === '') {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'Motivo de la prueba', function (string $tabla) use ($motivo): string {
            $filas = InformeWordXml::filasTabla($tabla);
            foreach ($filas as $indice => $fila) {
                if (! str_contains(InformeWordXml::textoFila($fila), 'Motivo de la prueba')) {
                    continue;
                }
                $filas[$indice] = InformeWordXml::establecerFila($fila, [$motivo], 1);

                return InformeWordXml::reconstruirTabla($tabla, $filas);
            }

            return $tabla;
        });
    }

    private static function marcadorTablaFamiliar(string $xml): string
    {
        foreach (['DATOS FAMILIARES', 'PADRES:', 'INFORMACIÓN familiar'] as $marcador) {
            if (InformeWordXml::limitesTablaPorMarcador($xml, $marcador) !== null) {
                return $marcador;
            }
        }

        return 'DATOS FAMILIARES';
    }

    /** @param array<string, mixed> $familiar */
    private static function rellenarTablaFamiliar(string $xml, array $familiar): string
    {
        $marcador = self::marcadorTablaFamiliar($xml);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($familiar): string {
            $padre = is_array($familiar['padre'] ?? null) ? $familiar['padre'] : [];
            $madre = is_array($familiar['madre'] ?? null) ? $familiar['madre'] : [];

            // La plantilla trae la fila de encabezados (Nombre/Edad/Teléfono...) entre el título y
            // "Padre:", así que las filas se localizan por su etiqueta y no por posición.
            foreach (['Padre:' => $padre, 'Madre:' => $madre] as $etiqueta => $datos) {
                $indice = self::indiceFilaPorEtiqueta($tabla, $etiqueta);
                if ($indice === null) {
                    continue;
                }

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indice, [
                    self::texto($datos['nombre'] ?? ''),
                    self::edad($datos['edad'] ?? ''),
                    self::texto($datos['telefono'] ?? ''),
                    self::texto($datos['direccion'] ?? ''),
                    self::texto($datos['ocupacion'] ?? ''),
                ], 1);
            }

            return InformeWordXml::repararBordesColumnaEtiquetaFamiliar($tabla);
        });
    }

    /** @param array<string, mixed> $familiar */
    private static function rellenarTablaEstadoCivil(string $xml, array $familiar, string $estadoCivil = ''): string
    {
        $pareja = is_array($familiar['pareja'] ?? null) ? $familiar['pareja'] : [];

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'ESTADO CIVIL', function (string $tabla) use ($pareja, $estadoCivil): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if ($estadoCivil !== '' && $estadoCivil !== '—' && isset($filas[0])) {
                $filas[0] = InformeWordXml::establecerFila($filas[0], [$estadoCivil], 1);
                $tabla = InformeWordXml::reconstruirTabla($tabla, $filas);
            }

            $tienePareja = ($pareja['tiene'] ?? false) === true
                || trim((string) ($pareja['nombre'] ?? '')) !== '';

            if (! $tienePareja) {
                // Sin pareja se deja solo el título con la leyenda: las ocho etiquetas en blanco
                // parecían un formulario sin llenar.
                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, 1, ['No aplica'], 1);

                return InformeWordXml::eliminarFilasPorEtiquetas($tabla, [
                    'Edad:',
                    'Número de teléfono:',
                    'Dirección:',
                    'Ocupación o lugar de trabajo:',
                    'Tipo de relación:',
                    'Estado de la relación:',
                    'Tiempo de la relación:',
                    'Número de relación:',
                ]);
            }

            $valoresPorEtiqueta = [
                'Nombre del conyugue o pareja actual:' => self::texto($pareja['nombre'] ?? ''),
                'Edad:' => self::edad($pareja['edad'] ?? ''),
                'Número de teléfono:' => self::texto($pareja['telefono'] ?? ''),
                'Dirección:' => self::texto($pareja['direccion'] ?? ''),
                'Ocupación o lugar de trabajo:' => self::texto($pareja['ocupacion'] ?? ''),
                'Tipo de relación:' => self::texto($pareja['tipo'] ?? ''),
                'Estado de la relación:' => self::texto($pareja['calidad_relacion'] ?? ''),
                'Tiempo de la relación:' => self::texto($pareja['tiempo_relacion'] ?? ''),
            ];

            $filas = InformeWordXml::filasTabla($tabla);
            foreach ($filas as $indice => $fila) {
                if ($indice === 0) {
                    continue;
                }

                $celdas = InformeWordXml::celdasFila($fila);
                if ($celdas === []) {
                    continue;
                }

                $etiqueta = InformeWordXml::textoCelda($celdas[0]);
                foreach ($valoresPorEtiqueta as $clave => $valor) {
                    if (self::etiquetaCoincide($etiqueta, $clave)) {
                        $filas[$indice] = InformeWordXml::establecerFila($fila, [$valor], 1);
                        break;
                    }
                }
            }

            $tabla = InformeWordXml::reconstruirTabla($tabla, $filas);

            return InformeWordXml::eliminarFilasPorEtiquetas($tabla, ['Número de relación:']);
        });
    }

    /** @param array<string, mixed> $familiar */
    private static function rellenarTablaExpareja(string $xml, array $familiar): string
    {
        $expareja = is_array($familiar['expareja'] ?? null) ? $familiar['expareja'] : [];
        $lineas = ['No aplica'];

        if (($expareja['aplica'] ?? false) === true) {
            $lineas = collect([
                self::texto($expareja['nombre'] ?? '') !== '' ? 'Nombre: ' . self::texto($expareja['nombre'] ?? '') : '',
                self::texto($expareja['tipo'] ?? '') !== '' ? 'Tipo de relación: ' . self::texto($expareja['tipo'] ?? '') : '',
                self::texto($expareja['tiempo_relacion'] ?? '') !== '' ? 'Tiempo de relación: ' . self::texto($expareja['tiempo_relacion'] ?? '') : '',
                isset($expareja['hijos_comun']) ? 'Hijos en común: ' . $expareja['hijos_comun'] : '',
                isset($expareja['cantidad_hijos']) && $expareja['cantidad_hijos'] !== '' ? 'Cantidad de hijos: ' . $expareja['cantidad_hijos'] : '',
                isset($expareja['problemas_legales']) ? 'Problemas legales: ' . $expareja['problemas_legales'] : '',
            ])->filter()->values()->all();
            if ($lineas === []) {
                $lineas = ['No aplica'];
            }
        }

        $marcador = InformeWordXml::limitesTablaPorMarcador($xml, 'DATOS DE EXPAREJAS') !== null
            ? 'DATOS DE EXPAREJAS'
            : 'DATOS DE EXPAREJA';

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (! isset($filas[1])) {
                return InformeWordXml::reemplazarMarcadores($tabla, [
                    'xxxxxxx' => implode("\n", $lineas),
                ]);
            }

            $celdas = InformeWordXml::celdasFila($filas[1]);
            if ($celdas === []) {
                return $tabla;
            }

            $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
            preg_match('/<w:tr\b[^>]*>/', $filas[1], $apertura);
            $filas[1] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /** @param list<array<string, mixed>> $hijos */
    private static function rellenarTablaHijos(string $xml, array $hijos): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'HIJOS:', function (string $tabla) use ($hijos): string {
            $indiceConQuienVive = InformeWordXml::indiceColumnaPorTexto($tabla, 'Con quién vive');
            if ($indiceConQuienVive !== null) {
                $tabla = InformeWordXml::eliminarColumnas($tabla, [$indiceConQuienVive]);
            }

            foreach (array_values($hijos) as $indice => $hijo) {
                $fila = $indice + 1;
                if ($fila > 5) {
                    break;
                }

                $edadHijo = TablaDinamica::etiquetaEdadHijo($hijo['edad'] ?? '');
                if ($edadHijo !== '' && $edadHijo !== 'Menor de 1 año') {
                    $edadHijo = self::edad($edadHijo);
                }

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, [
                    self::texto($hijo['nombre'] ?? ''),
                    $edadHijo,
                    self::texto($hijo['ocupacion'] ?? $hijo['telefono'] ?? ''),
                ]);
            }

            return InformeWordXml::podarFilasDatosVacias($tabla, 1);
        });
    }

    /** @param list<array<string, mixed>> $hermanos */
    private static function rellenarTablaHermanos(string $xml, array $hermanos): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'HERMANOS:', function (string $tabla) use ($hermanos): string {
            $filas = InformeWordXml::filasTabla($tabla);
            $plantilla = $filas[2] ?? null;

            foreach (array_values($hermanos) as $indice => $hermano) {
                $fila = $indice + 2;
                if (! isset($filas[$fila]) && $plantilla !== null) {
                    $filas[$fila] = $plantilla;
                }
                if (! isset($filas[$fila])) {
                    break;
                }

                $filas[$fila] = InformeWordXml::establecerFila($filas[$fila], [
                    self::texto($hermano['nombre'] ?? ''),
                    self::edad($hermano['edad'] ?? ''),
                    self::texto($hermano['telefono'] ?? ''),
                    self::texto($hermano['direccion'] ?? ''),
                    self::texto($hermano['ocupacion'] ?? $hermano['lugar_trabajo'] ?? ''),
                ]);
            }

            return InformeWordXml::podarFilasDatosVacias(
                InformeWordXml::reconstruirTabla($tabla, $filas),
                2
            );
        });
    }

    /** @param list<array<string, mixed>> $filasAcademicas */
    private static function rellenarTablaAcademica(
        string $xml,
        array $filasAcademicas,
        string $variante = '',
        array $estudiosActuales = [],
        ?string $estudiaActualmente = null
    ): string {
        $porNivel = [];
        foreach ($filasAcademicas as $fila) {
            $nivel = $fila['nivel'] ?? '';
            if ($nivel !== '') {
                $porNivel[$nivel] = $fila;
            }
        }

        $mapaFilas = [
            'universitario' => 1,
            'postgrado' => 1,
            'tecnico' => 2,
            'diversificado' => 2,
            'basico' => 3,
            'primaria' => 4,
        ];

        $soloUltimoGrado = InformeWordPlantillas::esVariantePeriodicaLike($variante)
            || $variante === InformeWordPlantillas::VARIANTE_ESPECIFICA;

        // Socioeconómico usa "DATOS ACADÉMICOS"; polígrafo/VSA "NIVEL ACADÉMICO".
        $marcadorAcademico = InformeWordXml::limitesTablaPorMarcador($xml, 'NIVEL ACADÉMICO') !== null
            ? 'NIVEL ACADÉMICO'
            : 'DATOS ACADÉMICOS';

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcadorAcademico, function (string $tabla) use ($porNivel, $mapaFilas, $soloUltimoGrado, $filasAcademicas, $estudiosActuales, $estudiaActualmente): string {
            $textoTabla = InformeWordXml::textoTablaConcatenado($tabla);
            $esUltimoGrado = $soloUltimoGrado || str_contains($textoTabla, 'Ultimo grado') || str_contains($textoTabla, 'Último grado');

            if ($esUltimoGrado) {
                $ultimo = self::ultimaFilaAcademica($filasAcademicas, $porNivel);
                if ($ultimo !== null) {
                    $partes = [
                        self::texto($ultimo['anio'] ?? '') !== '—' ? self::texto($ultimo['anio'] ?? '') : '',
                        HistorialAcademico::NIVELES[$ultimo['nivel'] ?? ''] ?? ($ultimo['nivel'] ?? ''),
                        self::texto($ultimo['carrera'] ?? '') !== '—' ? self::texto($ultimo['carrera'] ?? '') : '',
                        self::texto($ultimo['institucion'] ?? '') !== '—' ? self::texto($ultimo['institucion'] ?? '') : '',
                    ];
                    $grado = trim(implode(' — ', array_filter($partes, static fn (string $parte): bool => $parte !== '')));
                    $filas = InformeWordXml::filasTabla($tabla);
                    if (isset($filas[1])) {
                        $filas[1] = InformeWordXml::combinarCeldasFila($filas[1], 1, 3, [$grado !== '' ? $grado : '—']);
                        $tabla = InformeWordXml::reconstruirTabla($tabla, $filas);
                    }
                }

                return $tabla;
            }

            $nivelesColocados = [];
            $filasTabla = InformeWordXml::filasTabla($tabla);
            $indicePorNivel = [];
            $indiceOtros = null;
            foreach ($filasTabla as $indice => $filaXml) {
                $texto = InformeWordXml::textoFila($filaXml);
                if (str_contains($texto, 'Universitario')) {
                    $indicePorNivel['universitario'] = $indice;
                }
                if (str_contains($texto, 'Postgrado') || str_contains($texto, 'Posgrado')) {
                    $indicePorNivel['postgrado'] = $indice;
                }
                if (str_contains($texto, 'Técnico') || str_contains($texto, 'Tecnico')) {
                    $indicePorNivel['tecnico'] = $indice;
                }
                if (str_contains($texto, 'Diversificado')) {
                    $indicePorNivel['diversificado'] = $indice;
                }
                if (str_contains($texto, 'Básico') || str_contains($texto, 'Basico')) {
                    $indicePorNivel['basico'] = $indice;
                }
                if (str_contains($texto, 'Primari')) {
                    $indicePorNivel['primaria'] = $indice;
                }
                if (str_contains($texto, 'Otros')) {
                    $indiceOtros = $indice;
                }
            }
            if ($indiceOtros !== null) {
                foreach ($indicePorNivel as $nivelClave => $indiceFila) {
                    if ($indiceFila === $indiceOtros) {
                        unset($indicePorNivel[$nivelClave]);
                    }
                }
            }

            $filasUsadas = [];
            foreach ($porNivel as $nivel => $fila) {
                if (! isset($indicePorNivel[$nivel]) || isset($filasUsadas[$indicePorNivel[$nivel]])) {
                    continue;
                }
                $indiceFila = $indicePorNivel[$nivel];
                $filasUsadas[$indiceFila] = true;
                $nivelesColocados[$nivel] = true;
                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indiceFila, [
                    self::texto($fila['anio'] ?? ''),
                    self::texto($fila['carrera'] ?? $fila['estado'] ?? ''),
                    self::texto($fila['institucion'] ?? ''),
                ], 1);
            }

            $tabla = self::rellenarFilaOtrosAcademicos($tabla, $filasAcademicas, $porNivel, $nivelesColocados);
            $tabla = InformeWordXml::eliminarFilasSinDatosEnRango($tabla, 1, 5, 1);

            $filas = InformeWordXml::filasTabla($tabla);
            $filas = array_values(array_map(function (string $fila) use ($estudiosActuales, $estudiaActualmente): string {
                if (mb_stripos(InformeWordXml::textoFila($fila), 'estudia actualmente') !== false) {
                    return self::rellenarFilaEstudiaActualmente($fila, $estudiosActuales, $estudiaActualmente);
                }

                if (str_contains(InformeWordXml::textoFila($fila), 'Validación de constancia')) {
                    return self::filaCeldasVacias($fila, [1]) ? '' : $fila;
                }

                return $fila;
            }, $filas));

            $filas = array_values(array_filter($filas, static fn (string $fila): bool => $fila !== ''));

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $estudiosActuales
     */
    private static function rellenarFilaEstudiaActualmente(string $fila, array $estudiosActuales, ?string $estudiaActualmente): string
    {
        if ($estudiosActuales !== []) {
            $detalles = [];
            $horarios = [];

            foreach ($estudiosActuales as $estudio) {
                $queEstudia = self::texto($estudio['que_estudia'] ?? '');
                $institucion = self::texto($estudio['institucion'] ?? '');
                $detalle = trim($queEstudia . ($institucion !== '' && $queEstudia !== '' ? ' — ' : '') . $institucion);

                if ($detalle !== '') {
                    $detalles[] = $detalle;
                }

                $horario = self::texto($estudio['horario'] ?? '');
                if ($horario !== '') {
                    $horarios[] = $horario;
                }
            }

            if ($detalles === [] && $horarios === []) {
                return self::filaCeldasVacias($fila, [1, 3]) ? '' : $fila;
            }

            // El cliente pide leer primero si estudia o no y después qué y dónde estudia.
            $respuesta = trim('Sí' . ($detalles !== [] ? ' — ' . implode('; ', $detalles) : ''));

            $fila = InformeWordXml::establecerCeldasFila($fila, [
                1 => $respuesta,
                3 => implode('; ', $horarios),
            ]);

            return self::repartirAnchoFilaEstudia($fila);
        }

        if ($estudiaActualmente === 'no') {
            return InformeWordXml::establecerCeldasFila($fila, [
                1 => 'No',
                3 => '',
            ]);
        }

        return self::filaCeldasVacias($fila, [1, 3]) ? '' : $fila;
    }

    /**
     * La celda del detalle queda alineada con la columna del año (angosta) y el carrera/institución
     * se apilaba letra por letra. Se reparte el ancho de esa fila dando el espacio a los valores;
     * Word admite anchos propios por fila y el resto de la tabla no se altera.
     */
    private static function repartirAnchoFilaEstudia(string $fila): string
    {
        $celdas = InformeWordXml::celdasFila($fila);
        if (count($celdas) !== 4) {
            return $fila;
        }

        $total = 0;
        foreach ($celdas as $celda) {
            if (preg_match('/<w:tcW\b[^>]*w:w="(\d+)"/', $celda, $coincidencia) !== 1) {
                return $fila;
            }
            $total += (int) $coincidencia[1];
        }

        if ($total <= 0) {
            return $fila;
        }

        $proporciones = [0 => 0.24, 1 => 0.37, 2 => 0.11, 3 => 0.28];
        $asignado = 0;

        foreach ($celdas as $indice => $celda) {
            $ancho = $indice === 3
                ? max(1, $total - $asignado)
                : max(1, (int) round($total * $proporciones[$indice]));
            $asignado += $ancho;
            $celdas[$indice] = InformeWordXml::establecerAnchoCelda($celda, $ancho);
        }

        preg_match('/<w:tr\b[^>]*>/', $fila, $apertura);

        return ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
    }

    private static function estudiaActualmenteDesdeCuestionario(EvaluadoOrden $evaluado): ?string
    {
        $cuestionario = $evaluado->cuestionario;
        if ($cuestionario === null) {
            return null;
        }

        $respuestas = $cuestionario->obtenerRespuestasSeccion(3);
        $valor = trim((string) ($respuestas['estudia_actualmente'] ?? ''));

        return $valor !== '' ? $valor : null;
    }

    /**
     * Estudios extra del evaluador: una fila por nivel (Universitario, Técnico, …),
     * no concatenados en «Otros:» (Stephany 20-ago · socio + polígrafo/VSA).
     *
     * @param  list<array<string, mixed>>  $filasAcademicas
     * @param  array<string, array<string, mixed>>  $porNivel
     * @param  array<string, true>  $nivelesColocados
     */
    private static function rellenarFilaOtrosAcademicos(
        string $tabla,
        array $filasAcademicas,
        array $porNivel,
        array $nivelesColocados
    ): string {
        $extras = [];
        foreach ($filasAcademicas as $fila) {
            $nivel = (string) ($fila['nivel'] ?? '');
            if ($nivel !== '' && isset($nivelesColocados[$nivel]) && ($porNivel[$nivel] ?? null) === $fila) {
                continue;
            }

            $anio = self::texto($fila['anio'] ?? '');
            $carrera = self::texto($fila['carrera'] ?? $fila['estado'] ?? '');
            $institucion = self::texto($fila['institucion'] ?? '');
            if ($anio === '—') {
                $anio = '';
            }
            if ($carrera === '—') {
                $carrera = '';
            }
            if ($institucion === '—') {
                $institucion = '';
            }

            if ($nivel === '' && $anio === '' && $carrera === '' && $institucion === '') {
                continue;
            }

            $extras[] = [
                'etiqueta' => trim((HistorialAcademico::NIVELES[$nivel] ?? ($nivel !== '' ? $nivel : 'Otros'))).':',
                'anio' => $anio !== '' ? $anio : '—',
                'carrera' => $carrera !== '' ? $carrera : '—',
                'institucion' => $institucion !== '' ? $institucion : '—',
            ];
        }

        if ($extras === []) {
            return $tabla;
        }

        $filas = InformeWordXml::filasTabla($tabla);
        $indiceOtros = null;
        $filaPlantilla = null;
        foreach ($filas as $indice => $filaXml) {
            $texto = InformeWordXml::textoFila($filaXml);
            if (str_contains($texto, 'Otros:')) {
                $indiceOtros = $indice;
            }
            if ($filaPlantilla === null && (
                str_contains($texto, 'Diversificado:')
                || str_contains($texto, 'Universitario:')
                || str_contains($texto, 'Técnico:')
                || str_contains($texto, 'Tercero Básico:')
                || str_contains($texto, 'Básico:')
            )) {
                $filaPlantilla = $filaXml;
            }
        }

        if ($filaPlantilla === null || $indiceOtros === null) {
            foreach ($filas as $indice => $filaXml) {
                if (! str_contains(InformeWordXml::textoFila($filaXml), 'Otros:')) {
                    continue;
                }
                $filas[$indice] = InformeWordXml::establecerFila($filaXml, [implode('; ', array_map(
                    static fn (array $e): string => trim(implode(' — ', array_filter([
                        rtrim($e['etiqueta'], ':'),
                        $e['carrera'] !== '—' ? $e['carrera'] : '',
                        $e['institucion'] !== '—' ? $e['institucion'] : '',
                        $e['anio'] !== '—' ? $e['anio'] : '',
                    ]))),
                    $extras
                ))], 1);

                return InformeWordXml::reconstruirTabla($tabla, $filas);
            }

            return $tabla;
        }

        $nuevas = [];
        foreach ($extras as $extra) {
            $nuevas[] = InformeWordXml::establecerFila($filaPlantilla, [
                $extra['etiqueta'],
                $extra['anio'],
                $extra['carrera'],
                $extra['institucion'],
            ], 0);
        }

        array_splice($filas, $indiceOtros, 0, $nuevas);

        return InformeWordXml::reconstruirTabla($tabla, $filas);
    }

    /**
     * Formulario y Word peri/espe: una fila = último nivel elegido.
     * Si REPRO editó la tabla académica, no se filtra el override (J16).
     *
     * @param  list<array<string, mixed>>  $filasAcademicas
     * @return list<array<string, mixed>>
     */
    private static function filasAcademicasVisibles(EvaluadoOrden $evaluado, array $filasAcademicas): array
    {
        if (in_array('academico', InformePreempleo::clavesConOverride($evaluado->id), true)) {
            return array_values($filasAcademicas);
        }

        if ($filasAcademicas === []) {
            $soloNivel = self::ultimoNivelAcademicoDesdeCuestionario($evaluado);
            if ($soloNivel !== null && $soloNivel !== 'ninguno') {
                return [['nivel' => $soloNivel]];
            }
        }

        $ultimoNivel = self::ultimoNivelAcademicoDesdeCuestionario($evaluado);
        $nivelesVisibles = HistorialAcademico::nivelesVisibles($ultimoNivel);
        if ($nivelesVisibles === []) {
            return $filasAcademicas;
        }

        return array_values(array_filter(
            $filasAcademicas,
            static fn (array $fila): bool => in_array($fila['nivel'] ?? '', $nivelesVisibles, true)
        ));
    }

    private static function ultimoNivelAcademicoDesdeCuestionario(EvaluadoOrden $evaluado): ?string
    {
        $cuestionario = $evaluado->cuestionario;
        if ($cuestionario === null) {
            return null;
        }

        $respuestas = $cuestionario->obtenerRespuestasSeccion(3);
        $valor = trim((string) ($respuestas['ultimo_nivel_academico'] ?? ''));

        return $valor !== '' ? $valor : null;
    }

    /**
     * @param  list<array<string, mixed>>  $filasAcademicas
     * @param  array<string, array<string, mixed>>  $porNivel
     * @return array<string, mixed>|null
     */
    private static function ultimaFilaAcademica(array $filasAcademicas, array $porNivel): ?array
    {
        $orden = ['postgrado', 'universitario', 'tecnico', 'diversificado', 'basico', 'primaria'];
        foreach ($orden as $nivel) {
            if (isset($porNivel[$nivel])) {
                return $porNivel[$nivel];
            }
        }

        $ultima = end($filasAcademicas);

        return is_array($ultima) ? $ultima : null;
    }

    /** Escribe el valor en la celda que sigue a la etiqueta dentro de la misma fila. */
    private static function establecerValorTrasEtiqueta(string $filaXml, string $etiqueta, string $valor): string
    {
        $celdas = InformeWordXml::celdasFila($filaXml);

        foreach ($celdas as $indice => $celda) {
            if (! str_contains(InformeWordXml::textoCelda($celda), $etiqueta)) {
                continue;
            }

            if (! isset($celdas[$indice + 1])) {
                break;
            }

            return InformeWordXml::establecerCeldasFila($filaXml, [$indice + 1 => $valor]);
        }

        return $filaXml;
    }

    private static function claveComplementaria(string $etiqueta): string
    {
        $normalizada = mb_strtolower(trim($etiqueta));
        $normalizada = strtr($normalizada, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', '¿' => '', '?' => '']);
        $normalizada = rtrim($normalizada, '.:');

        return match (true) {
            str_contains($normalizada, 'licencia') => 'licencia',
            str_contains($normalizada, 'sindicato') => 'sindicato',
            str_contains($normalizada, 'familiar') && str_contains($normalizada, 'empresa') => 'familiar_empresa',
            str_contains($normalizada, 'laborado') && str_contains($normalizada, 'anterior') => 'ha_laborado',
            str_contains($normalizada, 'entero') => 'como_se_entero',
            str_contains($normalizada, 'condiciones laborales') => 'condiciones',
            str_contains($normalizada, 'metas') => 'metas',
            str_contains($normalizada, 'califica') || str_contains($normalizada, 'cualidades') => 'cualidades',
            str_contains($normalizada, 'redes') => 'redes',
            default => '',
        };
    }

    /** Índice de la fila cuya primera celda es la etiqueta indicada (p. ej. "Padre:"). */
    private static function indiceFilaPorEtiqueta(string $tablaXml, string $etiqueta): ?int
    {
        foreach (InformeWordXml::filasTabla($tablaXml) as $indice => $fila) {
            $celdas = InformeWordXml::celdasFila($fila);
            if ($celdas === []) {
                continue;
            }

            if (str_contains(InformeWordXml::textoCelda($celdas[0]), $etiqueta)) {
                return $indice;
            }
        }

        return null;
    }

    /** @param list<int> $columnas */
    private static function filaCeldasVacias(string $filaXml, array $columnas): bool
    {
        $celdas = InformeWordXml::celdasFila($filaXml);

        foreach ($columnas as $columna) {
            if (isset($celdas[$columna]) && InformeWordXml::textoCelda($celdas[$columna]) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array<string, mixed>>  $empleos
     */
    private static function rellenarTablaLaboral(string $xml, array $empleos, string $variante): string
    {
        if ($empleos === []) {
            return $xml;
        }

        $limites = InformeWordXml::limitesTablaTrasTexto($xml, 'INFORMACIÓN LABORAL')
            ?? InformeWordXml::limitesTablaPorMarcador($xml, 'EMPLEOS:')
            ?? InformeWordXml::limitesTablaPorMarcador($xml, 'EMPLEOS')
            ?? InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN LABORAL');
        if ($limites === null) {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaEnLimites($xml, $limites, function (string $tabla) use ($empleos, $variante): string {
            $interna = InformeWordXml::limitesTablaInternaConTextos($tabla, 'Empresa', 'Puesto');
            if ($interna !== null) {
                $grid = substr($tabla, $interna[0], $interna[1] - $interna[0]);

                return substr($tabla, 0, $interna[0])
                    .self::rellenarFilasEmpleos($grid, $empleos, $variante)
                    .substr($tabla, $interna[1]);
            }

            return self::rellenarFilasEmpleos($tabla, $empleos, $variante);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $empleos
     */
    private static function rellenarFilasEmpleos(string $tabla, array $empleos, string $variante): string
    {
        $filas = InformeWordXml::filasTabla($tabla);
        $indiceEncabezado = null;
        foreach ($filas as $indice => $filaXml) {
            $encabezado = InformeWordXml::textoFila($filaXml);
            if (str_contains($encabezado, 'Empresa') && (str_contains($encabezado, 'Puesto') || str_contains($encabezado, 'Salario'))) {
                $indiceEncabezado = $indice;
                break;
            }
        }

        $primeraDatos = $indiceEncabezado !== null ? $indiceEncabezado + 1 : 2;
        $plantilla = $filas[$primeraDatos] ?? ($indiceEncabezado !== null ? $filas[$indiceEncabezado] : null);
        if ($plantilla === null) {
            return $tabla;
        }

        $lista = array_values(array_filter($empleos, static function ($empleo): bool {
            if (! is_array($empleo)) {
                return false;
            }

            return trim((string) ($empleo['empresa'] ?? '')) !== ''
                || trim((string) ($empleo['puesto'] ?? '')) !== '';
        }));
        if ($lista === []) {
            return $tabla;
        }
        if (InformeWordPlantillas::esVariantePeriodicaLike($variante)) {
            $lista = array_slice($lista, 0, 1);
        }

        $nuevas = array_slice($filas, 0, $primeraDatos);
        foreach ($lista as $empleo) {
            $nuevas[] = InformeWordXml::establecerFila($plantilla, self::celdasEmpleo($empleo, $variante));
        }

        return InformeWordXml::reconstruirTabla($tabla, $nuevas);
    }

    /**
     * @param  array<string, mixed>  $empleo
     * @return list<string>
     */
    private static function celdasEmpleo(array $empleo, string $variante): array
    {
        if (InformeWordPlantillas::esVariantePeriodicaLike($variante)) {
            return [
                self::texto($empleo['empresa'] ?? ''),
                self::texto($empleo['puesto'] ?? ''),
                self::texto($empleo['fechas'] ?? $empleo['fechas_laboradas'] ?? ''),
                self::texto($empleo['salario'] ?? $empleo['salario_actual'] ?? $empleo['ultimo_salario'] ?? ''),
                self::texto($empleo['motivo'] ?? $empleo['motivo_prueba'] ?? $empleo['motivo_retiro'] ?? ''),
            ];
        }

        $fechas = trim((string) ($empleo['fechas_laboradas'] ?? $empleo['fechas'] ?? ''));
        if ($fechas === '') {
            $ingreso = trim((string) ($empleo['fecha_ingreso'] ?? ''));
            $salida = trim((string) ($empleo['fecha_salida'] ?? ''));
            if ($ingreso !== '' && $salida !== '') {
                $fechas = $ingreso.' - '.$salida;
            } elseif ($salida !== '') {
                $fechas = $salida;
            } else {
                $fechas = $ingreso;
            }
        }

        return [
            self::texto($empleo['empresa'] ?? ''),
            self::texto($empleo['puesto'] ?? ''),
            FechasLaboradasCampo::formatearParaInforme($fechas),
            self::moneda($empleo['ultimo_salario'] ?? $empleo['salario'] ?? $empleo['salario_mensual'] ?? ''),
            self::texto($empleo['motivo_retiro'] ?? $empleo['motivo'] ?? ''),
        ];
    }

    /** @param list<array<string, mixed>> $deudas */
    private static function rellenarTablaDeudas(string $xml, array $deudas): string
    {
        $marcador = self::marcadorTablaDeudas($xml);

        if ($deudas === []) {
            return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, static function (string $tabla): string {
                return InformeWordXml::podarSeccionDeudasVacia($tabla);
            });
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($deudas): string {
            $filasDetect = InformeWordXml::filasTabla($tabla);
            $encabezado = isset($filasDetect[1]) ? InformeWordXml::textoFila($filasDetect[1]) : '';
            $primeraDatos = str_contains($encabezado, 'Entidad') ? 2 : 3;
            $ordenSocio = str_contains($encabezado, 'Estatus') && str_contains($encabezado, 'Atraso');

            foreach (array_values($deudas) as $indice => $deuda) {
                $fila = $indice + $primeraDatos;
                if ($fila > 10) {
                    break;
                }

                $estatus = $deuda['estatus'] ?? '';
                if ($estatus === 'al_dia') {
                    $estatus = 'Al día';
                } elseif ($estatus === 'atrasado' || $estatus === 'en_mora') {
                    $estatus = 'Atrasado';
                } elseif ($estatus === 'pagado') {
                    $estatus = 'Pagado';
                }

                $valores = $ordenSocio
                    ? [
                        self::texto($deuda['entidad'] ?? ''),
                        self::moneda($deuda['monto'] ?? ''),
                        self::moneda($deuda['saldo'] ?? ''),
                        self::moneda($deuda['cuota'] ?? ''),
                        self::texto($estatus),
                        self::texto($deuda['meses_atraso'] ?? ''),
                        self::texto($deuda['motivo'] ?? ''),
                        self::texto($deuda['antiguedad'] ?? ''),
                    ]
                    : [
                        self::texto($deuda['entidad'] ?? ''),
                        self::moneda($deuda['monto'] ?? ''),
                        self::moneda($deuda['saldo'] ?? ''),
                        self::moneda($deuda['cuota'] ?? ''),
                        self::texto($deuda['motivo'] ?? ''),
                        self::texto($deuda['antiguedad'] ?? ''),
                        self::texto($estatus),
                        self::texto($deuda['meses_atraso'] ?? ''),
                    ];

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, $valores);
            }

            $filas = InformeWordXml::filasTabla($tabla);
            $indiceTotales = null;
            foreach ($filas as $indice => $fila) {
                if (str_contains(InformeWordXml::textoFila($fila), 'TOTALES:')) {
                    $indiceTotales = $indice;
                    break;
                }
            }

            if ($indiceTotales !== null) {
                if ($deudas !== []) {
                    $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indiceTotales, [
                        self::formatoQuetzales(self::sumarMoneda($deudas, 'monto')),
                        self::formatoQuetzales(self::sumarMoneda($deudas, 'saldo')),
                        self::formatoQuetzales(self::sumarMoneda($deudas, 'cuota')),
                    ], 1);
                }

                $tabla = InformeWordXml::podarFilasDatosVacias($tabla, 3, $indiceTotales);
            } else {
                $tabla = InformeWordXml::podarFilasDatosVacias($tabla, 3);
            }

            $tabla = InformeWordXml::ajustarAnchosColumnas($tabla, [
                1 => 1900,
                2 => 1900,
                3 => 1750,
                4 => 1100,
                5 => 1000,
            ]);

            return InformeWordXml::reemplazarMarcadores($tabla, ['xxxxxx' => '']);
        });
    }

    private static function rellenarNarrativaEconomica(string $xml, string $texto): string
    {
        $texto = trim($texto);
        if ($texto === '') {
            return $xml;
        }

        $lineas = self::lineasDesdeTexto($texto);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'ASPECTO ECONÓMICO', function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);

            foreach ($filas as $indice => $fila) {
                if (! str_contains(InformeWordXml::textoFila($fila), 'xxxxx')) {
                    continue;
                }

                $celdas = InformeWordXml::celdasFila($fila);
                if ($celdas === []) {
                    break;
                }

                $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
                preg_match('/<w:tr\b[^>]*>/', $fila, $apertura);
                $filas[$indice] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
                break;
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /** @param list<array{pregunta: string, respuesta: string}> $filas */
    private static function rellenarTablaComplementaria(string $xml, array $filas): string
    {
        if ($filas === []) {
            return $xml;
        }

        $lineas = self::lineasPreguntasRespuestas($filas);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA Y ACTIVIDADES DE RIESGO', function (string $tabla) use ($lineas): string {
            $filasTabla = InformeWordXml::filasTabla($tabla);
            if (! isset($filasTabla[1])) {
                return InformeWordXml::reemplazarMarcadores($tabla, ['xxxx' => '']);
            }

            $celdas = InformeWordXml::celdasFila($filasTabla[1]);
            if ($celdas === []) {
                return $tabla;
            }

            $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
            preg_match('/<w:tr\b[^>]*>/', $filasTabla[1], $apertura);
            $filasTabla[1] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';

            return InformeWordXml::reconstruirTabla($tabla, $filasTabla);
        });
    }

    private static function rellenarNarrativas(string $xml, Orden $orden, EvaluadoOrden $evaluado, string $variante): string
    {
        $narrativas = InformeWordNarrativas::compilar($orden, $evaluado, $variante);
        $bloquesWord = InformeWordBloquesEvaluador::mapa($evaluado->id);

        if ($variante === InformeWordPlantillas::VARIANTE_SOCIO) {
            $xml = self::asegurarTablaRecomendacionesSocio($xml);
        }

        // Peri/espe: quitar el recuadro RECOMENDACIONES del medio (entre laboral y económico)
        // antes de volcar textos, para no matchear «RECOMENDACIONES» dentro de lo que escriba ella.
        if (InformeWordPlantillas::esVariantePeriodicaLike($variante)) {
            $xml = InformeWordXml::eliminarTablaPorMarcador($xml, 'RECOMENDACIONES');
        } else {
            $xml = self::quitarTablaLaborComplementaria($xml, $variante);
        }

        // Sin redacción del evaluador se usan las respuestas del cuestionario para que el apartado
        // económico no se entregue en blanco.
        $economico = trim($bloquesWord['word_economico'] ?? '');
        if ($economico === '') {
            $economico = InformeWordEconomico::narrativa($evaluado->cuestionario);
        }

        $xml = self::rellenarNarrativaEconomica($xml, $economico);
        if ($economico === '') {
            $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, 'ASPECTO ECONÓMICO', function (string $tabla): string {
                return InformeWordXml::reemplazarMarcadores($tabla, ['xxxxx' => '']);
            });
        }
        $xml = self::rellenarObservacionesDeudasSocio($xml, $economico);
        $xml = self::rellenarNarrativaSalud($xml, $narrativas['salud']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'HÁBITOS PERSONALES', $narrativas['habitos']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'ACTIVIDADES DELICTIVAS', $narrativas['drogas']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'ASPECTOS JUDICIALES', $narrativas['judicial']);
        if ($variante === InformeWordPlantillas::VARIANTE_SOCIO
            || ($evaluado->tipo_servicio ?? '') === 'socioeconomico') {
            $xml = self::rellenarAspectoLaboralSocio($xml, $bloquesWord['word_laboral'] ?? '');
            $xml = self::rellenarRecomendacionesGeneralidades($xml, $narrativas['recomendaciones']);
        } elseif (InformeWordPlantillas::esVariantePeriodicaLike($variante)) {
            // N-L1: aspecto laboral debajo del historial; recomendaciones al final.
            $xml = self::rellenarAspectoLaboralComplementaria($xml, $bloquesWord['word_laboral'] ?? '');
            $xml = self::rellenarNarrativaTablaFila1(
                $xml,
                'OBSERVACIONES ADICIONALES',
                $narrativas['recomendaciones']
            );
        } else {
            // Preempleo: aspecto laboral en recuadro propio. La Q&A de licencia/metas se restaura (O-C2).
            $xml = self::rellenarAspectoLaboralPreempleo($xml, $bloquesWord['word_laboral'] ?? '');
            $xml = self::rellenarNarrativaTablaFila1($xml, 'RECOMENDACIONES', $narrativas['recomendaciones']);
        }
        // Preempleo + socio: Q&A licencia/metas. Peri/espe: no (ella 28-ago 07:38).
        if ($variante === InformeWordPlantillas::VARIANTE_SOCIO
            || ($evaluado->tipo_servicio ?? '') === 'socioeconomico'
            || $variante === InformeWordPlantillas::VARIANTE_PREEMPLEO
            || ($evaluado->tipo_formulario ?? '') === 'preempleo') {
            $xml = self::rellenarInformacionComplementariaTabla($xml, $narrativas['informacion_complementaria']);
        }
        $xml = self::rellenarPoligraficaTabla($xml, $narrativas['preguntas_poligraficas'], $evaluado);
        $xml = self::rellenarConclusiones($xml, $narrativas['nombre_candidato']);
        $xml = self::rellenarResultadoUltimaHoja($xml, $evaluado);
        $xml = self::rellenarApa($xml, $narrativas['poligrafista']);

        return self::finalizarTablaAspectoEconomico($xml);
    }

    /**
     * Socio: el párrafo de Aspecto laboral va bajo EMPLEOS, con el título que ella usa
     * (INFORMACIÓN COMPLEMENTARIA LABORAL). No restaura la Q&A de N-C1.
     */
    private static function rellenarAspectoLaboralSocio(string $xml, string $texto): string
    {
        return self::rellenarRecuadroNarrativoTrasHistorial(
            $xml,
            $texto,
            'INFORMACIÓN COMPLEMENTARIA LABORAL:',
            ['EMPLEOS:', 'EMPLEOS', 'INFORMACIÓN LABORAL']
        );
    }

    /** Preempleo: recuadro ASPECTO LABORAL bajo el historial, sin tocar la Q&A de INFORMACIÓN COMPLEMENTARIA. */
    private static function rellenarAspectoLaboralPreempleo(string $xml, string $texto): string
    {
        return self::rellenarRecuadroNarrativoTrasHistorial(
            $xml,
            $texto,
            'ASPECTO LABORAL:',
            ['INFORMACIÓN LABORAL']
        );
    }

    /**
     * @param  list<string>  $marcadoresTras
     */
    private static function rellenarRecuadroNarrativoTrasHistorial(
        string $xml,
        string $texto,
        string $titulo,
        array $marcadoresTras
    ): string {
        $lineas = self::lineasDesdeTexto($texto);
        $marcador = rtrim($titulo, ':');
        if (InformeWordXml::limitesTablaPorMarcador($xml, $marcador) === null) {
            $tras = 'INFORMACIÓN LABORAL';
            foreach ($marcadoresTras as $candidato) {
                if (InformeWordXml::limitesTablaPorMarcador($xml, $candidato) !== null) {
                    $tras = $candidato;
                    break;
                }
            }
            $xml = InformeWordXml::insertarTablaTrasMarcador(
                $xml,
                $tras,
                InformeWordXml::tablaTituloYCuerpo($titulo, $lineas[0] ?? '—')
            );
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (! isset($filas[1])) {
                return $tabla;
            }
            $celdas = InformeWordXml::celdasFila($filas[1]);
            if ($celdas === []) {
                return $tabla;
            }
            $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
            preg_match('/<w:tr\b[^>]*>/', $filas[1], $apertura);
            $filas[1] = ($apertura[0] ?? '<w:tr>').implode('', $celdas).'</w:tr>';

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /** Peri/espe: título + cuerpo de aspecto laboral en INFORMACIÓN COMPLEMENTARIA (N-L1). */
    private static function rellenarAspectoLaboralComplementaria(string $xml, string $texto): string
    {
        $lineas = self::lineasDesdeTexto($texto);
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA');
        $esTablaPreguntas = false;
        if ($limites !== null) {
            $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
            $plano = InformeWordXml::textoTablaConcatenado($tabla);
            $esTablaPreguntas = str_contains($plano, 'Licencia de conducir')
                || count(InformeWordXml::filasTabla($tabla)) > 3;
        }

        if ($esTablaPreguntas || $limites === null) {
            if ($limites !== null) {
                $xml = InformeWordXml::eliminarTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA');
            }

            $nueva = InformeWordXml::tablaTituloYCuerpo('INFORMACIÓN COMPLEMENTARIA:', $lineas[0] ?? '—');
            $xml = InformeWordXml::insertarTablaTrasMarcador($xml, 'INFORMACIÓN LABORAL', $nueva);

            return InformeWordXml::reemplazarTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA', function (string $tabla) use ($lineas): string {
                $filas = InformeWordXml::filasTabla($tabla);
                if (! isset($filas[1])) {
                    return $tabla;
                }
                $celdas = InformeWordXml::celdasFila($filas[1]);
                if ($celdas === []) {
                    return $tabla;
                }
                $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
                preg_match('/<w:tr\b[^>]*>/', $filas[1], $apertura);
                $filas[1] = ($apertura[0] ?? '<w:tr>').implode('', $celdas).'</w:tr>';

                return InformeWordXml::reconstruirTabla($tabla, $filas);
            });
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA', function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (! isset($filas[0], $filas[1])) {
                return $tabla;
            }

            $celdas = InformeWordXml::celdasFila($filas[1]);
            if ($celdas === []) {
                return $tabla;
            }

            $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
            preg_match('/<w:tr\b[^>]*>/', $filas[1], $apertura);
            $filas[1] = ($apertura[0] ?? '<w:tr>').implode('', $celdas).'</w:tr>';

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    private static function rellenarNarrativaTablaFila1(string $xml, string $marcador, string $texto): string
    {
        $lineas = self::lineasDesdeTexto($texto);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);
            if (! isset($filas[1])) {
                return $tabla;
            }

            $celdas = InformeWordXml::celdasFila($filas[1]);
            if ($celdas === []) {
                return $tabla;
            }

            $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
            preg_match('/<w:tr\b[^>]*>/', $filas[1], $apertura);
            $filas[1] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /** Socio: la fila de GENERALIDADES, no un bloque RECOMENDACIONES aparte. */
    private static function rellenarRecomendacionesGeneralidades(string $xml, string $texto): string
    {
        $texto = trim($texto);
        if ($texto === '' || $texto === '—') {
            return $xml;
        }

        $lineas = self::lineasDesdeTexto($texto);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'RECOMENDACIONES - OBSERVACIONES', function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);
            foreach ($filas as $indice => $filaXml) {
                if (! str_contains(InformeWordXml::textoFila($filaXml), 'RECOMENDACIONES - OBSERVACIONES')) {
                    continue;
                }

                $celdas = InformeWordXml::celdasFila($filaXml);
                if (count($celdas) < 2) {
                    $filas[$indice] = InformeWordXml::establecerFila(
                        $filaXml,
                        ['RECOMENDACIONES - OBSERVACIONES: '.implode(' ', $lineas)],
                        0
                    );
                    break;
                }

                $celdas[1] = InformeWordXml::establecerCeldaParrafos($celdas[1], $lineas);
                preg_match('/<w:tr\b[^>]*>/', $filaXml, $apertura);
                $filas[$indice] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
                break;
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    /**
     * @param  list<array{etiqueta: string, respuesta: string}>  $filasDatos
     */
    private static function rellenarInformacionComplementariaTabla(string $xml, array $filasDatos): string
    {
        if ($filasDatos === []) {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'INFORMACIÓN COMPLEMENTARIA:', function (string $tabla) use ($filasDatos): string {
            $filas = InformeWordXml::filasTabla($tabla);
            $porClave = [];
            foreach ($filasDatos as $filaDatos) {
                $etiqueta = (string) ($filaDatos['etiqueta'] ?? $filaDatos['pregunta'] ?? '');
                $clave = self::claveComplementaria($etiqueta);
                if ($clave !== '') {
                    $porClave[$clave] = self::texto($filaDatos['respuesta'] ?? '');
                }
            }

            foreach ($filas as $indice => $filaXml) {
                $celdas = InformeWordXml::celdasFila($filaXml);
                if ($celdas === []) {
                    continue;
                }

                $claveFila = self::claveComplementaria(InformeWordXml::textoCelda($celdas[0]));
                if ($claveFila === '' || ! isset($porClave[$claveFila])) {
                    continue;
                }

                $respuesta = $porClave[$claveFila];
                $filas[$indice] = InformeWordXml::establecerFila($filaXml, [$respuesta !== '' ? $respuesta : '—'], 1);
            }

            $tabla = InformeWordXml::reconstruirTabla($tabla, $filas);

            return InformeWordXml::eliminarFilasPorEtiquetas($tabla, [
                'Colaboración y actitud durante el proceso',
                'Observaciones adicionales',
            ]);
        });
    }

    /**
     * @param  list<array{pregunta: string, respuesta: string, resultado: string, puntuacion: string}>  $filas
     */
    private static function rellenarPoligraficaTabla(string $xml, array $filas, ?EvaluadoOrden $evaluado = null): string
    {
        if ($filas === []) {
            return $xml;
        }

        $marcador = InformeWordXml::limitesTablaPorMarcador($xml, 'PREGUNTA RELEVANTE') !== null
            ? 'PREGUNTA RELEVANTE'
            : 'Preguntas:';
        $conPuntuacion = $evaluado === null || InformeWordPreguntasPoligraficas::usaPuntuacion($evaluado);
        $colorDi = $evaluado !== null && InformeWordPreguntasPoligraficas::filasGuardadas($evaluado->id) !== [];

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($filas, $conPuntuacion, $colorDi): string {
            $filasTabla = InformeWordXml::filasTabla($tabla);
            if (! isset($filasTabla[0])) {
                return $tabla;
            }

            $plantillaFila = $filasTabla[1] ?? $filasTabla[0];
            $columnasPlantilla = count(InformeWordXml::celdasFila($plantillaFila));
            $resultado = [$filasTabla[0]];

            foreach ($filas as $indice => $filaDatos) {
                $filaPlantilla = $filasTabla[$indice + 1] ?? $plantillaFila;
                $numero = (string) ($indice + 1);
                $respuesta = InformeWordPreguntasPoligraficas::respuestaParaWord($filaDatos['respuesta'] ?? '');
                $resultadoCodigo = self::texto($filaDatos['resultado'] ?? '');
                $puntuacion = self::texto($filaDatos['puntuacion'] ?? '');
                $pregunta = self::texto($filaDatos['pregunta'] ?? '');

                // Periódica/específica: dejar pregunta vacía si no hay texto (editable en plantilla).
                if ($pregunta === '—') {
                    $pregunta = '';
                }
                if ($resultadoCodigo === '') {
                    $resultadoCodigo = '—';
                }

                $valores = [$numero, $pregunta, $respuesta, $resultadoCodigo];
                if ($conPuntuacion && $columnasPlantilla >= 5) {
                    $valores[] = $puntuacion === '' ? '—' : $puntuacion;
                }

                $filaXml = InformeWordXml::establecerFila($filaPlantilla, $valores, 0);
                if ($colorDi && InformeWordPreguntasPoligraficas::esDi($resultadoCodigo)) {
                    $filaXml = InformeWordXml::aplicarColorFila($filaXml, 'FF0000');
                }
                $resultado[] = $filaXml;
            }

            return InformeWordXml::reconstruirTabla($tabla, $resultado);
        });
    }

    private static function rellenarConclusiones(string $xml, string $nombreCandidato): string
    {
        if ($nombreCandidato === '' || $nombreCandidato === '—') {
            return $xml;
        }

        foreach (['NOMBRE DEL CANDIDATO', 'NOMBREDECANDIDATO', 'XXXXXXXX'] as $marcador) {
            $xml = InformeWordXml::reemplazarTexto($xml, $marcador, $nombreCandidato, 1);
        }

        return $xml;
    }

    /** M-P3 / N-R2: misma opción de la 1ª hoja en la última; poli/VSA/socio sin [ X ]. */
    private static function rellenarResultadoUltimaHoja(string $xml, EvaluadoOrden $evaluado): string
    {
        if (InformeWordResultado::esSocio($evaluado)) {
            return InformeWordXml::reemplazarTablaPorMarcador(
                $xml,
                InformeWordResultado::MARCADOR_CONCLUSIONES_SOCIO,
                fn (string $tabla): string => self::marcarOpcionesTabla(
                    $tabla,
                    InformeWordResultado::opcionMarcadaSocio($evaluado),
                    [InformeWordResultado::class, 'opcionDeTextoSocio'],
                    false
                )
            );
        }

        $xml = self::normalizarFraseClasificaEvaluado($xml);

        $marcada = InformeWordResultado::opcionMarcada($evaluado);
        $detalles = InformeWordResultado::detalles($evaluado->id);
        $frase = InformeWordResultado::fraseVeracidad($marcada);
        if ($frase !== null) {
            $xml = InformeWordXml::reemplazarTexto(
                $xml,
                InformeWordResultado::FRASE_NO_VERACIDAD_PLANTILLA,
                $frase,
                1
            );
        }

        if ($marcada === InformeWordResultado::OPCION_NO_APROBADO) {
            $autoDi = InformeWordPreguntasPoligraficas::textoConclusionDi(
                InformeWordPreguntasPoligraficas::filasGuardadas($evaluado->id)
            );
            $preguntas = $autoDi !== '' ? $autoDi : trim($detalles['mentira']);
            $xml = InformeWordXml::reemplazarTexto(
                $xml,
                InformeWordResultado::MARCADOR_PREGUNTAS_DI,
                $preguntas,
                1
            );
            if ($autoDi !== '') {
                $xml = InformeWordXml::forzarColorEnTexto($xml, $autoDi, 'FF0000');
            }
            // P-R1: el cuadro vacío de 3 filas ya no se usa; las preguntas van en el párrafo.
            $primera = trim((string) (preg_split("/\r\n|\r|\n/", $preguntas)[0] ?? ''));
            $ancla = $primera !== '' ? $primera : InformeWordResultado::MARCADOR_PREGUNTAS_DI;
            $xml = InformeWordXml::eliminarTablaSiguienteTrasParrafo($xml, $ancla);
        } elseif ($marcada === InformeWordResultado::OPCION_APROBADO
            || $marcada === InformeWordResultado::OPCION_EXCEPCION) {
            $xml = InformeWordXml::eliminarParrafoYTablaSiguiente(
                $xml,
                InformeWordResultado::MARCADOR_PREGUNTAS_DI
            );
        }

        if ($frase !== null) {
            $xml = InformeWordXml::forzarColorEnTexto($xml, $frase, '000000');
        }

        return InformeWordXml::reemplazarTablaPorMarcador(
            $xml,
            InformeWordResultado::MARCADOR_ULTIMA_HOJA,
            function (string $tabla) use ($marcada, $detalles): string {
                $tabla = self::marcarOpcionesTabla(
                    $tabla,
                    $marcada,
                    [InformeWordResultado::class, 'opcionDeTextoUltimaHoja'],
                    false
                );

                if ($marcada !== InformeWordResultado::OPCION_EXCEPCION || $detalles['excepcion'] === '') {
                    return $tabla;
                }

                $filas = InformeWordXml::filasTabla($tabla);
                foreach ($filas as $indice => $fila) {
                    if (! str_contains(mb_strtoupper(InformeWordXml::textoFila($fila)), 'ASPECTO QUE ORIGINA')) {
                        continue;
                    }
                    $celdas = InformeWordXml::celdasFila($fila);
                    if ($celdas === []) {
                        break;
                    }
                    $destino = $celdas[1] ?? $celdas[0];
                    $destino = InformeWordXml::prefijarTextoCeldaFinal($destino, ' '.$detalles['excepcion']);
                    if (isset($celdas[1])) {
                        $celdas[1] = $destino;
                    } else {
                        $celdas[0] = $destino;
                    }
                    preg_match('/<w:tr\b[^>]*>/', $fila, $apertura);
                    $filas[$indice] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
                    break;
                }

                return InformeWordXml::reconstruirTabla($tabla, $filas);
            }
        );
    }

    /** N-R3: «se clasifica al evaluado(a):» fijo, sin a la / como, para todos los poli/VSA. */
    private static function normalizarFraseClasificaEvaluado(string $xml): string
    {
        $destino = InformeWordResultado::FRASE_CLASIFICA;
        foreach ([
            InformeWordResultado::FRASE_CLASIFICA_PLANTILLA,
            'se clasifica a la evaluado(a) como',
            'se clasifica al evaluado como',
        ] as $origen) {
            if ($origen === $destino) {
                continue;
            }
            $xml = InformeWordXml::reemplazarTexto($xml, $origen, $destino, 1);
        }

        return InformeWordXml::forzarColorEnTexto($xml, $destino, '000000');
    }

    /**
     * Deja solo la fila de la opción marcada (más el título). Poli/VSA/socio: sin [ X ].
     *
     * @param  callable(string): (?string)  $opcionDeTexto
     */
    private static function marcarOpcionesTabla(
        string $tabla,
        ?string $marcada,
        callable $opcionDeTexto,
        bool $conMarca = true
    ): string {
        if ($marcada === null) {
            return $tabla;
        }

        $filas = InformeWordXml::filasTabla($tabla);
        foreach ($filas as $indice => $fila) {
            $opcion = $opcionDeTexto(InformeWordXml::textoFila($fila));
            if ($opcion !== $marcada) {
                continue;
            }

            $celdas = InformeWordXml::celdasFila($fila);
            if ($celdas === []) {
                continue;
            }

            if ($conMarca) {
                $celdas[0] = InformeWordXml::prefijarTextoCelda($celdas[0], InformeWordResultado::MARCA);
            } else {
                $celdas[0] = InformeWordXml::reemplazarTexto($celdas[0], InformeWordResultado::MARCA, '');
            }
            preg_match('/<w:tr\b[^>]*>/', $fila, $apertura);
            $filas[$indice] = ($apertura[0] ?? '<w:tr>') . implode('', $celdas) . '</w:tr>';
        }

        return InformeWordXml::reconstruirTabla(
            $tabla,
            self::filasOpcionSeleccionada($filas, $marcada, $opcionDeTexto)
        );
    }

    /**
     * @param  list<string>  $filas
     * @param  callable(string): (?string)  $opcionDeTexto
     * @return list<string>
     */
    private static function filasOpcionSeleccionada(array $filas, ?string $marcada, callable $opcionDeTexto): array
    {
        if ($marcada === null) {
            return $filas;
        }

        $conservadas = [];
        foreach ($filas as $indice => $fila) {
            $texto = InformeWordXml::textoFila($fila);
            $opcion = $opcionDeTexto($texto);
            if ($opcion === $marcada) {
                $conservadas[] = $fila;

                continue;
            }
            if ($opcion !== null) {
                continue;
            }
            if ($indice === 0) {
                $conservadas[] = $fila;

                continue;
            }
            if ($marcada === InformeWordResultado::OPCION_EXCEPCION
                && str_contains(mb_strtoupper($texto), 'ASPECTO QUE ORIGINA')) {
                $conservadas[] = $fila;
            }
        }

        return $conservadas !== [] ? $conservadas : $filas;
    }

    /**
     * N-A1 / N-A2: las firmas de plantilla (Stefanie/Rodrigo/Narda o Aldin) se quedan.
     * En polígrafo se ocultan los bordes de esa tabla; VSA no se reescribe.
     */
    private static function rellenarApa(string $xml, string $poligrafista): string
    {
        unset($poligrafista);

        return InformeWordXml::ocultarBordesTablasQueContienen($xml, 'Poligrafista Certificado');
    }

    /** @return list<string> */
    private static function lineasDesdeTexto(string $texto): array
    {
        $texto = trim($texto);
        if ($texto === '' || $texto === '—') {
            return ['—'];
        }

        $lineas = preg_split("/\r\n|\r|\n/", $texto) ?: [];
        $lineas = array_values(array_filter(array_map(
            static fn (string $linea): string => trim($linea),
            $lineas
        ), static fn (string $linea): bool => $linea !== ''));

        return $lineas !== [] ? $lineas : ['—'];
    }

    private static function optimizarLayoutSecciones(string $xml, string $variante): string
    {
        $marcadoresTitulo = [
            'DATOS FAMILIARES',
            'INFORMACIÓN ACADÉMICA',
            'INFORMACIÓN LABORAL',
            'INFORMACIÓN COMPLEMENTARIA',
            'INFORMACIÓN COMPLEMENTARIA Y ACTIVIDADES DE RIESGO',
            'ASPECTO ECONÓMICO',
            'ASPECTOS DE SALUD',
            'HÁBITOS PERSONALES',
            'ACTIVIDADES DELICTIVAS',
            'ASPECTOS JUDICIALES',
            'RECOMENDACIONES',
            'OBSERVACIONES ADICIONALES',
        ];

        foreach ($marcadoresTitulo as $marcador) {
            if (! str_contains($xml, $marcador)) {
                continue;
            }

            $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla): string {
                return InformeWordXml::aplicarKeepNextFilaTitulo($tabla, 0);
            });
        }

        $xml = InformeWordXml::compactarEntreTablasPorMarcadores(
            $xml,
            'ASPECTO ECONÓMICO',
            'ASPECTOS DE SALUD'
        );

        if (InformeWordPlantillas::esVariantePeriodicaLike($variante)
            || $variante === InformeWordPlantillas::VARIANTE_PREEMPLEO) {
            $xml = InformeWordXml::compactarEntreTablasPorMarcadores(
                $xml,
                'INFORMACIÓN LABORAL',
                'INFORMACIÓN COMPLEMENTARIA'
            );
            $xml = InformeWordXml::compactarEntreTablasPorMarcadores(
                $xml,
                'INFORMACIÓN COMPLEMENTARIA',
                'ASPECTO ECONÓMICO'
            );
        } else {
            $siguiente = str_contains($xml, 'ASPECTO ECONÓMICO')
                ? 'ASPECTO ECONÓMICO'
                : (str_contains($xml, 'Información brindada por el candidato') ? 'Información brindada por el candidato' : '');
            if ($siguiente !== '') {
                $xml = InformeWordXml::compactarEntreTablasPorMarcadores(
                    $xml,
                    str_contains($xml, 'INFORMACIÓN LABORAL') ? 'INFORMACIÓN LABORAL' : 'EMPLEOS:',
                    $siguiente
                );
            }
        }

        return InformeWordXml::separarTablasContiguas($xml);
    }

    /** M-P6: historial laboral sí; la tabla Q&A complementaria laboral se elimina en preempleo/socio. Peri/espe conservan el recuadro INFORMACIÓN COMPLEMENTARIA para word_laboral (N-L1). */
    private static function quitarTablaLaborComplementaria(string $xml, string $variante): string
    {
        if (InformeWordPlantillas::esVariantePeriodicaLike($variante)) {
            return $xml;
        }

        $marcador = self::marcadorLaborComplementaria($xml, $variante);

        return InformeWordXml::eliminarTablaPorMarcador($xml, $marcador);
    }

    /**
     * @param  list<array{pregunta: string, respuesta: string}>  $filas
     * @return list<string>
     */
    private static function lineasPreguntasRespuestas(array $filas, bool $numerar = false): array
    {
        $lineas = [];

        foreach (array_values($filas) as $indice => $fila) {
            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            $respuesta = trim((string) ($fila['respuesta'] ?? ''));

            if ($pregunta === '' && $respuesta === '') {
                continue;
            }

            if ($numerar) {
                $lineas[] = ($indice + 1) . '. ' . $pregunta;
            } else {
                $lineas[] = $pregunta;
            }

            $lineas[] = $respuesta;
            $lineas[] = '';
        }

        return $lineas;
    }

    private static function texto(mixed $valor): string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? '' : $texto;
    }

    /** @param list<array<string, mixed>> $tatuajes */
    private static function rellenarTablaTatuajes(string $xml, array $tatuajes): string
    {
        if ($tatuajes === []) {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'TATUAJES', function (string $tabla) use ($tatuajes): string {
            $filas = InformeWordXml::filasTabla($tabla);
            $titulo = $filas[0] ?? $tabla;
            $encabezado = null;
            $plantillaDatos = null;
            $filaRiesgo = null;

            foreach ($filas as $filaXml) {
                $texto = InformeWordXml::textoFila($filaXml);
                if ($encabezado === null && str_contains($texto, 'Ubicación') && str_contains($texto, 'Tamaño')) {
                    $encabezado = $filaXml;
                    continue;
                }
                if ($filaRiesgo === null && str_contains($texto, 'Nivel de riesgo')) {
                    $filaRiesgo = $filaXml;
                    continue;
                }
                if ($plantillaDatos === null && preg_match('/^\s*\d+\s*$/u', trim($texto)) === 1) {
                    $plantillaDatos = $filaXml;
                }
            }

            if ($encabezado === null || $plantillaDatos === null) {
                foreach (array_values($tatuajes) as $indice => $tatuaje) {
                    $fila = $indice + 1;
                    if ($fila > 8) {
                        break;
                    }
                    $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, self::celdasTatuaje($indice, $tatuaje), 0);
                }

                return InformeWordXml::podarFilasDatosVacias($tabla, 1);
            }

            $nuevas = [$titulo, $encabezado];
            foreach (array_values($tatuajes) as $indice => $tatuaje) {
                $nuevas[] = InformeWordXml::establecerFila($plantillaDatos, self::celdasTatuaje($indice, $tatuaje), 0);
            }
            if ($filaRiesgo !== null) {
                $nuevas[] = $filaRiesgo;
            }

            return InformeWordXml::reconstruirTabla($tabla, $nuevas);
        });
    }

    private static function marcadorTablaDeudas(string $xml): string
    {
        foreach (['ASPECTO ECONÓMICO', 'HISTORIAL CREDITICIO', 'Deudas:'] as $marcador) {
            if (InformeWordXml::limitesTablaPorMarcador($xml, $marcador) !== null) {
                return $marcador;
            }
        }

        return 'ASPECTO ECONÓMICO';
    }

    private static function rellenarObservacionesDeudasSocio(string $xml, string $texto): string
    {
        $texto = trim($texto);
        if ($texto === '' || InformeWordXml::limitesTablaPorMarcador($xml, 'ASPECTO ECONÓMICO') !== null) {
            return $xml;
        }

        $marcador = self::marcadorTablaDeudas($xml);
        $lineas = self::lineasDesdeTexto($texto);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla) use ($lineas): string {
            $filas = InformeWordXml::filasTabla($tabla);
            foreach ($filas as $indice => $filaXml) {
                if (! str_contains(InformeWordXml::textoFila($filaXml), 'Observaciones')) {
                    continue;
                }
                $celdas = InformeWordXml::celdasFila($filaXml);
                if (count($celdas) < 2) {
                    $filas[$indice] = InformeWordXml::establecerFila($filaXml, ['Observaciones: '.implode(' ', $lineas)], 0);
                    break;
                }
                $filas[$indice] = InformeWordXml::establecerFila($filaXml, [implode("\n", $lineas)], 1);
                break;
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    private static function rellenarNarrativaSalud(string $xml, string $texto): string
    {
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, 'ASPECTOS DE SALUD');
        if ($limites === null) {
            return $xml;
        }

        $tabla = substr($xml, $limites[0], $limites[1] - $limites[0]);
        $filas = InformeWordXml::filasTabla($tabla);
        $esGrilla = isset($filas[1]) && str_contains(InformeWordXml::textoFila($filas[1]), 'Estado general');
        if (! $esGrilla) {
            return self::rellenarNarrativaTablaFila1($xml, 'ASPECTOS DE SALUD', $texto);
        }

        $texto = trim($texto);
        if ($texto === '') {
            return $xml;
        }

        // M-S4: fila combinada a lo ancho, como Observaciones de deudas / xxxxx económico.
        $plantilla = $filas[array_key_last($filas)] ?? $filas[1];
        $nCeldas = count(InformeWordXml::celdasFila($plantilla));
        $filaCombinada = $nCeldas > 1
            ? InformeWordXml::combinarCeldasFila($plantilla, 0, $nCeldas - 1)
            : $plantilla;
        $celdas = InformeWordXml::celdasFila($filaCombinada);
        if ($celdas === []) {
            return $xml;
        }

        $lineas = self::lineasDesdeTexto($texto);
        array_unshift($lineas, 'Observaciones:');
        $celdas[0] = InformeWordXml::establecerCeldaParrafos($celdas[0], $lineas);
        preg_match('/<w:tr\b[^>]*>/', $filaCombinada, $apertura);
        $filas[] = ($apertura[0] ?? '<w:tr>').implode('', $celdas).'</w:tr>';

        return substr($xml, 0, $limites[0])
            .InformeWordXml::reconstruirTabla($tabla, $filas)
            .substr($xml, $limites[1]);
    }

    private static function clavePresupuesto(string $etiqueta): string
    {
        $n = self::normalizarEtiqueta($etiqueta);

        return match (true) {
            str_contains($n, 'alimentacion') => 'alimentacion',
            str_contains($n, 'alquiler') => 'alquiler',
            str_contains($n, 'vestuario') => 'vestuario',
            str_contains($n, 'transporte') => 'transporte',
            str_contains($n, 'servicios') => 'servicios',
            str_contains($n, 'medico') || str_contains($n, 'medito') => 'medicos',
            str_contains($n, 'colegiatura') => 'colegiaturas',
            str_contains($n, 'prestamo') => 'prestamos',
            str_contains($n, 'manutencion') => 'manutencion',
            str_contains($n, 'otros gastos') => 'otros',
            str_contains($n, 'totale') => 'totales',
            default => '',
        };
    }

    private static function normalizarEtiqueta(string $etiqueta): string
    {
        $n = mb_strtolower(trim($etiqueta));
        $n = strtr($n, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', '¿' => '', '?' => '']);

        return rtrim($n, '.:');
    }

    private static function etiquetaCoincide(string $celda, string $esperada): bool
    {
        if ($celda === $esperada) {
            return true;
        }

        return self::normalizarEtiqueta($celda) === self::normalizarEtiqueta($esperada);
    }

    private static function marcadorLaborComplementaria(string $xml, string $variante = ''): string
    {
        $candidatos = [
            'INFORMACIÓN COMPLEMENTARIA LABORAL',
            'INFORMACIÓN LABORAL COMPLEMENTARIA',
        ];

        foreach ($candidatos as $marcador) {
            if (InformeWordXml::limitesTablaPorMarcador($xml, $marcador) !== null) {
                return $marcador;
            }
        }

        return 'INFORMACIÓN LABORAL COMPLEMENTARIA';
    }

    /** La plantilla socio no trae el bloque; se inserta para word_recomendaciones. */
    private static function asegurarTablaRecomendacionesSocio(string $xml): string
    {
        if (InformeWordXml::limitesTablaPorMarcador($xml, 'RECOMENDACIONES') !== null) {
            return $xml;
        }

        $tras = InformeWordXml::limitesTablaPorMarcador($xml, 'VÍNCULO CON ACTIVIDADES DELICTIVAS') !== null
            ? 'VÍNCULO CON ACTIVIDADES DELICTIVAS'
            : 'HÁBITOS PERSONALES';

        return InformeWordXml::insertarTablaTrasMarcador(
            $xml,
            $tras,
            InformeWordXml::tablaTituloYCuerpo('RECOMENDACIONES', '')
        );
    }

    private static function numeroMoneda(mixed $valor): float
    {
        $texto = trim((string) $valor);
        $texto = str_replace(["\xc2\xa0", ' '], '', $texto);
        $texto = preg_replace('/^[Qq]\.?/', '', $texto) ?? $texto;
        $texto = str_replace(',', '', $texto);
        $texto = preg_replace('/[^\d.]/', '', $texto) ?? '';

        return $texto !== '' && is_numeric($texto) ? (float) $texto : 0.0;
    }

    /** @param array<string, mixed> $tatuaje */
    private static function celdasTatuaje(int $indice, array $tatuaje): array
    {
        $visible = match ($tatuaje['visible_uniforme'] ?? '') {
            'si' => 'Sí',
            'no' => 'No',
            default => self::texto($tatuaje['visible_uniforme'] ?? ''),
        };

        return [
            (string) ($indice + 1),
            self::texto($tatuaje['ubicacion'] ?? ''),
            self::texto($tatuaje['tamano'] ?? ''),
            self::texto($tatuaje['descripcion'] ?? ''),
            self::texto($tatuaje['tiempo'] ?? ''),
            $visible,
            self::texto($tatuaje['significado'] ?? ''),
        ];
    }

    private static function moneda(mixed $valor): string
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return '';
        }

        if (preg_match('/^[Qq]/', $texto) === 1) {
            return $texto;
        }

        $numerico = preg_replace('/[^\d.,]/', '', $texto) ?? $texto;
        if ($numerico === '') {
            return $texto;
        }

        return 'Q' . $numerico;
    }

    private static function edad(mixed $valor): string
    {
        $texto = self::texto($valor);
        if ($texto === '') {
            return '';
        }

        if (str_contains($texto, 'año')) {
            return $texto;
        }

        return $texto . ' años';
    }

    /** @param list<array<string, mixed>> $deudas */
    private static function sumarMoneda(array $deudas, string $campo): float
    {
        $total = 0.0;

        foreach ($deudas as $deuda) {
            $total += self::numeroMoneda($deuda[$campo] ?? '');
        }

        return $total;
    }

    private static function formatoQuetzales(float $monto): string
    {
        if ($monto <= 0) {
            return 'Q.';
        }

        return 'Q.' . "\xc2\xa0" . number_format($monto, 2, '.', ',');
    }
}
