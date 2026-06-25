<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     * Mirip seperti: const authMiddleware = (req, res, next) => { ... }
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil token dari header Authorization: Bearer <token>
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: No token provided'], 401);
        }

        // 2. Pecah token JWT (Header.Payload.Signature)
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json(['message' => 'Unauthorized: Invalid token format'], 401);
        }

        // 3. Decode Payload (Bagian tengah dari token)
        // Kembalikan Base64Url ke Base64 standar sebelum di-decode
        $base64Payload = str_replace(['-', '_'], ['+', '/'], $parts[1]);
        $payload = json_decode(base64_decode($base64Payload));

        // 4. Cek kedaluwarsa token
        if (isset($payload->exp) && $payload->exp < time()) {
            return response()->json(['message' => 'Unauthorized: Token expired'], 401);
        }

        // 5. Cari user di database mysql_users
        $user = User::query()->find($payload->user_id);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized: User not found'], 401);
        }

        // 6. Sisipkan data user ke dalam Request 
        // Express.js analogi: req.user = user;
        $request->attributes->add(['auth_user' => $user]);

        // Lanjut ke controller (Express.js analogi: next())
        return $next($request);
    }
}
