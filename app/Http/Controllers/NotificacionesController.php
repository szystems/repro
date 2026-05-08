<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionesController extends Controller
{
    public function index(): JsonResponse
    {
        $notificaciones = Auth::user()->unreadNotifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'mensaje' => $n->data['mensaje'] ?? '',
                'icono' => $n->data['icono'] ?? 'bi-bell',
                'color' => $n->data['color'] ?? 'secondary',
                'url' => $n->data['url'] ?? '#',
                'tiempo' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
            'notificaciones' => $notificaciones,
        ]);
    }

    public function centro(Request $request): \Illuminate\View\View
    {
        $query = Auth::user()->notifications()->latest();

        if ($request->filled('estado')) {
            if ($request->estado === 'leida') {
                $query->whereNotNull('read_at');
            } elseif ($request->estado === 'no_leida') {
                $query->whereNull('read_at');
            }
        }

        if ($request->filled('tipo')) {
            $query->where('type', 'like', '%' . $request->tipo . '%');
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('data->mensaje', 'like', "%{$buscar}%");
            });
        }

        $notificaciones = $query->paginate(20)->appends($request->query());
        $totalNoLeidas = Auth::user()->unreadNotifications()->count();

        return view('notificaciones.centro', compact('notificaciones', 'totalNoLeidas'));
    }

    public function marcarLeida(string $id): JsonResponse
    {
        $notificacion = Auth::user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function marcarTodasLeidas(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
