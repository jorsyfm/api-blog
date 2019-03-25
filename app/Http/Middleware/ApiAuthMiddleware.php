<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        // Recibir token
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            // Validación correcta
            return $next($request);
        } else {
            // Error al identificarse
            $response = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado'
            );
            return response()->json($response,$response['code']);
        }
                
    }
}
