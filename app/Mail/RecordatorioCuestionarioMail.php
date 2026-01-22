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
 * Email de recordatorio enviado al evaluado cuando su cuestionario
 * está próximo a expirar y aún no lo ha completado.
 */
class RecordatorioCuestionarioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public EvaluadoOrden $evaluado;
    public string $urlCuestionario;
    public int $diasRestantes;

    /**
     * Create a new message instance.
     */
    public function __construct(EvaluadoOrden $evaluado, int $diasRestantes = 0)
    {
        $this->evaluado = $evaluado;
        $this->diasRestantes = $diasRestantes;
        $this->urlCuestionario = route('cuestionario.mostrar', ['token' => $evaluado->token_unico]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $urgencia = $this->diasRestantes <= 1 ? '⚠️ URGENTE: ' : '';
        
        return new Envelope(
            subject: $urgencia . 'REPRO - Recordatorio: Complete su cuestionario',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recordatorio-cuestionario',
            with: [
                'evaluado' => $this->evaluado,
                'urlCuestionario' => $this->urlCuestionario,
                'diasRestantes' => $this->diasRestantes,
                'fechaExpiracion' => $this->evaluado->token_expira_at?->format('d/m/Y H:i'),
                'empresa' => $this->evaluado->orden->empresa->nombre ?? 'N/A',
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
