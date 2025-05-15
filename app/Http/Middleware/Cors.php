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
    public function handle($request, \Closure $next)
    {
        // Liste des origines autorisées
        $allowedOrigins = [
            'https://aeddi-antsiranana.onrender.com',
            'http://localhost:3000',  // Pour le développement local
        ];

        // Récupérer l'origine de la requête
        $origin = $request->header('Origin');

        // Vérifier si l'origine est autorisée
        if (in_array($origin, $allowedOrigins)) {
            $headers = [
                'Access-Control-Allow-Origin'      => $origin,
                'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN, Accept',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age'           => '86400',
                'Access-Control-Expose-Headers'    => 'Authorization, X-CSRF-TOKEN, X-XSRF-TOKEN'
            ];
        } else {
            // Si l'origine n'est pas autorisée, on retourne une erreur CORS
            return response('Not allowed', 403)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN, Accept');
        }

        // Gestion des requêtes OPTIONS (prévol)
        if ($request->isMethod('OPTIONS')) {
            return response('', 200, $headers);
        }

        // Pour les autres requêtes, exécuter la requête et ajouter les en-têtes
        $response = $next($request);

        // Ajouter les en-têtes CORS à la réponse
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
