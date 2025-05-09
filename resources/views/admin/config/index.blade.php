@extends('layouts.admin')

@section('content')
    <!-- Content wrapper scroll start -->
    <div class="content-wrapper-scroll">
        <!-- Main header starts -->
        <div class="main-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center justify-content-center">
                <div class="page-icon">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <div class="page-title">
                    <h5>Configuración del Sistema</h5>
                </div>
            </div>
            <!-- Date range start -->
            <div class="d-flex align-items-end d-none d-sm-block">
                <h6 class="float-end text-light" id="reloj"></h6>
            </div>
        </div>
        <!-- Main header ends -->

        <!-- Content wrapper start -->
        <div class="content-wrapper">
            <!-- Row start -->
            <div class="row gx-3">
                <div class="col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-sliders"></i> Ajustes Generales
                                </div>
                                @if(session('status'))
                                    <div class="alert alert-success py-2 px-3 mb-0">
                                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="custom-tabs-container">
                                <ul class="nav nav-tabs nav-tabs-v2" id="configTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="tab-general" data-bs-toggle="tab" href="#general" role="tab"
                                            aria-controls="general" aria-selected="true">
                                            <i class="bi bi-gear me-1"></i> General
                                        </a>
                                    </li>
                                    <!-- Las pestañas de Apariencia y Negocio están ocultas temporalmente
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="tab-appearance" data-bs-toggle="tab" href="#appearance" role="tab"
                                            aria-controls="appearance" aria-selected="false">
                                            <i class="bi bi-palette me-1"></i> Apariencia
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="tab-business" data-bs-toggle="tab" href="#business" role="tab"
                                            aria-controls="business" aria-selected="false">
                                            <i class="bi bi-building me-1"></i> Negocio
                                        </a>
                                    </li>
                                    -->
                                </ul>

                                <div class="tab-content" id="configTabContent">
                                    <!-- Pestaña de Configuración General -->
                                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                                        <div class="p-3">
                                            <p class="text-muted mb-4">Configure los ajustes generales del sistema. Estos parámetros afectan el funcionamiento de todas las áreas de la aplicación.</p>

                                            @if (count($errors)>0)
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <ul class="mb-0">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{$error}}</li>
                                                        @endforeach
                                                    </ul>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            @endif

                                            <form id="configForm" action="{{ url('update-config') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')

                                                <!-- Sección de Moneda y Finanzas -->
                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-currency-exchange me-1"></i> Moneda y Finanzas</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="currency" class="form-label">Moneda del Sistema</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                                                                    <select name="currency" id="currency" class="form-select">
                                                                        <option value="{{ $config->currency }}" selected>{{ $config->currency }}</option>
                                                                        <option value="USD $">Dólares (USD $)</option>
                                                                        <option value="GTQ Q">Quetzales (GTQ Q)</option>
                                                                        <option value="EUR €">Euros (EUR €)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-text">Establece la moneda principal para reportes y transacciones</div>
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label for="impuesto" class="form-label">Porcentaje de Impuesto</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                                                    <input name="impuesto" id="impuesto" type="number" class="form-control" min="0" max="100" step="0.01" placeholder="Ej: 12.50" value="{{ $config->impuesto }}">
                                                                    <span class="input-group-text bg-light">%</span>
                                                                </div>
                                                                <div class="form-text">IVA u otros impuestos aplicados en ventas</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Sección de Identidad Visual -->
                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-image me-1"></i> Identidad Visual</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-6 mb-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Logo Actual</label>
                                                                    <div class="text-center p-3 bg-light rounded">
                                                                        @if ($config->logo)
                                                                            <img id="logoPreview" src="{{ asset('assets/imgs/logos/'.$config->logo) }}" class="img-fluid" style="max-height: 150px;" alt="Logo" />
                                                                        @else
                                                                            <div class="text-muted p-3">
                                                                                <i class="bi bi-image fs-1"></i>
                                                                                <p>No hay logo configurado</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Cambiar Logo</label>
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text"><i class="bi bi-upload"></i></span>
                                                                    <input type="file" id="logoFile" name="logo" class="form-control" accept="image/*">
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="bi bi-info-circle me-1"></i> Formato recomendado: PNG o JPG con fondo transparente. Tamaño máximo: 2MB.
                                                                </div>
                                                                @if ($errors->has('logo'))
                                                                    <div class="text-danger mt-2">{{ $errors->first('logo') }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Sección de Información del Negocio (temporalmente oculta)
                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-building me-1"></i> Información del Negocio</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="nombre_negocio" class="form-label">Nombre del Negocio</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-shop"></i></span>
                                                                    <input name="nombre_negocio" type="text" class="form-control" placeholder="Nombre de su empresa" value="{{ $config->nombre_negocio }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label for="telefono" class="form-label">Teléfono</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                                                    <input name="telefono" type="text" class="form-control" placeholder="Número de contacto" value="{{ $config->telefono }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label for="direccion" class="form-label">Dirección</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                                                    <textarea name="direccion" class="form-control" rows="2" placeholder="Dirección completa del negocio">{{ $config->direccion }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                -->

                                                <div class="d-flex gap-2 justify-content-center mt-4">
                                                    <button type="reset" class="btn btn-outline-secondary">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restablecer
                                                    </button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-save"></i> Guardar Configuración
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Contenidos de pestañas ocultas (temporalmente comentados)
                                    <div class="tab-pane fade" id="appearance" role="tabpanel">
                                        <div class="p-3">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                La configuración de apariencia estará disponible en una actualización futura.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="business" role="tabpanel">
                                        <div class="p-3">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                La configuración extendida de información de negocio estará disponible en una actualización futura.
                                            </div>
                                        </div>
                                    </div>
                                    -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row end -->
        </div>
        <!-- Content wrapper end -->
    </div>
    <!-- Content wrapper scroll end -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Previsualización de logo al seleccionarlo
            $("#logoFile").change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $("#logoPreview").attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Validación del formulario
            $("#configForm").submit(function(e) {
                // Verificar impuesto
                var impuesto = $("#impuesto").val();
                if (impuesto < 0 || impuesto > 100) {
                    e.preventDefault();
                    alert("El porcentaje de impuesto debe estar entre 0 y 100");
                    return false;
                }

                // Verificar tamaño de archivo de logo
                var logoFile = $("#logoFile")[0];
                if (logoFile.files.length > 0) {
                    if (logoFile.files[0].size > 2 * 1024 * 1024) { // 2MB
                        e.preventDefault();
                        alert("El archivo de logo no debe exceder 2MB");
                        return false;
                    }
                }

                return true;
            });
        });
    </script>
    @endpush
@endsection
