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

                {{-- Panel de procesos --}}
                <div class="row gx-3 mt-3">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card text-center border-primary h-100">
                            <div class="card-body py-3">
                                <div class="fs-2 fw-bold text-primary">{{ $stats['actuales'] }}</div>
                                <div class="small text-muted">Procesos actuales</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card text-center border-success h-100">
                            <div class="card-body py-3">
                                <div class="fs-2 fw-bold text-success">{{ $stats['realizados'] }}</div>
                                <div class="small text-muted">Realizados</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card text-center border-warning h-100">
                            <div class="card-body py-3">
                                <div class="fs-2 fw-bold text-warning">{{ $stats['pendientes'] }}</div>
                                <div class="small text-muted">Pendientes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card text-center border-secondary h-100">
                            <div class="card-body py-3">
                                <div class="fs-2 fw-bold text-secondary">{{ $stats['total'] }}</div>
                                <div class="small text-muted">Total candidatos</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Búsqueda y tabla de candidatos --}}
                <div class="card mt-1">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="card-title mb-0"><i class="bi bi-people"></i> Candidatos en esta sede</span>
                    </div>
                    <div class="card-body pb-2">
                        <form method="GET" action="{{ route('sedes.show', $sede->id) }}" class="d-flex gap-2 mb-3">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Buscar por nombre o DPI…"
                                   value="{{ $search }}">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            @if($search)
                                <a href="{{ route('sedes.show', $sede->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x"></i>
                                </a>
                            @endif
                        </form>

                        @if($candidatos->isEmpty())
                            <p class="text-muted text-center py-3">No se encontraron candidatos.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Candidato</th>
                                            <th>DPI</th>
                                            <th>Empresa</th>
                                            <th>Servicio</th>
                                            <th>Estado proceso</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($candidatos as $candidato)
                                        <tr>
                                            <td>{{ $candidato->nombre }} {{ $candidato->apellidos }}</td>
                                            <td class="text-muted small">{{ $candidato->dpi ?? '—' }}</td>
                                            <td class="small">{{ $candidato->orden->empresa->nombre ?? '—' }}</td>
                                            <td class="small">{{ ucfirst($candidato->tipo_servicio ?? '—') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $candidato->orden->estado_color }}">
                                                    {{ $candidato->orden->estado_human }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('ordenes.show', $candidato->orden_id) }}"
                                                   class="btn btn-xs btn-outline-secondary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $candidatos->links() }}
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
