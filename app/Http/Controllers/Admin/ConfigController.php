<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Config;
use App\Http\Requests\ConfigFormRequest;
use Illuminate\Support\Facades\File;
use DB;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first();
        return view('admin.config.index', \compact('config'));
    }

    public function update(ConfigFormRequest $request)
    {
        $currency = explode(' ',trim($request->input('currency')));
        $currency_simbol = ucwords($currency[1]);

        $config = Config::first();
        if($request->hasFile('logo'))
        {
            $path = 'assets/imgs/logos/'.$config->logo;
            if(File::exists($path))
            {
                File::delete($path);
            }
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;
            $file->move('assets/imgs/logos/',$filename);
            $config->logo = $filename;
        }
        if($request->hasFile('contract'))
        {
            $path1 = 'assets/imgs/contract/'.$config->contract;
            if(File::exists($path1))
            {
                File::delete($path1);
            }
            $file1 = $request->file('contract');
            $ext1 = $file1->getClientOriginalExtension();
            $filename1 = time().'.'.$ext1;
            $file1->move('assets/imgs/contract/',$filename1);
            $config->contract = $filename1;
        }
        $config->currency = $request->input('currency');
        $config->currency_simbol = $currency_simbol;
        $config->email = $request->input('email');
        $config->fb_link = $request->input('fb_link');
        $config->inst_link = $request->input('inst_link');
        $config->yt_link = $request->input('yt_link');
        $config->wapp_link = $request->input('wapp_link');
        // $config->descuento_maximo = $request->input('descuento_maximo');
        $config->impuesto = $request->input('impuesto');
        $config->update();

        // $request->session()->flash('alert-success', 'Configuración actualizado correctamente!');
        return redirect('config')->with('status', __('Configuración actualizada correctamente!'));

        // return view('admin.config.index', \compact('config'));
    }
}
