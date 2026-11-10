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
        string $estilo,
        string $proveedor = 'claude'
    ): array {
        $conObservacion = $modo === 'observaciones';
        $paquete = AsistenteRedaccionSecciones::paquete(
            $evaluado,
            $seccion,
            $conObservacion ? $observacion : ''
        );
        $sistema = AsistenteRedaccionInstrucciones::sistema($seccion, (string) ($paquete['servicio'] ?? ''));
        $pedido = AsistenteRedaccionInstrucciones::pedido($modo, $estilo, $conObservacion && $restoSinCambios);
        $usuario = $this->mensaje($modo, $pedido, $observacion, $textoActual, (string) $paquete['texto_para_modelo']);

        return [
            'texto' => AsistenteRedaccionFormato::limpiar(
                $this->motorDe($proveedor)->redactar($sistema, $usuario),
                $seccion
            ),
            'avisos' => AsistenteRedaccionAvisos::de($paquete, $conObservacion && $restoSinCambios),
        ];
    }

    private function motorDe(string $proveedor): AsistenteRedaccionMotor
    {
        if ($proveedor === 'gpt') {
            return app(AsistenteRedaccionOpenAi::class);
        }

        return $this->motor;
    }

    private function mensaje(string $modo, string $pedido, string $observacion, string $textoActual, string $formulario): string
    {
        $soloTexto = in_array($modo, ['reescribir', 'otra_version', 'cambiar_estilo', 'instruccion'], true)
            || ($modo === 'observaciones' && $textoActual !== '');

        if ($soloTexto) {
            $mensaje = $pedido."\n\nTexto actual:\n".$textoActual;
            if ($observacion !== '') {
                $mensaje .= "\n\nInstrucción:\n".$observacion;
            }

            return $mensaje;
        }

        if ($modo === 'confirmada' && $textoActual !== '') {
            return $pedido."\n\nTexto ya escrito en la sección. Consérvalo como base:\n".$textoActual."\n\n".$formulario;
        }

        return $pedido."\n\n".$formulario;
    }
}
