<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Config;
use App\Http\Requests\ConfigFormRequest;
use Illuminate\Support\Facades\File;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first();
        return view('admin.config.index', \compact('config'));
    }

    public function update(ConfigFormRequest $request)
    {
        $currencyInput = trim($request->input('currency'));
        $currencyParts = explode(' ', $currencyInput);
        $currency_simbol = isset($currencyParts[1]) ? ucwords($currencyParts[1]) : ucwords($currencyParts[0]);

        $config = Config::first();
        if ($request->hasFile('logo')) {
            $path = 'assets/imgs/logos/'.$config->logo;
            if (File::exists($path)) {
                File::delete($path);
            }
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;
            $file->move('assets/imgs/logos/', $filename);
            $config->logo = $filename;
        }
        $config->currency = $request->input('currency');
        $config->currency_simbol = $currency_simbol;
        $config->email = $request->input('email');
        $config->fb_link = $request->input('fb_link');
        $config->inst_link = $request->input('inst_link');
        $config->yt_link = $request->input('yt_link');
        $config->wapp_link = $request->input('wapp_link');
        $config->descuento_maximo = $request->input('descuento_maximo', 0);
        $config->impuesto = $request->input('impuesto', 0);
        $config->nombre_empresa = $request->input('nombre_empresa') ?: null;
        $config->dias_vigencia_token = $request->input('dias_vigencia_token', 30);
        $config->update();

        // $request->session()->flash('alert-success', 'Configuración actualizado correctamente!');
        return redirect('config')->with('status', __('Configuración actualizada correctamente!'));
    }

    public function finanzas(): \Illuminate\View\View
    {
        return view('admin.finanzas.index');
    }
}
