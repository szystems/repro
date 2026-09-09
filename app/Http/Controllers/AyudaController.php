<?php

namespace App\Http\Controllers;

use App\Support\AyudaSupport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function verGuiaSigor(): BinaryFileResponse
    {
        return response()->file($this->rutaGuiaSigor(), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="GUIA DE USUARIO SIGOR.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function descargarGuiaSigor(): BinaryFileResponse
    {
        return response()->download($this->rutaGuiaSigor(), 'GUIA DE USUARIO SIGOR.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function rutaGuiaSigor(): string
    {
        $ruta = resource_path('ayuda/guia-usuario-sigor.pdf');
        abort_unless(is_file($ruta), 404, 'La guía no está disponible.');

        return $ruta;
    }
}
