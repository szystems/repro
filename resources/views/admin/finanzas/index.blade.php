@extends('layouts.admin')

@section('content')
    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">
        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="page-title">
                    <h5>Finanzas</h5>
                </div>
            </div>
            <div class="d-flex align-items-end d-none d-sm-block">
                <h6 class="float-end text-light" id="reloj"></h6>
            </div>
        </div>
        <!-- Main header ends -->

        <!-- Content wrapper start -->
        <div class="content-wrapper">
            <div class="row gx-3">
                <div class="col-sm-12 col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-cash-stack text-muted" style="font-size: 5rem;"></i>
                            <h3 class="mt-4">Módulo de Finanzas</h3>
                            <p class="text-muted fs-5 mb-4">Este módulo está en desarrollo y estará disponible próximamente.</p>
                            <span class="badge bg-secondary fs-5 px-4 py-2">Próximamente</span>
                            <div class="mt-4">
                                <p class="text-muted small">Aquí podrás gestionar facturación, pagos, reportes financieros y más.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content wrapper end -->
    </div>
    <!-- Content wrapper scroll end -->
@endsection
