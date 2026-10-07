<?php

namespace App\Mail;

use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso a la empresa cuando REPRO libera un resultado.
 * El preliminar y el informe final usan textos distintos para que el cliente
 * no confunda las dos etapas.
 */
class ResultadosDisponiblesMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Orden $orden;

    public bool $preliminar = false;

    public function __construct(Orden $orden, bool $preliminar = false)
    {
        $this->orden = $orden;
        $this->preliminar = $preliminar;
    }

    public function esPreliminar(): bool
    {
        return $this->preliminar ?? false;
    }

    public function envelope(): Envelope
    {
        $asunto = $this->esPreliminar()
            ? "REPRO - Resultado preliminar disponible: Orden {$this->orden->codigo_orden}"
            : "REPRO - Informe final disponible: Orden {$this->orden->codigo_orden}";

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.resultados-disponibles',
            with: [
                'orden' => $this->orden,
                'empresa' => $this->orden->empresa->nombre ?? 'N/A',
                'cantidadEvaluados' => $this->orden->evaluados->count(),
                'evaluados' => $this->orden->evaluados,
                'preliminar' => $this->esPreliminar(),
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
