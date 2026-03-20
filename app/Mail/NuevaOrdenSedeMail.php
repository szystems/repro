<?php

namespace App\Mail;

use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaOrdenSedeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Orden $orden;

    public function __construct(Orden $orden)
    {
        $this->orden = $orden;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'REPRO - Nueva orden asignada a su sede',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva-orden-sede',
            with: [
                'orden' => $this->orden,
                'empresa' => $this->orden->empresa,
                'sede' => $this->orden->sede,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
