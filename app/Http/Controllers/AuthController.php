<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Stripe\StripeClient;


class AuthController extends Controller
{
    public function register(Request $request)
    {

        $stripe = new StripeClient(env('STRIPE_SECRET'));

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'birthday' => 'string',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|max:255',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        
        
        $customer = $stripe->customers->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone']
        ]);
        
        $validatedData['stripe_id'] = $customer['id'];
        
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
        $request->validate([
            'email' => 'required|email'
        ]);

        $response = Password::sendResetLink($request->only('email'));

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
                $payload = JWTAuth::setToken($token)->getPayload();

                if ($payload && $payload->get('sub')) {
                    $userId = $payload->get('sub');

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

        $token = $request->bearerToken();

        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();

                if ($payload && $payload->get('sub')) {
                    $userId = $payload->get('sub');

                    $user = User::find($userId);

                    if ($user) {
                        $user->delete();

                        return response()->json(['message' => 'Perfil removido com sucesso'], 200);
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
