<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

class AsistenteRedaccionGenerador
{
    public function __construct(private AsistenteRedaccionMotor $motor) {}

    /**
     * @return array{texto: string, avisos: list<string>}
     */
    public function proponer(
        EvaluadoOrden $evaluado,
        string $seccion,
        string $modo,
        string $observacion,
        bool $restoSinCambios,
        string $textoActual,
        string $estilo
    ): array {
        $conObservacion = $modo === 'observaciones';
        $paquete = AsistenteRedaccionSecciones::paquete(
            $evaluado,
            $seccion,
            $conObservacion ? $observacion : ''
        );
        $sistema = AsistenteRedaccionInstrucciones::sistema($seccion, (string) ($paquete['servicio'] ?? ''));
        $pedido = AsistenteRedaccionInstrucciones::pedido($modo, $estilo, $conObservacion && $restoSinCambios);
        $usuario = $pedido."\n\n".$paquete['texto_para_modelo'];

        if (in_array($modo, ['reescribir', 'otra_version', 'cambiar_estilo'], true)) {
            $usuario .= "\n\nTexto a trabajar:\n".$textoActual;
        }

        return [
            'texto' => $this->motor->redactar($sistema, $usuario),
            'avisos' => AsistenteRedaccionAvisos::de($paquete, $conObservacion && $restoSinCambios),
        ];
    }
}
