<!-- Row start -->
<div class="row gx-3">
    <div class="col-xl-12">
        <div class="card card-background-mask-info">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-search"></i> Filtros de búsqueda</div>
            </div>
            <div class="card-body">
                <form action="{{ url('users') }}" method="GET">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label for="exampleDataList" class="form-label">Buscar usuario por nombre, email o teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input class="form-control" list="exampleDataList" id="exampleDataList" placeholder="Buscar Usuario ..." name="fuser" value="{{ $queryUser }}"/>
                                <datalist id="exampleDataList">
                                    @if ($queryUser != null)
                                        <option selected value="{{ $queryUser }}" >{{ $queryUser }}</option>
                                    @endif
                                    @foreach ($filterUsers as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }} ({{ $item->email }})</option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label for="role_filter" class="form-label">Tipo de usuario</label>
                            <select name="role_filter" id="role_filter" class="form-select">
                                <option value="">Todos los roles</option>
                                <option value="0" {{ isset($role_filter) && $role_filter == '0' ? 'selected' : '' }}>Evaluado</option>
                                <option value="1" {{ isset($role_filter) && $role_filter == '1' ? 'selected' : '' }}>Empresa</option>
                                @if(Auth::user()->role_as >= 2)
                                    <option value="2" {{ isset($role_filter) && $role_filter == '2' ? 'selected' : '' }}>Repro</option>
                                @endif
                                @if(Auth::user()->role_as == 3)
                                    <option value="3" {{ isset($role_filter) && $role_filter == '3' ? 'selected' : '' }}>Administrador</option>
                                @endif
                            </select>
                        </div>

                        @if(Auth::user()->role_as >= 2)
                        <div class="col-md-3 mb-2">
                            <label for="empresa_filter" class="form-label">Empresa</label>
                            <select name="empresa_filter" id="empresa_filter" class="form-select">
                                <option value="">Todas las empresas</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ isset($empresa_filter) && $empresa_filter == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-2 d-flex align-items-end mb-2">
                            <div class="btn-group w-100">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <a href="{{ url('users') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($queryUser || (isset($role_filter) && $role_filter != '') || (isset($empresa_filter) && $empresa_filter != ''))
                    <div class="mt-2">
                        <span class="badge bg-info text-dark">Filtros activos:</span>
                        @if($queryUser)
                            <span class="badge bg-light text-dark">Búsqueda: {{ $queryUser }}</span>
                        @endif
                        @if(isset($role_filter) && $role_filter != '')
                            <span class="badge bg-light text-dark">
                                Rol:
                                @if($role_filter == '0') Evaluado
                                @elseif($role_filter == '1') Empresa
                                @elseif($role_filter == '2') Repro
                                @elseif($role_filter == '3') Administrador
                                @endif
                            </span>
                        @endif
                        @if(isset($empresa_filter) && $empresa_filter != '')
                            @php
                                $empresaName = '';
                                foreach($empresas as $emp) {
                                    if($emp->id == $empresa_filter) {
                                        $empresaName = $emp->nombre;
                                        break;
                                    }
                                }
                            @endphp
                            <span class="badge bg-light text-dark">Empresa: {{ $empresaName }}</span>
                        @endif
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Row end -->
