<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Parameter $role akan dikirim dari routes/api.php
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->attributes->get('auth_user');

        // Pastikan user ada dan role-nya sesuai dengan yang diminta di rute
        if (!$user || $user->role !== $role) {
            return response()->json([
                'message' => 'Forbidden: Endpoint ini membutuhkan role ' . $role
            ], 403);
        }

        return $next($request);
    }
}
