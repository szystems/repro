@if(!empty($src))
<figure class="ayuda-screenshot mb-4">
    <div class="ayuda-screenshot-frame">
        <img src="{{ asset('assets/ayuda/screens/' . $src) }}"
             alt="{{ $alt ?? 'Captura de pantalla REPRO' }}"
             class="img-fluid rounded"
             loading="lazy">
        @if(!empty($pins))
        <div class="ayuda-screenshot-pins">
            @foreach($pins as $pin)
            <span class="ayuda-screenshot-pin" style="top:{{ $pin['top'] ?? '50%' }};left:{{ $pin['left'] ?? '50%' }}">{{ $pin['num'] ?? '①' }}</span>
            @endforeach
        </div>
        @endif
    </div>
    @if(!empty($caption))
    <figcaption class="small text-muted mt-2 text-center">{{ $caption }}</figcaption>
    @endif
</figure>
@endif
