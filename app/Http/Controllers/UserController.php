<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(Request $request)
    {
        // Obtém o token de autorização do cabeçalho
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
                        $data = [
                            "name" => $user->name,
                            "email" => $user->email,
                            "birthday" => $user->birthday,
                            "phone" => $user->phone,
                            "subscription" => $user->subscription,
                            "ticket_count" => $user->ticket_count,
                            "promocode" => $user->promocode
                        ];

                        return response()->json(['user' => $data], 200);
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

}
