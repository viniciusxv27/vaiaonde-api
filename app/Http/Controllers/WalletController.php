<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Stripe\Stripe;
use Stripe\Charge;

class WalletController extends Controller
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

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$user->hasWalletAccess()) {
                return response()->json(['error' => 'Acesso à carteira não disponível'], 403);
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

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$user->hasWalletAccess()) {
                return response()->json(['error' => 'Acesso à carteira não disponível'], 403);
            }

            $type = $request->get('type'); // deposit, withdrawal, transfer_in, transfer_out
            $status = $request->get('status'); // pending, completed, failed

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

    /**
     * Depositar via cartão (Stripe)
     */
    public function depositCard(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$user->hasWalletAccess()) {
                return response()->json(['error' => 'Acesso à carteira não disponível'], 403);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:10|max:10000',
                'payment_method_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Configura Stripe
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Cria transação pendente
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'balance_before' => $user->wallet_balance,
                'balance_after' => $user->wallet_balance + $request->amount,
                'description' => 'Depósito via cartão',
                'payment_method' => 'card',
                'status' => 'pending',
            ]);

            try {
                // Processa pagamento
                $charge = Charge::create([
                    'amount' => $request->amount * 100, // Centavos
                    'currency' => 'brl',
                    'payment_method' => $request->payment_method_id,
                    'confirm' => true,
                    'description' => "Depósito carteira - User {$user->id}",
                ]);

                if ($charge->status === 'succeeded') {
                    // Atualiza saldo
                    $user->increment('wallet_balance', $request->amount);

                    // Atualiza transação
                    $transaction->update([
                        'status' => 'completed',
                        'stripe_charge_id' => $charge->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Depósito realizado com sucesso!',
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

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao realizar depósito'], 500);
        }
    }

    /**
     * Gerar QR Code PIX para depósito
     */
    public function depositPix(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$user->hasWalletAccess()) {
                return response()->json(['error' => 'Acesso à carteira não disponível'], 403);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:10|max:10000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Cria transação pendente
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'balance_before' => $user->wallet_balance,
                'balance_after' => $user->wallet_balance + $request->amount,
                'description' => 'Depósito via PIX',
                'payment_method' => 'pix',
                'status' => 'pending',
            ]);

            // Aqui você integraria com um gateway PIX real
            // Por enquanto, retornamos dados simulados
            $pixData = [
                'qr_code' => 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode("pix_code_{$transaction->id}"),
                'pix_code' => "00020126580014BR.GOV.BCB.PIX0136{$transaction->id}520400005303986540{$request->amount}5802BR5913VAIAONDE6009SAO PAULO62070503***6304",
                'expiration' => now()->addMinutes(15)->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'QR Code gerado! Aguardando pagamento...',
                'transaction_id' => $transaction->id,
                'pix' => $pixData,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar PIX'], 500);
        }
    }

    /**
     * Confirmar pagamento PIX (webhook/manual)
     */
    public function confirmPix(Request $request, $transactionId)
    {
        try {
            $transaction = Transaction::find($transactionId);
            if (!$transaction) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            if ($transaction->status !== 'pending') {
                return response()->json(['error' => 'Transação já processada'], 400);
            }

            $user = $transaction->user;

            // Atualiza saldo
            $user->increment('wallet_balance', $transaction->amount);

            // Atualiza transação
            $transaction->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Depósito confirmado!',
                'new_balance' => $user->wallet_balance,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao confirmar PIX'], 500);
        }
    }

    /**
     * Sacar para chave PIX
     */
    public function withdraw(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$user->hasWalletAccess()) {
                return response()->json(['error' => 'Acesso à carteira não disponível'], 403);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:20',
                'pix_key' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verifica saldo
            if ($user->wallet_balance < $request->amount) {
                return response()->json(['error' => 'Saldo insuficiente'], 400);
            }

            // Cria transação
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'balance_before' => $user->wallet_balance,
                'balance_after' => $user->wallet_balance - $request->amount,
                'description' => 'Saque via PIX',
                'payment_method' => 'pix',
                'pix_key' => $request->pix_key,
                'status' => 'pending',
            ]);

            // Aqui você processaria o saque com gateway PIX real
            // Por enquanto, aprovamos automaticamente
            $user->decrement('wallet_balance', $request->amount);
            $transaction->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Saque solicitado com sucesso! Processamento em até 24h.',
                'new_balance' => $user->wallet_balance,
                'transaction' => $transaction,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao solicitar saque'], 500);
        }
    }

    /**
     * Atualizar chave PIX
     */
    public function updatePixKey(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'pix_key' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user->update(['pix_key' => $request->pix_key]);

            return response()->json([
                'success' => true,
                'message' => 'Chave PIX atualizada',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar chave PIX'], 500);
        }
    }
}
