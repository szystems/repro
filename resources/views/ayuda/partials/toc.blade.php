@if(!empty($secciones))
<nav class="ayuda-toc card mb-3" aria-label="Índice del artículo">
    <div class="card-header bg-transparent py-2">
        <h6 class="mb-0"><i class="bi bi-list-ul me-1"></i> En este artículo</h6>
    </div>
    <div class="card-body py-2">
        <ul class="nav flex-column ayuda-toc-list">
            @foreach($secciones as $sec)
            <li class="nav-item">
                <a class="nav-link py-1 px-0" href="#{{ $sec['id'] }}">
                    @if(!empty($sec['icono']))
                        <i class="bi {{ $sec['icono'] }} me-1 text-primary"></i>
                    @endif
                    {{ $sec['titulo'] }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</nav>
@endif
