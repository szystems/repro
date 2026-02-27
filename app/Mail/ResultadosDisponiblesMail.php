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
 * Email enviado a la empresa cuando los resultados de una orden
 * son marcados como visibles por REPRO.
 */
class ResultadosDisponiblesMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Orden $orden;

    /**
     * Create a new message instance.
     */
    public function __construct(Orden $orden)
    {
        $this->orden = $orden;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "REPRO - Resultados disponibles: Orden {$this->orden->codigo_orden}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.resultados-disponibles',
            with: [
                'orden' => $this->orden,
                'empresa' => $this->orden->empresa->nombre ?? 'N/A',
                'cantidadEvaluados' => $this->orden->evaluados->count(),
                'evaluados' => $this->orden->evaluados,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
