<?php

namespace App\Mail;

use App\Models\EvaluadoOrden;
use App\Support\CitaProgramadaContenido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaProgramadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EvaluadoOrden $evaluado,
        public bool $reprogramada = false
    ) {
        $this->evaluado->loadMissing(['orden.empresa', 'sede']);
    }

    public function envelope(): Envelope
    {
        $asunto = $this->reprogramada
            ? 'REPRO - Su cita ha sido reprogramada'
            : 'REPRO - Cita de evaluación programada';

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        $inicio = $this->evaluado->fecha_programada;
        $fin = $this->evaluado->fecha_hora_fin;
        $plantilla = CitaProgramadaContenido::plantilla($this->evaluado);

        return new Content(
            view: 'emails.cita-programada',
            with: [
                'evaluado' => $this->evaluado,
                'reprogramada' => $this->reprogramada,
                'plantilla' => $plantilla,
                'tituloServicio' => CitaProgramadaContenido::tituloServicio($this->evaluado),
                'esVirtual' => CitaProgramadaContenido::esVirtual($this->evaluado),
                'mostrarSede' => CitaProgramadaContenido::mostrarSedeYDireccion($this->evaluado),
                'empresa' => $this->evaluado->orden->empresa->nombre ?? 'N/A',
                'puesto' => trim((string) ($this->evaluado->puesto_evaluar ?? '')) ?: 'N/A',
                'fecha' => $inicio?->format('d/m/Y'),
                'horaInicio' => $inicio?->format('H:i'),
                'horaFin' => $fin?->format('H:i'),
                'sede' => $this->evaluado->sede->nombre ?? 'N/A',
                'direccion' => $this->evaluado->sede->direccion ?? 'N/A',
                'enlaceMaps' => $this->evaluado->sede->enlace_maps ?? null,
                'modalidad' => CitaProgramadaContenido::etiquetaModalidad($this->evaluado),
                'urlCuestionario' => CitaProgramadaContenido::urlCuestionario($this->evaluado),
                'whatsappUrl' => CitaProgramadaContenido::whatsappUrl($this->evaluado),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
