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
        $origin = $request->header('Origin');
        
        if (empty($origin)) {
            $origin = 'http://localhost:3000';
        }

        $headers = [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With, X-CSRF-TOKEN',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400'
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200, $headers);
        }

        $response = $next($request);
        
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
