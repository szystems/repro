<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
