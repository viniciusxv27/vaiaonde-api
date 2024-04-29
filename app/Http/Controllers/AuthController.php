<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

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

        return response()->json(null, 200);
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
        // Validação dos dados
        $request->validate(['email' => 'required|email']);

        // Enviar o e-mail de recuperação de senha
        $response = Password::sendResetLink($request->only('email'));

        // Verificar se o e-mail foi enviado com sucesso
        if ($response === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'E-mail de recuperação de senha enviado com sucesso.'], 200);
        } else {
            return response()->json(['error' => 'Falha ao enviar o e-mail de recuperação de senha.'], 500);
        }
    }

    public function updateProfile(Request $request)
    {

        $token = $request->bearerToken();

        if ($token) {
            try {
                // Decodifica o token JWT para obter as informações do payload
                $payload = JWTAuth::setToken($token)->getPayload();

                // Verifica se o token é válido e se possui o campo "sub"
                if ($payload && $payload->get('sub')) {
                    $userId = $payload->get('sub');

                    // Busca o usuário pelo ID obtido do token
                    $user = User::find($userId);

                    if ($user) {
                        $validatedData = $request->validate([
                            'name' => 'string|max:255',
                            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
                            'phone' => 'nullable|string|max:20',
                        ]);
                
                        if (isset($validatedData['password'])) {
                            $validatedData['password'] = Hash::make($validatedData['password']);
                        }
                
                        $user->update($validatedData);
                
                        return response()->json(['message' => 'Perfil atualizado com sucesso'], 200);
                                    } else {
                        return response()->json(['error' => 'Usuário não encontrado'], 404);
                    }
                } else {
                    return response()->json(['error' => 'Token de autorização inválido'], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['error' => 'Token expirado'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['error' => 'Token inválido'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['error' => 'Erro ao processar o token'], 500);
            }
        } else {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }
    }

    public function deleteProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Perfil removido com sucesso'], 200);
    }


}
