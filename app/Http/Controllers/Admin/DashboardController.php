<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [];

        // Obtener estadísticas según el rol del usuario
        $currentUser = Auth::user();

        if ($currentUser->role_as == 3 || $currentUser->role_as == 2) {
            // Para administradores y usuarios de Repro
            $data['totalEmpresas'] = Empresa::where('estado', 1)->count();
            $data['totalUsuarios'] = User::where('estado', 1)->count();
            $data['usuariosEmpresa'] = User::where('estado', 1)->where('role_as', 1)->count();
            $data['usuariosRepro'] = User::where('estado', 1)->where('role_as', 2)->count();
            $data['evaluados'] = User::where('estado', 1)->where('role_as', 0)->count();

            // Listar las empresas más recientes
            $data['empresasRecientes'] = Empresa::where('estado', 1)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Listar usuarios recientes
            $data['usuariosRecientes'] = User::where('estado', 1)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        elseif ($currentUser->role_as == 1) {
            // Para usuarios de empresa
            $empresa = Empresa::find($currentUser->empresa_id);
            if ($empresa) {
                $data['empresa'] = $empresa;
                $data['totalUsuariosEmpresa'] = User::where('empresa_id', $empresa->id)
                    ->where('estado', 1)
                    ->count();

                // Listar los usuarios de la empresa
                $data['usuariosEmpresa'] = User::where('empresa_id', $empresa->id)
                    ->where('estado', 1)
                    ->orderBy('principal', 'desc')
                    ->orderBy('name', 'asc')
                    ->get();
            }
        }

        return view('admin.dashboard', $data);
    }
}
