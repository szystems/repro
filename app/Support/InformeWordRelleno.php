<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\Orden;

/** Rellena encabezado y tablas de plantillas Word oficiales preservando diseño tabular. */
class InformeWordRelleno
{
    /**
     * @param  array{variante: string}  $config
     */
    public static function aplicar(string $xml, Orden $orden, EvaluadoOrden $evaluado, array $config): string
    {
        $valores = InformeWordDatos::encabezado($orden, $evaluado);
        $tablas = InformeWordDatos::tablas($evaluado);
        $variante = $config['variante'] ?? InformeWordPlantillas::VARIANTE_PREEMPLEO;

        $xml = self::rellenarEncabezado($xml, $valores, $evaluado);
        $xml = self::rellenarTablas($xml, $tablas, $variante);
        $xml = self::rellenarNarrativas($xml, $orden, $evaluado, $variante);

        return self::optimizarLayoutSecciones($xml, $variante);
    }

    /**
     * @param  array<string, string>  $valores
     */
    private static function rellenarEncabezado(string $xml, array $valores, EvaluadoOrden $evaluado): string
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
     */
    private static function rellenarTablas(string $xml, array $tablas, string $variante): string
    {
        if ($tablas === []) {
            return $xml;
        }

        $familiar = is_array($tablas['familiar'] ?? null) ? $tablas['familiar'] : [];
        $xml = self::rellenarTablaFamiliar($xml, $familiar);
        $xml = self::rellenarTablaEstadoCivil($xml, $familiar);
        $xml = self::rellenarTablaExpareja($xml, $familiar);
        $xml = self::rellenarTablaHijos($xml, is_array($familiar['hijos'] ?? null) ? $familiar['hijos'] : []);

        if ($variante === InformeWordPlantillas::VARIANTE_PREEMPLEO) {
            $xml = self::rellenarTablaHermanos($xml, is_array($familiar['hermanos'] ?? null) ? $familiar['hermanos'] : []);
            $xml = self::rellenarTablaComplementaria($xml, is_array($tablas['complementaria'] ?? null) ? $tablas['complementaria'] : []);
        } else {
            $xml = self::rellenarTablaLaborComplementaria($xml, is_array($tablas['labor_complementaria'] ?? null) ? $tablas['labor_complementaria'] : []);
        }

        $xml = self::rellenarTablaAcademica($xml, is_array($tablas['academico'] ?? null) ? $tablas['academico'] : []);
        $xml = self::rellenarTablaLaboral($xml, is_array($tablas['laboral'] ?? null) ? $tablas['laboral'] : [], $variante);
        $xml = self::rellenarTablaDeudas($xml, is_array($tablas['deudas'] ?? null) ? $tablas['deudas'] : []);

        return $xml;
    }

    /** @param array<string, mixed> $familiar */
    private static function rellenarTablaFamiliar(string $xml, array $familiar): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'DATOS FAMILIARES', function (string $tabla) use ($familiar): string {
            $padre = is_array($familiar['padre'] ?? null) ? $familiar['padre'] : [];
            $madre = is_array($familiar['madre'] ?? null) ? $familiar['madre'] : [];

            $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, 2, [
                self::texto($padre['nombre'] ?? ''),
                self::edad($padre['edad'] ?? ''),
                self::texto($padre['telefono'] ?? ''),
                '',
                self::texto($padre['ocupacion'] ?? ''),
            ], 1);

