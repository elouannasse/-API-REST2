<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié et s'il est un administrateur
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé. Administrateur requis.'  
            ], 403);
        }

        // Si l'utilisateur est un administrateur, continuer avec la requête
        return $next($request);
    }
}
