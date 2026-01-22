<?php

namespace App\Mail;

use App\Models\EvaluadoOrden;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email enviado al evaluado cuando es asignado a una orden.
 * Incluye el enlace único para acceder al cuestionario.
 */
class EvaluadoAsignadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public EvaluadoOrden $evaluado;
    public string $urlCuestionario;

    /**
     * Create a new message instance.
     */
    public function __construct(EvaluadoOrden $evaluado)
    {
        $this->evaluado = $evaluado;
        $this->urlCuestionario = route('cuestionario.mostrar', ['token' => $evaluado->token_unico]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'REPRO - Ha sido asignado para evaluación',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.evaluado-asignado',
            with: [
                'evaluado' => $this->evaluado,
                'urlCuestionario' => $this->urlCuestionario,
                'empresa' => $this->evaluado->orden->empresa->nombre ?? 'N/A',
                'fechaExpiracion' => $this->evaluado->token_expira_at?->format('d/m/Y H:i'),
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
