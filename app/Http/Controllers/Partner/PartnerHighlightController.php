<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\PlaceHighlight;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Stripe\Stripe;
use Stripe\Charge;

class PartnerHighlightController extends Controller
{
    /**
     * Listar destaques do proprietário
     */
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->isProprietario()) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $placeIds = Place::where('owner_id', $user->id)->pluck('id');

            $highlights = PlaceHighlight::with('place')
                ->whereIn('place_id', $placeIds)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'highlights' => $highlights,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar destaques'], 500);
        }
    }

    /**
     * Pagar destaque para um estabelecimento (30 dias - R$ 39,90)
     */
    public function purchase(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->isProprietario()) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $validator = Validator::make($request->all(), [
                'place_id' => 'required|exists:place,id',
                'payment_method' => 'required|in:wallet,card',
                'payment_method_id' => 'required_if:payment_method,card|string', // Stripe Payment Method
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $place = Place::find($request->place_id);
            
            // Verifica se é o dono
            if ($place->owner_id != $user->id) {
                return response()->json(['error' => 'Você não é o dono deste estabelecimento'], 403);
            }

            // Verifica se já tem destaque ativo
            $activeHighlight = PlaceHighlight::where('place_id', $place->id)
                ->active()
                ->first();

            if ($activeHighlight) {
                return response()->json([
                    'error' => 'Este estabelecimento já possui um destaque ativo até ' . $activeHighlight->end_date->format('d/m/Y')
                ], 400);
            }

            $amount = 39.90;

            // Cria o destaque
            $highlight = PlaceHighlight::create([
                'place_id' => $place->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Processa pagamento
            if ($request->payment_method === 'wallet') {
                // Pagar com saldo da carteira
                if ($user->wallet_balance < $amount) {
                    $highlight->delete();
                    return response()->json(['error' => 'Saldo insuficiente na carteira'], 400);
                }

                $user->decrement('wallet_balance', $amount);

                // Registra transação
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'highlight_purchase',
                    'amount' => $amount,
                    'balance_before' => $user->wallet_balance + $amount,
                    'balance_after' => $user->wallet_balance,
                    'description' => "Destaque de 30 dias - {$place->name}",
                    'payment_method' => 'wallet',
                    'status' => 'completed',
                ]);

                // Ativa destaque
                $highlight->activate();

                return response()->json([
                    'success' => true,
                    'message' => 'Destaque ativado com sucesso! Válido por 30 dias.',
                    'highlight' => $highlight->fresh(),
                    'new_balance' => $user->wallet_balance,
                ]);

            } elseif ($request->payment_method === 'card') {
                // Pagar com cartão via Stripe
                Stripe::setApiKey(env('STRIPE_SECRET'));

                try {
                    $charge = Charge::create([
                        'amount' => $amount * 100, // Centavos
                        'currency' => 'brl',
                        'payment_method' => $request->payment_method_id,
                        'confirm' => true,
                        'description' => "Destaque 30 dias - {$place->name}",
                    ]);

                    if ($charge->status === 'succeeded') {
                        $highlight->update([
                            'stripe_charge_id' => $charge->id,
                        ]);
                        $highlight->activate();

                        // Registra transação
                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'highlight_purchase',
                            'amount' => $amount,
                            'balance_before' => $user->wallet_balance,
                            'balance_after' => $user->wallet_balance,
                            'description' => "Destaque de 30 dias - {$place->name}",
                            'payment_method' => 'card',
                            'stripe_charge_id' => $charge->id,
                            'status' => 'completed',
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Destaque ativado com sucesso! Válido por 30 dias.',
                            'highlight' => $highlight->fresh(),
                        ]);
                    } else {
                        $highlight->delete();
                        return response()->json(['error' => 'Pagamento não autorizado'], 400);
                    }

                } catch (\Exception $e) {
                    $highlight->delete();
                    return response()->json([
                        'error' => 'Erro ao processar pagamento',
                        'message' => $e->getMessage()
                    ], 400);
                }
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao comprar destaque',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar destaque
     */
    public function cancel(Request $request, $highlightId)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->isProprietario()) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $highlight = PlaceHighlight::find($highlightId);
            if (!$highlight) {
                return response()->json(['error' => 'Destaque não encontrado'], 404);
            }

            // Verifica se é o dono
            if ($highlight->user_id != $user->id) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            if ($highlight->status !== 'active') {
                return response()->json(['error' => 'Destaque não está ativo'], 400);
            }

            $highlight->update([
                'status' => 'cancelled',
                'is_active' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Destaque cancelado',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao cancelar destaque'], 500);
        }
    }

    /**
     * Listar lugares em destaque (público)
     */
    public function highlighted(Request $request)
    {
        try {
            $highlights = PlaceHighlight::with('place')
                ->active()
                ->latest()
                ->get();

            $places = $highlights->map(function ($highlight) {
                return $highlight->place;
            });

            return response()->json([
                'success' => true,
                'highlighted_places' => $places,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar destaques'], 500);
        }
    }
}
