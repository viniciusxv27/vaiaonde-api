<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'birthday' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|max:255',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json(null , 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        try {
            if (!$token = FacadesJWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    
        return response()->json(['token' => $token], 200);
    }

    public function recoverPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'E-mail não encontrado'], 404);
        }

        $token = Password::createToken($user);

        $resetPasswordNotification = new ResetPassword($token);
        $user->notify($resetPasswordNotification);

        return response()->json(null, 200);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Verifique se o usuário autenticado é o proprietário do perfil
        if ($request->user()->id !== $user->id) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|max:255',
            'subscription' => 'nullable|string|max:255',
            'stripe_id' => 'nullable|string|max:255',
            'ticket_count' => 'nullable|integer',
            'promocode' => 'nullable|string|max:255',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json(['message' => 'Perfil atualizado com sucesso'], 200);
    }

    public function deleteProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Verifique se o usuário autenticado é o proprietário do perfil
        if ($request->user()->id !== $user->id) {
            return response()->json(['error' => 'Acesso não autorizado'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Perfil removido com sucesso'], 200);
    }


}
