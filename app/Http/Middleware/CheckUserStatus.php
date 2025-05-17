<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isActive()) {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is inactive. Please contact the administrator.',
                ], 403);
            }


            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        return $next($request);
    }
}