            $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, 3, [
                self::texto($madre['nombre'] ?? ''),
                self::edad($madre['edad'] ?? ''),
                self::texto($madre['telefono'] ?? ''),
                '',
                self::texto($madre['ocupacion'] ?? ''),
            ], 1);

            $convive = is_array($familiar['convive_con'] ?? null) ? implode(', ', $familiar['convive_con']) : '';

            return InformeWordXml::reemplazarFilaEnTabla($tabla, 4, [$convive], 1);
        });
    }

    /** @param array<string, mixed> $familiar */
    private static function rellenarTablaEstadoCivil(string $xml, array $familiar): string
    {
        $pareja = is_array($familiar['pareja'] ?? null) ? $familiar['pareja'] : [];

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'ESTADO CIVIL', function (string $tabla) use ($pareja): string {
            if (($pareja['tiene'] ?? false) === true) {
                $filas = [
                    1 => [self::texto($pareja['nombre'] ?? '')],
                    2 => [self::edad($pareja['edad'] ?? '')],
                    3 => [self::texto($pareja['telefono'] ?? '')],
                    4 => [self::texto($pareja['direccion'] ?? '')],
                    5 => [self::texto($pareja['ocupacion'] ?? '')],
                    6 => [self::texto($pareja['tipo'] ?? '')],
                    7 => [self::texto($pareja['calidad_relacion'] ?? '')],
                    8 => [self::texto($pareja['tiempo_relacion'] ?? '')],
                ];

                foreach ($filas as $indice => $valores) {
                    $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indice, $valores, 1);
                }
            }

            return InformeWordXml::eliminarFilasPorEtiquetas($tabla, ['Número de relación:']);
        });
    }

    /** @param array<string, mixed> $familiar */
    private static function rellenarTablaExpareja(string $xml, array $familiar): string
    {
        $expareja = is_array($familiar['expareja'] ?? null) ? $familiar['expareja'] : [];

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'EXPAREJAS', function (string $tabla) use ($expareja): string {
            $texto = '';

            if (($expareja['aplica'] ?? false) === true) {
                $texto = collect([
                    self::texto($expareja['nombre'] ?? '') !== '' ? 'Nombre: ' . self::texto($expareja['nombre'] ?? '') : '',
                    self::texto($expareja['tipo'] ?? '') !== '' ? 'Tipo de relación: ' . self::texto($expareja['tipo'] ?? '') : '',
                    self::texto($expareja['tiempo_relacion'] ?? '') !== '' ? 'Tiempo de relación: ' . self::texto($expareja['tiempo_relacion'] ?? '') : '',
                    isset($expareja['hijos_comun']) ? 'Hijos en común: ' . $expareja['hijos_comun'] : '',
                    isset($expareja['cantidad_hijos']) && $expareja['cantidad_hijos'] !== '' ? 'Cantidad de hijos: ' . $expareja['cantidad_hijos'] : '',
                    isset($expareja['problemas_legales']) ? 'Problemas legales: ' . $expareja['problemas_legales'] : '',
                ])->filter()->implode("\n");
            }

            return InformeWordXml::reemplazarMarcadores($tabla, [
                'xxxxxxx' => $texto,
                'xxxx' => $texto,
            ]);
        });
    }

    /** @param list<array<string, mixed>> $hijos */
    private static function rellenarTablaHijos(string $xml, array $hijos): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'HIJOS:', function (string $tabla) use ($hijos): string {
            foreach (array_values($hijos) as $indice => $hijo) {
                $fila = $indice + 1;
                if ($fila > 5) {
                    break;
                }

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, [
                    self::texto($hijo['nombre'] ?? ''),
                    self::edad($hijo['edad'] ?? ''),
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
            foreach (array_values($hermanos) as $indice => $hermano) {
                $fila = $indice + 2;
                if ($fila > 9) {
                    break;
                }

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, [
                    self::texto($hermano['nombre'] ?? ''),
                    self::edad($hermano['edad'] ?? ''),
                    self::texto($hermano['telefono'] ?? ''),
                    self::texto($hermano['direccion'] ?? ''),
                    self::texto($hermano['ocupacion'] ?? $hermano['lugar_trabajo'] ?? ''),
                ]);
            }

            return InformeWordXml::podarFilasDatosVacias($tabla, 2);
        });
    }

    /** @param list<array<string, mixed>> $filasAcademicas */
    private static function rellenarTablaAcademica(string $xml, array $filasAcademicas): string
    {
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

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'NIVEL ACADÉMICO', function (string $tabla) use ($porNivel, $mapaFilas): string {
            foreach ($mapaFilas as $nivel => $indiceFila) {
                if (! isset($porNivel[$nivel])) {
                    continue;
                }

                $fila = $porNivel[$nivel];
                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $indiceFila, [
                    self::texto($fila['institucion'] ?? ''),
                    self::texto($fila['carrera'] ?? $fila['estado'] ?? ''),
                    self::texto($fila['anio'] ?? ''),
                ], 1);
            }

            $tabla = InformeWordXml::eliminarFilasSinDatosEnRango($tabla, 1, 5, 1);

            $filas = InformeWordXml::filasTabla($tabla);
            $filas = array_values(array_filter($filas, function (string $fila): bool {
                if (str_contains(InformeWordXml::textoFila($fila), 'Estudia Actualmente:')) {
                    return ! self::filaCeldasVacias($fila, [1, 3]);
                }

                if (str_contains(InformeWordXml::textoFila($fila), 'Validación de constancia')) {
                    return ! self::filaCeldasVacias($fila, [1]);
                }

                return true;
            }));

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
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

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'INFORMACIÓN LABORAL', function (string $tabla) use ($empleos, $variante): string {
            foreach (array_values($empleos) as $indice => $empleo) {
                $fila = $indice + 2;
                if ($fila > 9) {
                    break;
                }

                if ($variante === InformeWordPlantillas::VARIANTE_PERIODICA) {
                    $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, [
                        self::texto($empleo['empresa'] ?? ''),
                        self::texto($empleo['puesto'] ?? ''),
                        self::texto($empleo['fechas'] ?? ''),
                        self::texto($empleo['salario'] ?? ''),
                        self::texto($empleo['motivo'] ?? ''),
                    ]);
                    break;
                }

                $fechas = trim((string) ($empleo['fechas_laboradas'] ?? ''));
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

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, [
                    self::texto($empleo['empresa'] ?? ''),
                    self::texto($empleo['puesto'] ?? ''),
                    $fechas,
                    self::texto($empleo['ultimo_salario'] ?? ''),
                    self::texto($empleo['motivo_retiro'] ?? ''),
                ]);
            }

            return InformeWordXml::podarFilasDatosVacias($tabla, 2);
        });
    }

    /** @param list<array<string, mixed>> $deudas */
    private static function rellenarTablaDeudas(string $xml, array $deudas): string
    {
        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'ASPECTO ECONÓMICO', function (string $tabla) use ($deudas): string {
            foreach (array_values($deudas) as $indice => $deuda) {
                $fila = $indice + 3;
                if ($fila > 10) {
                    break;
                }

                $estatus = $deuda['estatus'] ?? '';
                if ($estatus === 'al_dia') {
                    $estatus = 'Al día';
                } elseif ($estatus === 'atrasado') {
                    $estatus = 'Atrasado';
                } elseif ($estatus === 'pagado') {
                    $estatus = 'Pagado';
                }

                $tabla = InformeWordXml::reemplazarFilaEnTabla($tabla, $fila, [
                    self::texto($deuda['entidad'] ?? ''),
                    self::texto($deuda['monto'] ?? ''),
                    self::texto($deuda['saldo'] ?? ''),
                    self::texto($deuda['cuota'] ?? ''),
                    self::texto($deuda['motivo'] ?? ''),
                    self::texto($deuda['antiguedad'] ?? ''),
                    self::texto($estatus),
                    self::texto($deuda['meses_atraso'] ?? ''),
                ]);
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

            return InformeWordXml::reemplazarMarcadores($tabla, ['xxxxxx' => '', 'xxxxx' => '']);
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

        $xml = self::rellenarNarrativaTablaFila1($xml, 'ASPECTOS DE SALUD', $narrativas['salud']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'HÁBITOS PERSONALES', $narrativas['habitos']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'ACTIVIDADES DELICTIVAS', $narrativas['drogas']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'ASPECTOS JUDICIALES', $narrativas['judicial']);
        $xml = self::rellenarNarrativaTablaFila1($xml, 'RECOMENDACIONES', $narrativas['recomendaciones']);
        $xml = self::rellenarInformacionComplementariaTabla($xml, $narrativas['informacion_complementaria']);
        $xml = self::rellenarPoligraficaTabla($xml, $narrativas['resultado_poligrafico']);
        $xml = self::rellenarConclusiones($xml, $narrativas['nombre_candidato']);
        $xml = self::rellenarApa($xml, $narrativas['poligrafista']);

        return $xml;
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

            foreach ($filasDatos as $indice => $filaDatos) {
                $indiceFila = $indice + 1;
                if (! isset($filas[$indiceFila])) {
                    continue;
                }

                $respuesta = self::texto($filaDatos['respuesta'] ?? '');
                if ($respuesta === '') {
                    $respuesta = '—';
                }

                $filas[$indiceFila] = InformeWordXml::establecerFila($filas[$indiceFila], [$respuesta], 1);
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    private static function rellenarPoligraficaTabla(string $xml, string $resultado): string
    {
        if ($resultado === '') {
            return $xml;
        }

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'Preguntas:', function (string $tabla) use ($resultado): string {
            $filas = InformeWordXml::filasTabla($tabla);

            for ($indice = 1; $indice <= 5; $indice++) {
                if (! isset($filas[$indice])) {
                    break;
                }

                $filas[$indice] = InformeWordXml::establecerFila($filas[$indice], [$resultado], 3);
            }

            return InformeWordXml::reconstruirTabla($tabla, $filas);
        });
    }

    private static function rellenarConclusiones(string $xml, string $nombreCandidato): string
    {
        if ($nombreCandidato === '' || $nombreCandidato === '—') {
            return $xml;
        }

        return InformeWordXml::reemplazarTexto($xml, 'XXXXXXXX', $nombreCandidato, 1);
    }

    private static function rellenarApa(string $xml, string $poligrafista): string
    {
        if ($poligrafista === '' || $poligrafista === '—') {
            return $xml;
        }

        return InformeWordXml::reemplazarTexto($xml, 'Stefanie9245 Rodrigo12871', $poligrafista, 1);
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
            ' LABORAL COMPLEMENTARIA',
            'INFORMACIÓN COMPLEMENTARIA Y ACTIVIDADES DE RIESGO',
            'ASPECTO ECONÓMICO',
            'ASPECTOS DE SALUD',
            'HÁBITOS PERSONALES',
            'ACTIVIDADES DELICTIVAS',
            'ASPECTOS JUDICIALES',
            'RECOMENDACIONES',
        ];

        foreach ($marcadoresTitulo as $marcador) {
            if (! str_contains($xml, $marcador)) {
                continue;
            }

            $xml = InformeWordXml::reemplazarTablaPorMarcador($xml, $marcador, function (string $tabla): string {
                return InformeWordXml::aplicarKeepNextFilaTitulo($tabla, 0);
            });
        }

        if ($variante === InformeWordPlantillas::VARIANTE_PERIODICA) {
            $xml = InformeWordXml::compactarEntreTablasPorMarcadores(
                $xml,
                'INFORMACIÓN LABORAL',
                ' LABORAL COMPLEMENTARIA'
            );
        }

        return $xml;
    }

    /** @param list<array{pregunta: string, respuesta: string}> $filas */
    private static function rellenarTablaLaborComplementaria(string $xml, array $filas): string
    {
        if ($filas === []) {
            return $xml;
        }

        $lineas = self::lineasPreguntasRespuestas($filas, numerar: true);

        return InformeWordXml::reemplazarTablaPorMarcador($xml, 'INFORMACIÓN LABORAL COMPLEMENTARIA', function (string $tabla) use ($lineas): string {
            $filasTabla = InformeWordXml::filasTabla($tabla);
            if (! isset($filasTabla[1])) {
                return $tabla;
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
            $raw = preg_replace('/[^\d.]/', '', (string) ($deuda[$campo] ?? '')) ?? '';
            if ($raw !== '' && is_numeric($raw)) {
                $total += (float) $raw;
            }
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
