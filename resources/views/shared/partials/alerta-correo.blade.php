@if(!empty($correoAlerta['mensaje'] ?? null))
<div class="alert alert-{{ $correoAlerta['nivel'] === 'danger' ? 'danger' : 'warning' }} alert-dismissible fade show mx-3 mt-3 mb-0" role="alert">
    <i class="bi bi-envelope-exclamation me-2"></i>{{ $correoAlerta['mensaje'] }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
