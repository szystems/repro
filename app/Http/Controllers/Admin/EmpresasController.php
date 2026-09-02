<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\User;
use App\Http\Requests\EmpresaFormRequest;
use App\Exports\EmpresasExport;
use App\Support\ExportacionesSupport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\Config;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class EmpresasController extends Controller
{
    /**
     * Construir query base de empresas con filtros de búsqueda y estado.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildEmpresasQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Empresa::query()->with('creador');

        $searchTerm = $filters['search'] ?? null;
        $estado = $filters['estado'] ?? null;

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%'.$searchTerm.'%')
                  ->orWhere('nit', 'like', '%'.$searchTerm.'%')
                  ->orWhere('email', 'like', '%'.$searchTerm.'%')
                  ->orWhere('telefono', 'like', '%'.$searchTerm.'%');
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', $estado);
        } else {
            $query->where('estado', 1);
        }

        return $query->orderBy('nombre', 'asc');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchTerm = $request->input('search');
        $estado = $request->input('estado');

        $empresas = $this->buildEmpresasQuery($request->all())->paginate(20);

        return view('admin.empresa.index', compact('empresas', 'searchTerm', 'estado'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.empresa.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\EmpresaFormRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmpresaFormRequest $request)
    {
        $empresa = new Empresa();
        $empresa->nombre = $request->input('nombre');
        $empresa->nit = $request->input('nit');
        $empresa->direccion = $request->input('direccion');
        $empresa->telefono = $request->input('telefono');
        $empresa->email = $request->input('email');
        $empresa->descripcion = $request->input('descripcion');
        $empresa->sitio_web = $request->input('sitio_web');
        $empresa->contacto_nombre = $request->input('contacto_nombre');
        $empresa->contacto_cargo = $request->input('contacto_cargo');
        $empresa->contacto_telefono = $request->input('contacto_telefono');
        $empresa->contacto_email = $request->input('contacto_email');
        $empresa->notas = $request->input('notas');
        $empresa->estado = 1; // Activa por defecto
        if (Schema::hasColumn('empresas', 'created_by')) {
            $empresa->created_by = Auth::id();
        }

        // Subir logo si se proporciona
        if($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;
            $file->move('assets/imgs/empresas', $filename);
            $empresa->logo = $filename;
        }

        $empresa->save();

        return redirect('empresas')->with('status', 'Empresa agregada correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $empresa = Empresa::with('creador')->findOrFail($id);

        // Obtener usuarios relacionados con esta empresa
        $usuarios = User::where('empresa_id', $id)
                ->where('estado', 1)
                ->orderBy('principal', 'desc')
                ->orderBy('name', 'asc')
                ->get();

        return view('admin.empresa.show', compact('empresa', 'usuarios'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $empresa = Empresa::findOrFail($id);

        return view('admin.empresa.edit', compact('empresa'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\EmpresaFormRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EmpresaFormRequest $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $empresa->nombre = $request->input('nombre');
        $empresa->nit = $request->input('nit');
        $empresa->direccion = $request->input('direccion');
        $empresa->telefono = $request->input('telefono');
        $empresa->email = $request->input('email');
        $empresa->descripcion = $request->input('descripcion');
        $empresa->sitio_web = $request->input('sitio_web');
        $empresa->contacto_nombre = $request->input('contacto_nombre');
        $empresa->contacto_cargo = $request->input('contacto_cargo');
        $empresa->contacto_telefono = $request->input('contacto_telefono');
        $empresa->contacto_email = $request->input('contacto_email');
        $empresa->notas = $request->input('notas');

        if($request->has('estado')) {
            $empresa->estado = $request->input('estado');
        }

        // Subir logo si se proporciona uno nuevo
        if($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if($empresa->logo) {
                $path = 'assets/imgs/empresas/'.$empresa->logo;
                if(File::exists($path)) {
                    File::delete($path);
                }
            }

            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $filename = time().'.'.$ext;
            $file->move('assets/imgs/empresas', $filename);
            $empresa->logo = $filename;
        }

        $empresa->update();

        return redirect('show-empresa/'.$id)->with('status', 'Información de empresa actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Verificar permisos - Solo administradores pueden eliminar empresas
        if(Auth::user()->role_as != 3) {
            return redirect('empresas')->with('error', 'No tiene permisos para eliminar empresas');
        }

        $empresa = Empresa::findOrFail($id);

        // Verificar si hay usuarios asociados a esta empresa
        $usuariosAsociados = User::where('empresa_id', $id)->where('estado', 1)->count();
        if($usuariosAsociados > 0) {
            return redirect('empresas')->with('error', 'No se puede eliminar esta empresa porque tiene usuarios asociados');
        }

        // Desactivar en lugar de eliminar físicamente
        $empresa->estado = 0;
        $empresa->update();

        return redirect('empresas')->with('status', 'Empresa desactivada correctamente');
    }

    /**
     * Generar PDF con listado de empresas
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {
        ExportacionesSupport::asegurarPuedeExportarPadronEmpresas(Auth::user());

        $searchTerm = $request->input('search');
        $estado = $request->input('estado');

        $empresas = $this->buildEmpresasQuery($request->all())->get();

        // Configuración para el PDF
        $nompdf = date('d-m-Y_H-i-s');

        $config = Config::first();
        $currency = $config->currency_simbol;

        $imagen = null;
        if ($config->logo && file_exists(public_path('assets/imgs/logos/'.$config->logo))) {
            $imagen = public_path('assets/imgs/logos/'.$config->logo);
        }

        // Construir título del reporte
        $titulo = 'Listado de Empresas';
        if($searchTerm) {
            $titulo .= ' (Búsqueda: '.$searchTerm.')';
        }

        if($estado !== null && $estado !== '') {
            $titulo .= ' - ' . ($estado == 1 ? 'Activas' : 'Inactivas');
        }

        $pdf = Pdf::loadView('admin.empresa.pdf', compact('empresas', 'config', 'imagen', 'titulo', 'currency'));
        return $pdf->stream('Empresas_'.$nompdf.'.pdf');
    }

    public function excel(Request $request)
    {
        ExportacionesSupport::asegurarPuedeExportarPadronEmpresas(Auth::user());

        $empresas = $this->buildEmpresasQuery($request->all())->withCount('ordenes')->get();
        $export = new EmpresasExport($empresas);
        $base = 'padron-empresas-' . now()->format('Y-m-d');

        return ExportacionesSupport::descargarExcel($export, $base);
    }

    /**
     * Generar PDF con detalles de una empresa específica
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pdfEmpresa($id)
    {
        $empresa = Empresa::findOrFail($id);

        // Obtener usuarios relacionados con esta empresa
        $usuarios = User::where('empresa_id', $id)
                ->where('estado', 1)
                ->orderBy('principal', 'desc')
                ->orderBy('name', 'asc')
                ->get();

        $nompdf = date('d-m-Y_H-i-s');

        $config = Config::first();
        $currency = $config->currency_simbol;

        // Rutas de imágenes
        $logoEmpresaPath = null;
        if($empresa->logo && file_exists(public_path('assets/imgs/empresas/'.$empresa->logo))) {
            $logoEmpresaPath = public_path('assets/imgs/empresas/'.$empresa->logo);
        }

        $logoConfigPath = null;
        if ($config->logo && file_exists(public_path('assets/imgs/logos/'.$config->logo))) {
            $logoConfigPath = public_path('assets/imgs/logos/'.$config->logo);
        }

        $pdf = Pdf::loadView('admin.empresa.pdfempresa', compact(
            'empresa', 'usuarios', 'config', 'logoConfigPath', 'logoEmpresaPath', 'currency'
        ));

        $pdf->getDomPDF()->set_option("enable_html5_parser", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->getDomPDF()->set_option("isRemoteEnabled", true);
        $pdf->setPaper('Letter', 'portrait');

        return $pdf->stream('Empresa_'.$empresa->nombre.'_'.$nompdf.'.pdf');
    }

    /**
     * Activar o desactivar una empresa
     *
     * @param  int  $id
     * @param  int  $estado (0 o 1)
     * @return \Illuminate\Http\Response
     */
    public function cambiarEstado($id, $estado)
    {
        // Verificar permisos - Solo administradores
        if(Auth::user()->role_as != 3) {
            return redirect('empresas')->with('error', 'No tiene permisos para cambiar el estado de las empresas');
        }

        $empresa = Empresa::findOrFail($id);
        $empresa->estado = $estado;
        $empresa->update();

        $mensaje = $estado == 1 ? 'Empresa activada correctamente' : 'Empresa desactivada correctamente';

        return redirect()->back()->with('status', $mensaje);
    }
}
