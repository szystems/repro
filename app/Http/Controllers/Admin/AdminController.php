<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Config;
use App\Models\User;
use Carbon\Carbon;
use DB;
use PDF;

class AdminController extends Controller
{
    public function index()
    {
        $config = Config::first();
        $data = ['config' => $config];

        // Determinar qué layout usar según el rol del usuario
        $user = Auth::user();
        $layout = 'admin';

        if ($user->role_as == 0) {
            $layout = 'evaluado';
            // Aquí puedes agregar datos específicos para evaluados
        } elseif ($user->role_as == 1) {
            $layout = 'empresa';

            // Datos específicos para empresas
            if ($user->empresa) {
                $data['empresa'] = $user->empresa;
            }
        } elseif ($user->role_as >= 2) {
            $layout = 'admin';

            // Datos específicos para admin y repro
            // Por ejemplo, estadísticas generales
            $data['totalEmpresas'] = \App\Models\Empresa::where('estado', 1)->count();
            $data['totalUsuarios'] = User::where('estado', 1)->count();
        }

        return view('admin.index', $data)->with('layout', $layout);
    }
}
