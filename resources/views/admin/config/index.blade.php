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

                            @if (count($errors) > 0)
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="custom-tabs-container">
                                <ul class="nav nav-tabs nav-tabs-v2" id="configTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="tab-identidad" data-bs-toggle="tab" href="#identidad" role="tab"
                                            aria-controls="identidad" aria-selected="true">
                                            <i class="bi bi-building me-1"></i> Identidad
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="tab-catalogos" data-bs-toggle="tab" href="#catalogos" role="tab"
                                            aria-controls="catalogos" aria-selected="false">
                                            <i class="bi bi-list-ul me-1"></i> Catálogos
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="tab-plantillas" data-bs-toggle="tab" href="#plantillas" role="tab"
                                            aria-controls="plantillas" aria-selected="false">
                                            <i class="bi bi-file-earmark-text me-1"></i> Plantillas
                                        </a>
                                    </li>
                                </ul>

                                <form id="configForm" action="{{ url('update-config') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="tab-content" id="configTabContent">

                                        <!-- ===== TAB: IDENTIDAD ===== -->
                                        <div class="tab-pane fade show active" id="identidad" role="tabpanel">
                                            <div class="p-3">
                                                <p class="text-muted mb-4">Información visual y datos de contacto del sistema.</p>

                                                <!-- Identidad de la empresa -->
                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-building me-1"></i> Identidad de la Empresa</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-8 mb-3">
                                                                <label for="nombre_empresa" class="form-label">Nombre Comercial</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                                    <input name="nombre_empresa" id="nombre_empresa" type="text" class="form-control" placeholder="Ej: REPRO Guatemala" maxlength="100" value="{{ $config->nombre_empresa }}">
                                                                </div>
                                                                <div class="form-text">Aparece en el pie de página de PDFs generados.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Logo -->
                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-image me-1"></i> Logo del Sistema</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Logo Actual</label>
                                                                <div class="text-center p-3 bg-light rounded">
                                                                    @if ($config->logo)
                                                                        <img id="logoPreview" src="{{ asset('assets/imgs/logos/'.$config->logo) }}" class="img-fluid" style="max-height: 150px;" alt="Logo" />
                                                                    @else
                                                                        <div class="text-muted p-3" id="logoPlaceholder">
                                                                            <i class="bi bi-image fs-1"></i>
                                                                            <p>No hay logo configurado</p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Cambiar Logo</label>
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text"><i class="bi bi-upload"></i></span>
                                                                    <input type="file" id="logoFile" name="logo" class="form-control" accept="image/*">
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="bi bi-info-circle me-1"></i> PNG o JPG con fondo transparente. Máx. 2MB.
                                                                </div>
                                                                @if ($errors->has('logo'))
                                                                    <div class="text-danger mt-2">{{ $errors->first('logo') }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Contacto y Redes -->
                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-envelope me-1"></i> Contacto y Redes Sociales</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="email" class="form-label">Correo Electrónico</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                                    <input name="email" id="email" type="email" class="form-control" placeholder="correo@empresa.com" value="{{ $config->email }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="wapp_link" class="form-label">WhatsApp</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                                                    <input name="wapp_link" id="wapp_link" type="url" class="form-control" placeholder="https://wa.me/502..." value="{{ $config->wapp_link }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="fb_link" class="form-label">Facebook</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                                                                    <input name="fb_link" id="fb_link" type="url" class="form-control" placeholder="https://facebook.com/..." value="{{ $config->fb_link }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="inst_link" class="form-label">Instagram</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                                                                    <input name="inst_link" id="inst_link" type="url" class="form-control" placeholder="https://instagram.com/..." value="{{ $config->inst_link }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="yt_link" class="form-label">YouTube</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-youtube"></i></span>
                                                                    <input name="yt_link" id="yt_link" type="url" class="form-control" placeholder="https://youtube.com/..." value="{{ $config->yt_link }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2 justify-content-end mt-2">
                                                    <button type="reset" class="btn btn-outline-secondary">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restablecer
                                                    </button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-save"></i> Guardar Cambios
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ===== TAB: CATÁLOGOS ===== -->
                                        <div class="tab-pane fade" id="catalogos" role="tabpanel">
                                            <div class="p-3">
                                                <p class="text-muted mb-4">Valores de referencia utilizados en el sistema: moneda e impuestos.</p>

                                                <div class="card mb-4 border">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0"><i class="bi bi-currency-exchange me-1"></i> Moneda e Impuestos</h6>
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
                                                                <div class="form-text">Moneda principal para reportes y transacciones.</div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="impuesto" class="form-label">Porcentaje de Impuesto (%)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                                                    <input name="impuesto" id="impuesto" type="number" class="form-control" min="0" max="100" step="0.01" placeholder="Ej: 12.50" value="{{ $config->impuesto }}">
                                                                    <span class="input-group-text bg-light">%</span>
                                                                </div>
                                                                <div class="form-text">IVA u otros impuestos aplicados en ventas.</div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="descuento_maximo" class="form-label">Descuento Máximo Permitido (%)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                                    <input name="descuento_maximo" id="descuento_maximo" type="number" class="form-control" min="0" max="100" step="0.01" placeholder="Ej: 20.00" value="{{ $config->descuento_maximo }}">
                                                                    <span class="input-group-text bg-light">%</span>
                                                                </div>
                                                                <div class="form-text">Porcentaje máximo de descuento aplicable.</div>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="dias_vigencia_token" class="form-label">Vigencia del Link de Formulario (días)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                                                    <input name="dias_vigencia_token" id="dias_vigencia_token" type="number" class="form-control" min="1" max="365" placeholder="30" value="{{ $config->dias_vigencia_token ?? 30 }}">
                                                                    <span class="input-group-text bg-light">días</span>
                                                                </div>
                                                                <div class="form-text">Días que tiene el candidato para completar el formulario antes de que el link expire.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2 justify-content-end mt-2">
                                                    <button type="reset" class="btn btn-outline-secondary">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restablecer
                                                    </button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-save"></i> Guardar Cambios
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ===== TAB: PLANTILLAS ===== -->
                                            <div class="p-3">
                                                <div class="text-center py-5">
                                                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 4rem;"></i>
                                                    <h5 class="mt-3 text-muted">Plantillas de Documentos y Correos</h5>
                                                    <p class="text-muted">La configuración de plantillas estará disponible en una próxima actualización.</p>
                                                    <span class="badge bg-secondary fs-6 px-3 py-2">Próximamente</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content wrapper end -->
    </div>
    <!-- Content wrapper scroll end -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            $("#logoFile").change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        if ($("#logoPreview").length) {
                            $("#logoPreview").attr('src', e.target.result);
                        } else {
                            $("#logoPlaceholder").html('<img id="logoPreview" src="' + e.target.result + '" class="img-fluid" style="max-height: 150px;" alt="Logo" />');
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            $("#configForm").submit(function(e) {
                var impuesto = parseFloat($("#impuesto").val());
                if (!isNaN(impuesto) && (impuesto < 0 || impuesto > 100)) {
                    e.preventDefault();
                    alert("El porcentaje de impuesto debe estar entre 0 y 100");
                    return false;
                }
                var logoFile = $("#logoFile")[0];
                if (logoFile.files.length > 0 && logoFile.files[0].size > 2 * 1024 * 1024) {
                    e.preventDefault();
                    alert("El archivo de logo no debe exceder 2MB");
                    return false;
                }
                return true;
            });
        });
    </script>
    @endpush
@endsection
