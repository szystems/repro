<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('redirect.role');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $viewData = [];

        // Opcional: agregar datos específicos según el tipo de usuario
        if ($user->role_as == 1 && $user->empresa) {
            $viewData['empresa'] = $user->empresa;
        }

        return view('admin.index', $viewData);
    }
}
