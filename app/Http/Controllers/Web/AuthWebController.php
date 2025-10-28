<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthWebController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redireciona baseado no tipo de usuário
            if ($user->is_admin) {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->role === 'proprietario') {
                return redirect()->intended(route('partner.dashboard'));
            } elseif ($user->role === 'influenciador') {
                return redirect()->intended(route('influencer.dashboard'));
            }
            
            Auth::logout();
            return back()->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
