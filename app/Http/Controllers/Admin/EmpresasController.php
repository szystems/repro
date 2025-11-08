<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\User;
use App\Http\Requests\EmpresaFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\Config;
use Carbon\Carbon;
use PDF;
use DB;

class EmpresasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Verificar permisos - Solo roles 2 y 3 pueden acceder
        if(Auth::user()->role_as < 2) {
            return redirect('/dashboard')->with('error', 'No tiene permisos para acceder a esta sección');
        }

        // Obtener parámetros de búsqueda y filtrado
        $searchTerm = $request->input('search');
        $estado = $request->input('estado');

        $empresasQuery = Empresa::query();

        // Aplicar filtros de búsqueda
        if($searchTerm) {
            $empresasQuery->where(function($query) use ($searchTerm) {
                $query->where('nombre', 'like', '%'.$searchTerm.'%')
                    ->orWhere('nit', 'like', '%'.$searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$searchTerm.'%')
                    ->orWhere('telefono', 'like', '%'.$searchTerm.'%');
            });
        }

        // Filtrar por estado si se especifica
        if($estado !== null && $estado !== '') {
            $empresasQuery->where('estado', $estado);
        } else {
            // Por defecto mostrar solo empresas activas
            $empresasQuery->where('estado', 1);
        }

        $empresas = $empresasQuery->orderBy('nombre', 'asc')->paginate(20);

        return view('admin.empresa.index', compact('empresas', 'searchTerm', 'estado'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Verificar permisos
        if(Auth::user()->role_as < 2) {
            return redirect('/dashboard')->with('error', 'No tiene permisos para crear empresas');
        }

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
        // Verificar permisos
        if(Auth::user()->role_as < 2) {
            return redirect('/dashboard')->with('error', 'No tiene permisos para ver detalles de empresas');
        }

        $empresa = Empresa::findOrFail($id);

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
        // Verificar permisos
        if(Auth::user()->role_as < 2) {
            return redirect('/dashboard')->with('error', 'No tiene permisos para editar empresas');
        }

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
        // Verificar permisos
        if(Auth::user()->role_as < 2) {
            return redirect('/dashboard')->with('error', 'No tiene permisos para generar reportes');
        }

        $searchTerm = $request->input('search');
        $estado = $request->input('estado');

        $empresasQuery = Empresa::query();

        // Aplicar filtros de búsqueda
        if($searchTerm) {
            $empresasQuery->where(function($query) use ($searchTerm) {
                $query->where('nombre', 'like', '%'.$searchTerm.'%')
                    ->orWhere('nit', 'like', '%'.$searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$searchTerm.'%')
                    ->orWhere('telefono', 'like', '%'.$searchTerm.'%');
            });
        }

        // Filtrar por estado si se especifica
        if($estado !== null && $estado !== '') {
            $empresasQuery->where('estado', $estado);
        } else {
            // Por defecto mostrar solo empresas activas
            $empresasQuery->where('estado', 1);
        }

        $empresas = $empresasQuery->orderBy('nombre', 'asc')->get();

        // Configuración para el PDF
        $verpdf = "Browser"; // "Download" o "Browser"
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

        if ($verpdf == "Download") {
            $pdf = PDF::loadView('admin.empresa.pdf', compact('empresas', 'config', 'imagen', 'titulo', 'currency'));
            return $pdf->download('Empresas_'.$nompdf.'.pdf');
        } else {
            $pdf = PDF::loadView('admin.empresa.pdf', compact('empresas', 'config', 'imagen', 'titulo', 'currency'));
            return $pdf->stream('Empresas_'.$nompdf.'.pdf');
        }
    }

    /**
     * Generar PDF con detalles de una empresa específica
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pdfEmpresa($id)
    {
        // Verificar permisos
        if(Auth::user()->role_as < 2) {
            return redirect('/dashboard')->with('error', 'No tiene permisos para generar reportes');
        }

        $empresa = Empresa::findOrFail($id);

        // Obtener usuarios relacionados con esta empresa
        $usuarios = User::where('empresa_id', $id)
                ->where('estado', 1)
                ->orderBy('principal', 'desc')
                ->orderBy('name', 'asc')
                ->get();

        $verpdf = "Browser"; // "Download" o "Browser"
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

        // Opciones de papel
        $pdftamaño = 'Letter';
        $pdfhorientacion = 'portrait';

        if ($verpdf == "Download") {
            $pdf = PDF::loadView('admin.empresa.pdfempresa', compact(
                'empresa', 'usuarios', 'config', 'logoConfigPath', 'logoEmpresaPath', 'currency'
            ));

            // Configuración adicional para DOMPDF
            $pdf->getDomPDF()->set_option("enable_html5_parser", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->getDomPDF()->set_option("isRemoteEnabled", true);

            $pdf->setPaper($pdftamaño, $pdfhorientacion);

            return $pdf->download('Empresa_'.$empresa->nombre.'_'.$nompdf.'.pdf');
        } else {
            $pdf = PDF::loadView('admin.empresa.pdfempresa', compact(
                'empresa', 'usuarios', 'config', 'logoConfigPath', 'logoEmpresaPath', 'currency'
            ));

            // Configuración adicional para DOMPDF
            $pdf->getDomPDF()->set_option("enable_html5_parser", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->getDomPDF()->set_option("isRemoteEnabled", true);

            $pdf->setPaper($pdftamaño, $pdfhorientacion);

            return $pdf->stream('Empresa_'.$empresa->nombre.'_'.$nompdf.'.pdf');
        }
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
