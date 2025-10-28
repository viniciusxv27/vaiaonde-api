<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Stripe\Stripe;
use Stripe\Charge;

class PartnerWalletController extends Controller
{
    /**
     * Ver saldo da carteira
     */
    public function balance(Request $request)
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

            return response()->json([
                'success' => true,
                'balance' => $user->wallet_balance,
                'pix_key' => $user->pix_key,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar saldo'], 500);
        }
    }

    /**
     * Adicionar saldo via cartão
     */
    public function addBalance(Request $request)
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
                'amount' => 'required|numeric|min:10|max:10000',
                'payment_method' => 'required|in:card,pix',
                'payment_method_id' => 'required_if:payment_method,card|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $amount = $request->amount;

            if ($request->payment_method === 'card') {
                // Pagamento via cartão (Stripe)
                Stripe::setApiKey(env('STRIPE_SECRET'));

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $user->wallet_balance,
                    'balance_after' => $user->wallet_balance + $amount,
                    'description' => 'Recarga de saldo via cartão',
                    'payment_method' => 'card',
                    'status' => 'pending',
                ]);

                try {
                    $charge = Charge::create([
                        'amount' => $amount * 100,
                        'currency' => 'brl',
                        'payment_method' => $request->payment_method_id,
                        'confirm' => true,
                        'description' => "Recarga saldo - User {$user->id}",
                    ]);

                    if ($charge->status === 'succeeded') {
                        $user->increment('wallet_balance', $amount);

                        $transaction->update([
                            'status' => 'completed',
                            'stripe_charge_id' => $charge->id,
                            'balance_after' => $user->wallet_balance,
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Saldo adicionado com sucesso!',
                            'new_balance' => $user->wallet_balance,
                            'transaction' => $transaction,
                        ]);
                    } else {
                        $transaction->update(['status' => 'failed']);
                        return response()->json(['error' => 'Pagamento não autorizado'], 400);
                    }

                } catch (\Exception $e) {
                    $transaction->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                    
                    return response()->json([
                        'error' => 'Erro ao processar pagamento',
                        'message' => $e->getMessage()
                    ], 400);
                }

            } elseif ($request->payment_method === 'pix') {
                // PIX - Gerar QR Code
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $user->wallet_balance,
                    'balance_after' => $user->wallet_balance + $amount,
                    'description' => 'Recarga de saldo via PIX',
                    'payment_method' => 'pix',
                    'status' => 'pending',
                ]);

                $pixData = [
                    'qr_code' => 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode("pix_code_{$transaction->id}"),
                    'pix_code' => "00020126580014BR.GOV.BCB.PIX0136{$transaction->id}520400005303986540{$amount}5802BR5913VAIAONDE6009SAO PAULO62070503***6304",
                    'expiration' => now()->addMinutes(15)->toISOString(),
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'QR Code gerado! Aguardando pagamento...',
                    'transaction_id' => $transaction->id,
                    'pix' => $pixData,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao adicionar saldo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histórico de transações
     */
    public function transactions(Request $request)
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

            $type = $request->get('type');
            $status = $request->get('status');

            $query = Transaction::with(['relatedUser', 'proposal'])
                ->where('user_id', $user->id);

            if ($type) {
                $query->where('type', $type);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar transações'], 500);
        }
    }
}
