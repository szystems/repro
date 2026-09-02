<?php

namespace App\Http\Controllers;

use App\Support\AyudaSupport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AyudaController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $porCategoria = AyudaSupport::articulosPorCategoria($user);
        $porModulo = AyudaSupport::articulosPorModulo($user);
        $destacados = AyudaSupport::destacadosDashboard($user);

        return view('ayuda.index', compact('porCategoria', 'porModulo', 'destacados'));
    }

    public function show(string $slug): View
    {
        $user = auth()->user();
        $articulo = AyudaSupport::articuloPorSlug($user, $slug);

        abort_if($articulo === null, 404);

        $relacionados = AyudaSupport::relacionados($user, $articulo);

        return view('ayuda.show', compact('articulo', 'relacionados'));
    }

    public function buscar(Request $request): View
    {
        $user = auth()->user();
        $q = (string) $request->query('q', '');
        $resultados = AyudaSupport::buscar($user, $q);

        return view('ayuda.buscar', compact('q', 'resultados'));
    }

    public function faq(): View
    {
        $user = auth()->user();
        $preguntas = AyudaSupport::faqConEnlaces($user);

        return view('ayuda.faq', compact('preguntas'));
    }

    public function glosario(): View
    {
        $terminos = AyudaSupport::glosarioEnriquecido();

        return view('ayuda.glosario', compact('terminos'));
    }
}
