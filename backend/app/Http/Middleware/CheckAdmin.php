<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Authentication required'
            ], 401);
        }

        // Check if user is admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden. Admin access required.',
                'error' => 'Insufficient privileges'
            ], 403);
        }

        return $next($request);
    }
}
