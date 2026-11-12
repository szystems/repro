<?php

namespace App\Support;

/**
 * Corrige solo el texto de la primera hoja. No consulta el formulario ni la ficha.
 */
class AsistenteRedaccionPreliminar
{
    public function __construct(private AsistenteRedaccionMotor $motor) {}

    /**
     * @return array{texto: string, html: string}
     */
    public function proponer(
        string $textoActual,
        string $modo,
        string $observacion,
        string $estilo,
        string $proveedor = 'claude'
    ): array {
        $plano = AsistenteRedaccionFormato::textoDesdeHtml($textoActual);
        $sistema = implode("\n", [
            'Corriges la redacción de la primera hoja del informe preliminar de REPRO Guatemala.',
            'Trabajas únicamente con el texto entregado. No uses datos del candidato, del formulario ni de otras secciones.',
            'No inventes hechos, nombres, fechas, montos ni resultados. No cambies el sentido de lo escrito.',
            'Los montos son quetzales. Consérvalos con Q, separador de miles y dos decimales, como Q3,000.00. No dejes un monto como número suelto.',
            'Devuelve solo la redacción corregida, sin título y sin explicar lo que hiciste.',
            'Separa los párrafos con un solo salto de línea. No dejes renglones vacíos.',
            'Puedes usar viñetas, cada una en su línea y empezando con «• », o numeración «1. », cuando el texto o la instrucción lo pidan.',
            AsistenteRedaccionInstrucciones::tonoHumano(),
        ]);
        $pedido = AsistenteRedaccionInstrucciones::pedido($modo, $estilo, false);
        $usuario = $pedido."\n\nTexto actual:\n".$plano;
        if ($observacion !== '') {
            $usuario .= "\n\nInstrucción:\n".$observacion;
        }

        $texto = AsistenteRedaccionFormato::limpiar(
            $this->motorDe($proveedor)->redactar($sistema, $usuario),
            'preliminar'
        );

        return [
            'texto' => $texto,
            'html' => AsistenteRedaccionFormato::html($texto),
        ];
    }

    private function motorDe(string $proveedor): AsistenteRedaccionMotor
    {
        if ($proveedor === 'gpt') {
            return app(AsistenteRedaccionOpenAi::class);
        }

        return $this->motor;
    }
}
