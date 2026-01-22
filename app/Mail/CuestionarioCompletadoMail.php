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
 * Email enviado a los administradores/empresa cuando un evaluado
 * completa su cuestionario.
 */
class CuestionarioCompletadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public EvaluadoOrden $evaluado;

    /**
     * Create a new message instance.
     */
    public function __construct(EvaluadoOrden $evaluado)
    {
        $this->evaluado = $evaluado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $nombreEvaluado = $this->evaluado->nombre . ' ' . $this->evaluado->apellidos;
        
        return new Envelope(
            subject: "REPRO - Cuestionario completado: {$nombreEvaluado}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cuestionario-completado',
            with: [
                'evaluado' => $this->evaluado,
                'orden' => $this->evaluado->orden,
                'empresa' => $this->evaluado->orden->empresa->nombre ?? 'N/A',
                'fechaCompletado' => $this->evaluado->completado_at?->format('d/m/Y H:i'),
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
