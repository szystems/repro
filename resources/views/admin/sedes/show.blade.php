@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="page-title">
                <h5>Detalle de Sede</h5>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row gx-3 justify-content-center">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title">
                            <i class="bi bi-geo-alt"></i> {{ $sede->nombre }}
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('sedes.edit', $sede->id) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="{{ route('sedes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Nombre</label>
                                <div class="fw-semibold">{{ $sede->nombre }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small">Teléfono</label>
                                <div>{{ $sede->telefono ?? '—' }}</div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small">WhatsApp</label>
                                <div>
                                    @if($sede->whatsapp)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $sede->whatsapp) }}" target="_blank" class="text-success">
                                            <i class="bi bi-whatsapp"></i> {{ $sede->whatsapp }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small">Estado</label>
                                <div>
                                    @if($sede->estado)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-danger">Inactiva</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label text-muted small">Dirección</label>
                                <div>
                                    {{ $sede->direccion ?? '—' }}
                                    @if($sede->enlace_maps)
                                        <a href="{{ $sede->enlace_maps }}" target="_blank" class="ms-2 text-primary">
                                            <i class="bi bi-geo-alt-fill"></i> Ver en Maps
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label text-muted small">Capacidad máxima</label>
                                <div>
                                    <span class="badge bg-secondary fs-6">{{ $sede->capacidad }}</span>
                                    <small class="text-muted ms-1">evaluaciones simultáneas</small>
                                </div>
                            </div>
                            @if($sede->notas)
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted small">Notas internas</label>
                                <div class="border rounded p-2 bg-light">{{ $sede->notas }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-muted small">
                        Creada: {{ $sede->created_at->format('d/m/Y H:i') }} &nbsp;|&nbsp;
                        Evaluados asignados: <strong>{{ $sede->evaluados_count }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
