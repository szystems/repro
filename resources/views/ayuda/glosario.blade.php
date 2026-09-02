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
                        <li class="breadcrumb-item active">Glosario</li>
                    </ol>
                </nav>
                <h3 class="page-title"><i class="bi bi-journal-text me-2"></i>Glosario</h3>
                <p class="text-muted">Términos y estados usados en REPRO.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped mb-0 align-middle">
                        <thead><tr><th style="width:32%">Término</th><th>Definición</th></tr></thead>
                        <tbody>
                            @foreach($terminos as $t)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="ayuda-glosario-icon"><i class="bi {{ $t['icono'] ?? 'bi-bookmark' }}"></i></span>
                                        <div>
                                            <strong>{{ $t['termino'] }}</strong>
                                            @if(!empty($t['articulo']))
                                            <div class="mt-1">
                                                <a href="{{ route('ayuda.show', $t['articulo']) }}" class="small">
                                                    <i class="bi bi-arrow-right-circle me-1"></i>Ver en ayuda
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $t['definicion'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
