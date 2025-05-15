<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'https://aeddi-antsiranana.onrender.com',
            'https://aeddi-backend.onrender.com',
            'http://localhost:8000'
        ];

        $origin = $request->header('Origin');
        
        // Définir les en-têtes CORS par défaut
        $headers = [
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN, Accept',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Expose-Headers'    => 'Authorization, X-CSRF-TOKEN, X-XSRF-TOKEN'
        ];

        // Si l'origine est dans la liste des origines autorisées, l'utiliser, sinon utiliser '*'
        if (in_array($origin, $allowedOrigins)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
        } else {
            $headers['Access-Control-Allow-Origin'] = '*';
        }


        // Gestion des requêtes OPTIONS (prévol)
        if ($request->isMethod('OPTIONS')) {
            return response('', 200, $headers);
        }


        // Pour les autres requêtes, exécuter la requête et ajouter les en-têtes
        $response = $next($request);

        // Ajouter les en-têtes CORS à la réponse
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
