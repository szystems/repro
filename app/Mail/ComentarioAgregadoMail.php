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
 * Aviso extra, apagado por defecto: la empresa pidió enterarse de cada comentario nuevo.
 */
class ComentarioAgregadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public EvaluadoOrden $evaluado, public string $comentario)
    {
        $this->evaluado->loadMissing('orden.empresa');
    }

    public function envelope(): Envelope
    {
        $orden = $this->evaluado->orden;
        $codigo = $orden->codigo_orden ?? $orden->id ?? $this->evaluado->orden_id;

        return new Envelope(
            subject: 'REPRO - Nuevo comentario: '.$this->evaluado->nombre_completo.' — Orden '.$codigo,
        );
    }

    public function content(): Content
    {
        $orden = $this->evaluado->orden;

        return new Content(
            view: 'emails.comentario-agregado',
            with: [
                'evaluado' => $this->evaluado,
                'comentario' => $this->comentario,
                'orden' => $orden,
                'urlOrden' => $orden ? route('empresa.ordenes.show', $orden) : url('/login'),
            ],
        );
    }
}
