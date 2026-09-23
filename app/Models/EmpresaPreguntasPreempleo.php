<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hasta cinco preguntas de preempleo, un segundo juego por puesto, y cinco de periódica. */
class EmpresaPreguntasPreempleo extends Model
{
    /** Nombre del primer juego cuando la empresa no escribe uno. */
    public const ETIQUETA_PRINCIPAL = 'Preguntas generales';

    protected $table = 'empresa_preguntas_preempleo';

    protected $fillable = [
        'empresa_id',
        'principal_nombre',
        'p1', 'p2', 'p3', 'p4', 'p5',
        'puesto_nombre',
        'puesto_p1', 'puesto_p2', 'puesto_p3', 'puesto_p4', 'puesto_p5',
        'periodica_p1', 'periodica_p2', 'periodica_p3', 'periodica_p4', 'periodica_p5',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * @param  array<int|string, mixed>  $principal
     * @param  array<int|string, mixed>  $puesto
     * @param  array<int|string, mixed>|null  $periodica  null = no tocar las de periódica ya guardadas
     */
    public static function guardarDesdeRequest(
        Empresa $empresa,
        array $principal,
        ?string $puestoNombre,
        array $puesto,
        ?string $principalNombre = null,
        ?array $periodica = null
    ): void {
        $principales = self::cinco($principal);
        $dePuesto = self::cinco($puesto);
        $dePeriodica = $periodica === null ? null : self::cinco($periodica);
        $nombre = trim((string) $puestoNombre);
        $nombrePrincipal = trim((string) $principalNombre);
        $existente = static::where('empresa_id', $empresa->id)->first();

        $sinPrincipal = self::vacias($principales);
        $sinPuesto = $nombre === '' && self::vacias($dePuesto);
        $sinPeriodica = $dePeriodica === null
            ? ($existente === null || self::vacias($existente->espaciosPeriodica()))
            : self::vacias($dePeriodica);

        if ($sinPrincipal && $sinPuesto && $sinPeriodica) {
            static::where('empresa_id', $empresa->id)->delete();

            return;
        }

        $datos = [
            'principal_nombre' => ($nombrePrincipal !== '' && ! $sinPrincipal) ? mb_substr($nombrePrincipal, 0, 100) : null,
            'p1' => $principales[0],
            'p2' => $principales[1],
            'p3' => $principales[2],
            'p4' => $principales[3],
            'p5' => $principales[4],
            'puesto_nombre' => $nombre !== '' ? mb_substr($nombre, 0, 100) : null,
            'puesto_p1' => $dePuesto[0],
            'puesto_p2' => $dePuesto[1],
            'puesto_p3' => $dePuesto[2],
            'puesto_p4' => $dePuesto[3],
            'puesto_p5' => $dePuesto[4],
        ];
        if ($dePeriodica !== null) {
            $datos['periodica_p1'] = $dePeriodica[0];
            $datos['periodica_p2'] = $dePeriodica[1];
            $datos['periodica_p3'] = $dePeriodica[2];
            $datos['periodica_p4'] = $dePeriodica[3];
            $datos['periodica_p5'] = $dePeriodica[4];
        }

        static::updateOrCreate(['empresa_id' => $empresa->id], $datos);
    }

    /** Nombre que ve quien arma la orden. Vacío = Preguntas generales. */
    public function nombrePrincipalVisible(): string
    {
        $nombre = trim((string) $this->principal_nombre);

        return $nombre !== '' ? $nombre : self::ETIQUETA_PRINCIPAL;
    }

    /** @return list<string> */
    public function espaciosPrincipales(): array
    {
        return $this->espacios(['p1', 'p2', 'p3', 'p4', 'p5']);
    }

    /** @return list<string> */
    public function espaciosPuesto(): array
    {
        return $this->espacios(['puesto_p1', 'puesto_p2', 'puesto_p3', 'puesto_p4', 'puesto_p5']);
    }

    /** @return list<string> */
    public function espaciosPeriodica(): array
    {
        return $this->espacios(['periodica_p1', 'periodica_p2', 'periodica_p3', 'periodica_p4', 'periodica_p5']);
    }

    /** @return list<string> */
    public function preguntasPrincipales(): array
    {
        return array_values(array_filter($this->espaciosPrincipales(), fn (string $texto) => $texto !== ''));
    }

    /** @return list<string> */
    public function preguntasPuesto(): array
    {
        if ($this->nombrePuestoVisible() === null) {
            return [];
        }

        return array_values(array_filter($this->espaciosPuesto(), fn (string $texto) => $texto !== ''));
    }

    /** @return list<string> */
    public function preguntasPeriodica(): array
    {
        return array_values(array_filter($this->espaciosPeriodica(), fn (string $texto) => $texto !== ''));
    }

    public function nombrePuestoVisible(): ?string
    {
        $nombre = trim((string) $this->puesto_nombre);
        $hayPregunta = array_filter($this->espaciosPuesto(), fn (string $texto) => $texto !== '') !== [];

        if ($nombre === '' || ! $hayPregunta) {
            return null;
        }

        return $nombre;
    }

    /**
     * @param  array<int|string, mixed>  $valores
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string, 4: ?string}
     */
    private static function cinco(array $valores): array
    {
        $salida = [];
        for ($i = 0; $i < 5; $i++) {
            $texto = trim((string) ($valores[$i] ?? ''));
            $salida[] = $texto === '' ? null : mb_substr($texto, 0, 500);
        }

        return $salida;
    }

    /** @param  list<?string>  $valores */
    private static function vacias(array $valores): bool
    {
        foreach ($valores as $valor) {
            if ($valor !== null && $valor !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $columnas
     * @return list<string>
     */
    private function espacios(array $columnas): array
    {
        $salida = [];
        foreach ($columnas as $columna) {
            $salida[] = trim((string) ($this->{$columna} ?? ''));
        }

        return $salida;
    }
}
