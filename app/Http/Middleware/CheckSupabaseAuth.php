<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSupabaseAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();

        $token     = $session->get('supabase_access_token');
        $expiresAt = $session->get('supabase_expires_at');

        if (! $token || ! $expiresAt || $expiresAt <= time()) {
            $session->flush();

            return redirect('/login')->withErrors([
                'email' => 'Your session has expired. Please log in again.',
            ]);
        }

        return $next($request);
    }
}
