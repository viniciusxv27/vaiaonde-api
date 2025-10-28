<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    /**
     * Cancela a assinatura do usuário
     */
    public function cancel(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            if ($payload && $payload->get('sub')) {
                $userId = $payload->get('sub');
                $user = User::find($userId);

                if (!$user) {
                    return response()->json(['error' => 'Usuário não encontrado'], 404);
                }

                if (!$user->subscription) {
                    return response()->json(['error' => 'Usuário não possui assinatura ativa'], 400);
                }

                $stripe = new StripeClient(env('STRIPE_SECRET'));

                // Busca as assinaturas do cliente
                if ($user->stripe_id) {
                    $subscriptions = $stripe->subscriptions->all([
                        'customer' => $user->stripe_id,
                        'status' => 'active',
                        'limit' => 10,
                    ]);

                    // Cancela todas as assinaturas ativas
                    foreach ($subscriptions->data as $subscription) {
                        $stripe->subscriptions->cancel($subscription->id);
                    }
                }

                // Atualiza o status do usuário
                $user->subscription = false;
                $user->payment_id = null;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Assinatura cancelada com sucesso',
                ]);
            } else {
                return response()->json(['error' => 'Token de autorização inválido'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao cancelar assinatura',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cria uma nova assinatura para o usuário
     */
    public function buy(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            if ($payload && $payload->get('sub')) {
                $userId = $payload->get('sub');
                $user = User::find($userId);

                if (!$user) {
                    return response()->json(['error' => 'Usuário não encontrado'], 404);
                }

                if ($user->subscription) {
                    return response()->json(['error' => 'Usuário já possui assinatura ativa'], 400);
                }

                // Busca o plano selecionado
                $plan = \App\Models\SubscriptionPlan::find($request->plan_id);

                if (!$plan || !$plan->active) {
                    return response()->json(['error' => 'Plano não disponível'], 400);
                }

                $stripe = new StripeClient(env('STRIPE_SECRET'));

                // Cria ou recupera o cliente no Stripe
                if (!$user->stripe_id) {
                    $customer = $stripe->customers->create([
                        'email' => $user->email,
                        'name' => $user->name,
                        'metadata' => [
                            'user_id' => $user->id,
                        ],
                    ]);
                    $user->stripe_id = $customer->id;
                    $user->save();
                } else {
                    $customer = $stripe->customers->retrieve($user->stripe_id);
                }

                // Cria a assinatura
                $subscriptionData = [
                    'customer' => $user->stripe_id,
                    'items' => [['price' => $plan->stripe_price_id]],
                    'payment_settings' => ['payment_method_types' => ['card']],
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                    ],
                ];

                // Aplica cupom se for primeiro uso
                if ($user->promocode == '1') {
                    $subscriptionData['coupon'] = env('STRIPE_VACLUB_COUPON');
                    $user->promocode = '0';
                }

                $subscription = $stripe->subscriptions->create($subscriptionData);

                // Atualiza o usuário
                $user->subscription = true;
                $user->payment_id = $subscription->id;
                $user->subscription_plan_id = $plan->id;
                
                // Adiciona giros de roleta baseado no plano
                $user->roulette_spins_available += $plan->roulette_spins_per_month;
                
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Assinatura criada com sucesso',
                    'subscription' => [
                        'id' => $subscription->id,
                        'status' => $subscription->status,
                        'current_period_end' => $subscription->current_period_end,
                        'plan' => $plan,
                    ],
                    'client_secret' => $subscription->latest_invoice->payment_intent->client_secret ?? null,
                ]);
            } else {
                return response()->json(['error' => 'Token de autorização inválido'], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar assinatura',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
