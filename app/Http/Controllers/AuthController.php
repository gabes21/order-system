<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $response = Http::acceptJson()
            ->withHeaders([
                'apikey' => config('services.supabase.key'),
            ])
            ->post(
                rtrim(config('services.supabase.url'), '/')
                    . '/auth/v1/token?grant_type=password',
                $credentials
            );

        if ($response->failed()) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        $data = $response->json();

        $request->session()->regenerate();

        $request->session()->put([
            'supabase_access_token' => $data['access_token'],
            'supabase_refresh_token' => $data['refresh_token'],
            'supabase_user' => $data['user'],
        ]);

        return redirect('/store');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}