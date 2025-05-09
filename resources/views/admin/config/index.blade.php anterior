@extends('layouts.admin')

@section('content')
    <div class="row">

        <div class="col-xl-12 col-sm-12 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div
                        class="icon icon-lg icon-shape bg-gradient-primary shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">settings</i>
                    </div>
                    <div class="text-center pt-1">
                        {{-- <p class="text-sm mb-0 text-capitalize">Today's Money</p> --}}
                        <h4 class="mb-0">{{ __('Settings') }}</h4>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
                <div class="card-body p-3 pt-2">
                    <h4><u>{{ __('Asonata Settings') }}</u></h4>
                    <!-- .flash-message -->
                    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                        @if(Session::has('alert-' . $msg))
                            <div class="alert alert-{{ $msg }} alert-dismissible text-white fade show" role="alert">
                                <span class="alert-text"> {{ Session::get('alert-' . $msg) }}</span>
                                <span class="alert-icon align-middle">
                                    <span class="material-icons text-md">
                                    thumb_up_off_alt
                                    </span>
                                </span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                     @endforeach
                    <!-- fin .flash-message -->
                    <form action="{{ url('update-config') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_method" value="PUT">
                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label for="">{{ __('Currency') }}</label>
                                <select class="form-select px-2" aria-label="Default select example" name="currency">
                                    <option selected value="{{ $config->currency }}">{{ $config->currency }}</option>
                                    <option value="USD $">USD ($)</option>
                                    <option value="GTQ Q">GTQ (Q)</option>
                                </select>
                                <label><font color="orange">{{ __('Choose the currency that will be displayed on the e-shop') }}</font></label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="">Email</label>
                                <div class="input-group input-group-dynamic mb-4">
                                    <input type="email" name="email" class="form-control" placeholder="info@dominio.com" aria-describedby="basic-addon1" value="{{ $config->email }}" required>
                                </div>
                                <label><font color="orange">{{ __('Write the email where you will receive all notifications') }}</font></label>
                                @if ($errors->has('email'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('email') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="">Facebook Link</label>
                                <div class="input-group input-group-dynamic mb-4">
                                    <input type="text" name="fb_link" class="form-control" placeholder="Link de pagina de Facebook" aria-describedby="basic-addon1" value="{{ $config->fb_link }}">
                                </div>
                                @if ($errors->has('fb_link'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('fb_link') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="">Instagram Link</label>
                                <div class="input-group input-group-dynamic mb-4">
                                    <input type="text" name="inst_link" class="form-control" placeholder="Link de pagina de Instagram" aria-describedby="basic-addon1" value="{{ $config->inst_link }}">
                                </div>
                                @if ($errors->has('inst_link'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('inst_link') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="">Youtube Link</label>
                                <div class="input-group input-group-dynamic mb-4">
                                    <input type="text" name="yt_link" class="form-control" placeholder="Link del canal de Youtube" aria-describedby="basic-addon1" value="{{ $config->yt_link }}">
                                </div>
                                @if ($errors->has('yt_link'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('yt_link') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="">Numero de Whatsapp</label>
                                <div class="input-group input-group-dynamic mb-4">
                                    <input type="number" name="wapp_link" min="0" step="1" class="form-control" placeholder="# de Whatsapp" aria-describedby="basic-addon1" value="{{ $config->wapp_link }}" oninput="this.value = this.value.replace(/\\./g, '')">
                                </div>
                                @if ($errors->has('wapp_link'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('wapp_link') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Recommendations and rules in the use of facilities') }}</label>
                                <textarea type="text" class="form-control border px-2 " rows="5" name="payments_description">{{  $config->payments_description  }}</textarea>
                                @if ($errors->has('payments_description'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('payments_description') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Registration Process') }}</label>
                                <textarea type="text" class="form-control border px-2 " rows="5" name="registration_process">{{  $config->registration_process  }}</textarea>
                                @if ($errors->has('registration_process'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('registration_process') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            {{-- <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="paypal" {{ $config->paypal == 1 ? 'checked':'' }}>
                                    <label class="form-check-label" for="flexSwitchCheckDefault">{{ __('PayPal Status') }}</label>
                                </div>
                                <label><font color="orange">{{ __('Enable PayPal payment') }}</font></label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="dbt" {{ $config->dbt == 1 ? 'checked':'' }}>
                                    <label class="form-check-label" for="flexSwitchCheckDefault">{{ __('POD/DBT Status') }}</label>
                                </div>
                                <label><font color="orange">{{ __('Enable Payment per Direct Bank Transfer') }}r</font></label>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tax_status" {{ $config->tax_status == 1 ? 'checked':'' }}>
                                        <label class="form-check-label" for="flexSwitchCheckDefault">{{ __('Tax Status') }}</label>
                                    </div>
                                </label>

                                <div class="input-group input-group-dynamic mb-4">
                                    <span class="input-group-text" id="basic-addon1">%</span>
                                    <input type="number" name="tax" class="form-control" placeholder="Example:12%" aria-describedby="basic-addon1" value="{{ $config->tax }}" required>
                                </div>
                                <label><font color="orange">{{ __('Enable tax and percentage in order') }}</font></label>
                                @if ($errors->has('tax'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('tax') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Shipping Description') }}</label>
                                <textarea name="shipping_description" cols="30" rows="5" class="form-control border px-2 ">{{ $config->shipping_description }}</textarea>
                                @if ($errors->has('shipping_description'))
                                    <span class="help-block opacity-7">
                                            <strong>
                                                <font color="red">{{ $errors->first('shipping_description') }}</font>
                                            </strong>
                                    </span>
                                @endif
                            </div> --}}
                            <input type="hidden" name="tax" value="0">

                            {{-- @if ($config->contract)
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Contract') }}</label>
                                <a class="border-radius-md w-25" href="{{ asset('assets/uploads/contract/'.$config->contract) }}" target="_blank">{{ __('Download Contract') }}: {{ $config->contract }}</a>
                            </div>
                            @endif
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Change Contract') }}</label>
                                <input type="file" name="contract" class="form-control border">
                            </div> --}}
                            @if ($config->logo)
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Image') }}</label>
                                <img class="border-radius-md w-25" src="{{ asset('assets/uploads/logos/'.$config->logo) }}" alt="Logo Image">
                            </div>
                            @endif
                            <div class="col-md-12 mb-3">
                                <label for="">{{ __('Change Image') }}</label>
                                <input type="file" name="logo" class="form-control border">
                            </div>

                            <div class="col-md-12 mb-3" >
                                <button type="submit" class="btn btn-primary"><i class="material-icons">save</i> {{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    {{-- <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+55% </span>than last week</p> --}}
                </div>
            </div>
        </div>

    </div>
@endsection
