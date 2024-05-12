<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function cancel(Request $request)
    {
    }

    public function buy(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();

                if ($payload && $payload->get('sub')) {
                    $userId = $payload->get('sub');

                    $user = User::find($userId);

                    if ($user) {

                        $stripe = new StripeClient(env('STRIPE_SECRET'));

                        if ($user->promocode == '1')
                        {
                            $stripe->subscriptions->create([
                                'customer' => $user->stripe_id,
                                'items' => [['price' => env('STRIPE_VACLUB_SUBSCRIPTION')]],
                                'payment_settings' => ['payment_method_types' => ['card']],
                                'discount' => ['coupon' => env('STRIPE_VACLUB_COUPON')]
                            ]);
                        } else {
                            $stripe->subscriptions->create([
                                'customer' => $user->stripe_id,
                                'items' => [['price' => env('STRIPE_VACLUB_SUBSCRIPTION')]],
                                'payment_settings' => ['payment_method_types' => ['card']]
                            ]);
                        }

                        // return OK
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
