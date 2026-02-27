@extends('layouts.admin')
@section('content')

<div class="content-wrapper-scroll">

    <div class="main-header d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center justify-content-center">
            <div class="page-icon">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="page-title">
                <h5>Nueva Sede</h5>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="row gx-3 justify-content-center">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="bi bi-plus-circle"></i> Registrar Nueva Sede</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sedes.store') }}" method="POST">
                            @csrf
                            @include('admin.sedes._form')
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Guardar Sede
                                </button>
                                <a href="{{ route('sedes.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
