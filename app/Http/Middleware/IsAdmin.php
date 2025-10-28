<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            if (!$payload || !$payload->get('sub')) {
                return response()->json(['error' => 'Token inválido'], 401);
            }

            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$user->is_admin) {
                return response()->json(['error' => 'Acesso negado. Permissões de administrador necessárias.'], 403);
            }

            // Adiciona o usuário ao request para uso posterior
            $request->attributes->set('user', $user);

            return $next($request);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro de autenticação'], 500);
        }
    }
}
