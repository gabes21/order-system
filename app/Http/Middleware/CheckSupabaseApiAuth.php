<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSupabaseApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();

        $token = $session->get('supabase_access_token');
        $expiresAt = $session->get('supabase_expires_at');

        if (!$token || !$expiresAt || (int) $expiresAt <= time()) {
            $session->flush();

            return response()->json(
                [
                    'message' => 'Unauthenticated. Please log in again.',
                ],
                401,
            );
        }

        return $next($request);
    }
}
