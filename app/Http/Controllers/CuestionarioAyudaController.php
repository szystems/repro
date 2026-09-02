<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CuestionarioAyudaController extends Controller
{
    public function index(): View
    {
        $ayuda = config('cuestionario_ayuda', []);

        return view('cuestionario.ayuda', compact('ayuda'));
    }
}
