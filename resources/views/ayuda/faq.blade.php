@extends(session('layout', 'layouts.admin'))

@push('styles')
@include('ayuda.partials.styles')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('ayuda.index') }}">Centro de Ayuda</a></li>
                        <li class="breadcrumb-item active">Preguntas frecuentes</li>
                    </ol>
                </nav>
                <h3 class="page-title"><i class="bi bi-question-circle me-2"></i>Preguntas frecuentes</h3>
                <p class="text-muted mb-0">Respuestas rápidas con enlace a la guía completa cuando aplique.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
                @foreach($preguntas as $i => $item)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq-{{ $i }}">
                            @if(!empty($item['icono']))
                                <i class="bi {{ $item['icono'] }} me-2 text-primary"></i>
                            @endif
                            {{ $item['pregunta'] }}
                        </button>
                    </h2>
                    <div id="faq-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                         data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            {!! nl2br(e($item['respuesta'])) !!}
                            @if(!empty($item['articulo_data']))
                            <div class="mt-3 pt-2 border-top">
                                <a href="{{ route('ayuda.show', $item['articulo_data']['slug']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-book me-1"></i>Ver guía completa: {{ $item['articulo_data']['titulo'] }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
