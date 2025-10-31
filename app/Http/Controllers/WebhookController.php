<?php

namespace App\Http\Controllers;

use App\Models\AbacatePayBilling;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Webhook do AbacatePay para confirmação de pagamentos
     */
    public function abacatepay(Request $request)
    {
        try {
            // Log do webhook recebido
            Log::info('AbacatePay Webhook Received', $request->all());

            // Validar assinatura (se configurado no AbacatePay)
            // $signature = $request->header('X-AbacatePay-Signature');
            // if (!$this->verifySignature($signature, $request->getContent())) {
            //     return response()->json(['error' => 'Invalid signature'], 401);
            // }

            $data = $request->all();

            // Verifica se é uma notificação de billing
            if (!isset($data['id']) || !isset($data['status'])) {
                Log::warning('AbacatePay Webhook: Missing required fields');
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            $billingId = $data['id'];
            $status = $data['status'];

            // Busca o billing no banco de dados
            $billing = AbacatePayBilling::where('billing_id', $billingId)->first();

            if (!$billing) {
                Log::warning("AbacatePay Webhook: Billing not found: {$billingId}");
                return response()->json(['error' => 'Billing not found'], 404);
            }

            // Atualiza status do billing
            $billing->status = $status;
            $billing->metadata = array_merge($billing->metadata ?? [], $data);

            // Se pagamento foi confirmado
            if ($status === 'PAID' && $billing->status !== 'paid') {
                $billing->markAsPaid();

                // Credita saldo na carteira do usuário
                $user = $billing->user;
                $balanceBefore = $user->wallet_balance;
                $user->wallet_balance += $billing->amount;
                $user->save();

                // Registra a transação
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $billing->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $user->wallet_balance,
                    'description' => 'Depósito via PIX - AbacatePay',
                    'payment_method' => 'pix',
                    'status' => 'completed'
                ]);

                Log::info("AbacatePay: Payment confirmed for billing {$billingId}, user {$user->id} credited with {$billing->amount}");
            }

            // Status de falha
            if (in_array($status, ['CANCELLED', 'EXPIRED', 'REFUNDED'])) {
                $billing->status = 'failed';
                $billing->save();

                Log::info("AbacatePay: Billing {$billingId} marked as failed: {$status}");
            }

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            Log::error('AbacatePay Webhook Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verifica a assinatura do webhook (opcional)
     */
    private function verifySignature($signature, $payload)
    {
        $secret = config('abacatepay.webhook_secret');
        
        if (!$secret) {
            return true; // Se não tiver secret configurado, aceita
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
}
