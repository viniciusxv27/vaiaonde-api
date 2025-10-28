<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminTransactionController extends Controller
{
    /**
     * Listar todas as transações
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

            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $type = $request->get('type');
            $status = $request->get('status');
            $userId = $request->get('user_id');

            $query = Transaction::with(['user', 'relatedUser', 'proposal']);

            if ($type) {
                $query->where('type', $type);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $transactions = $query->latest()->paginate(50);

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao listar transações'], 500);
        }
    }

    /**
     * Aprovar transação pendente
     */
    public function approve(Request $request, $id)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $transaction = Transaction::find($id);
            
            if (!$transaction) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            if ($transaction->status !== 'pending') {
                return response()->json(['error' => 'Transação não está pendente'], 400);
            }

            $transaction->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Transação aprovada',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao aprovar transação'], 500);
        }
    }

    /**
     * Rejeitar transação
     */
    public function reject(Request $request, $id)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $transaction = Transaction::find($id);
            
            if (!$transaction) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            if ($transaction->status !== 'pending') {
                return response()->json(['error' => 'Transação não está pendente'], 400);
            }

            // Se for saque, devolve saldo
            if ($transaction->type === 'withdrawal') {
                $transactionUser = $transaction->user;
                $transactionUser->increment('wallet_balance', $transaction->amount);
            }

            $transaction->update(['status' => 'failed']);

            return response()->json([
                'success' => true,
                'message' => 'Transação rejeitada',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao rejeitar transação'], 500);
        }
    }

    /**
     * Estatísticas financeiras
     */
    public function stats(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $totalDeposits = Transaction::where('type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount');

            $totalWithdrawals = Transaction::where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount');

            $totalTransfers = Transaction::where('type', 'transfer_out')
                ->where('status', 'completed')
                ->sum('amount');

            $pendingWithdrawals = Transaction::where('type', 'withdrawal')
                ->where('status', 'pending')
                ->count();

            $totalWalletBalance = User::whereIn('role', ['proprietario', 'influenciador'])
                ->sum('wallet_balance');

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_deposits' => $totalDeposits,
                    'total_withdrawals' => $totalWithdrawals,
                    'total_transfers' => $totalTransfers,
                    'pending_withdrawals' => $pendingWithdrawals,
                    'total_wallet_balance' => $totalWalletBalance,
                    'platform_revenue' => $totalDeposits - $totalWithdrawals,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar estatísticas'], 500);
        }
    }
}
