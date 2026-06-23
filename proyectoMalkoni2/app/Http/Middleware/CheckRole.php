<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Mapeo de segmentos de ruta a roles permitidos.
     */
    private const ROUTE_ROLES = [
        'vendedor'   => 'vendedor',
        'supervisor' => 'supervisor',
        'cliente'    => 'cliente',
    ];

    /**
     * Intercepta la petición y verifica que el rol en sesión
     * coincida con el grupo de ruta al que se intenta acceder.
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $sessionRole = session('user_role');

        if (empty($sessionRole) || $sessionRole !== $requiredRole) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado.',
                    'error'   => 'FORBIDDEN',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'No tenés permiso para acceder a esa sección.');
        }

        return $next($request);
    }
}
