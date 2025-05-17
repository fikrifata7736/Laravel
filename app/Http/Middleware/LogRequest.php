<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $route = $request->route() ? $request->route()->uri() : 'unknown';
        $method = $request->method();

        // Get user ID
        $userId = $request->user() ? $request->user()->id : 'guest';


        Log::channel('api_activity')->info("API Request", [
            'user_id' => $userId,
            'route' => $route,
            'method' => $method,
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
