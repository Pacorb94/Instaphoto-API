<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class JWT
{
    /**
     * Handle an incoming request.
     * @param $request
     * @param $next
     * @return 
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response(['status' => 'Token is invalid'], 400);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response(['status' => 'Token is expired'], 500);
            }
            return response(['status' => 'Authorization Token not found'], 400);          
        }
        return $next($request);
    }
}
