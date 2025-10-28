<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminProposalController extends Controller
{
    /**
     * Listar todas as propostas
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

            $status = $request->get('status');
            $influencerId = $request->get('influencer_id');
            $placeId = $request->get('place_id');

            $query = Proposal::with(['influencer', 'place']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($influencerId) {
                $query->where('influencer_id', $influencerId);
            }

            if ($placeId) {
                $query->where('place_id', $placeId);
            }

            $proposals = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'proposals' => $proposals,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao listar propostas'], 500);
        }
    }

    /**
     * Ver detalhes de uma proposta
     */
    public function show(Request $request, $id)
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

            $proposal = Proposal::with(['influencer', 'place', 'transactions'])->find($id);
            
            if (!$proposal) {
                return response()->json(['error' => 'Proposta não encontrada'], 404);
            }

            return response()->json([
                'success' => true,
                'proposal' => $proposal,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar proposta'], 500);
        }
    }

    /**
     * Cancelar proposta (admin)
     */
    public function cancel(Request $request, $id)
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

            $proposal = Proposal::find($id);
            
            if (!$proposal) {
                return response()->json(['error' => 'Proposta não encontrada'], 404);
            }

            $proposal->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Proposta cancelada',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao cancelar proposta'], 500);
        }
    }

    /**
     * Estatísticas de propostas
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

            $total = Proposal::count();
            $pending = Proposal::where('status', 'pending')->count();
            $accepted = Proposal::where('status', 'accepted')->count();
            $completed = Proposal::where('status', 'completed')->count();
            $rejected = Proposal::where('status', 'rejected')->count();
            $cancelled = Proposal::where('status', 'cancelled')->count();

            $totalValue = Proposal::where('status', 'completed')->sum('amount');
            $avgValue = Proposal::where('status', 'completed')->avg('amount');

            return response()->json([
                'success' => true,
                'stats' => [
                    'total' => $total,
                    'pending' => $pending,
                    'accepted' => $accepted,
                    'completed' => $completed,
                    'rejected' => $rejected,
                    'cancelled' => $cancelled,
                    'total_value_completed' => $totalValue,
                    'avg_value' => round($avgValue, 2),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar estatísticas'], 500);
        }
    }
}
