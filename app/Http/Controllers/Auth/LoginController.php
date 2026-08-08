<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                try { Log::registrar('login'); } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Log::registrar falhou: ' . $e->getMessage());
                }
                return redirect()->intended(route('dashboard'));
            }

            try { Log::registrar('login_falhou'); } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Log::registrar falhou: ' . $e->getMessage());
            }

            return back()->withErrors([
                'email' => 'Credenciais inválidas.',
            ])->onlyInput('email');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ERRO LOGIN: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return back()->withErrors([
                'email' => 'Erro interno: ' . $e->getMessage(),
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request)
    {
        Log::registrar('logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
