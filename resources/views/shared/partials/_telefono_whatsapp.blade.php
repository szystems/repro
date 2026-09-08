@php
    $numeroWa = $numero ?? null;
    $urlWa = \App\Support\WhatsAppLink::url($numeroWa);
    $etiqueta = $etiqueta ?? null;
@endphp
@if($numeroWa)
    @if($etiqueta)
        <span class="text-muted">{{ $etiqueta }}:</span>
    @endif
    <i class="bi bi-telephone"></i> {{ $numeroWa }}
    @if($urlWa)
        <a href="{{ $urlWa }}" target="_blank" rel="noopener noreferrer" class="text-success" title="Abrir WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </a>
    @endif
@endif
