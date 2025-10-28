<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\User;
use App\Models\Place;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProposalController extends Controller
{
    /**
     * Criar proposta (influenciador)
     */
    public function create(Request $request)
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

            if (!$user->isInfluenciador()) {
                return response()->json(['error' => 'Apenas influenciadores podem criar propostas'], 403);
            }

            $validator = Validator::make($request->all(), [
                'place_id' => 'required|exists:place,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'deadline_days' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $proposal = Proposal::create([
                'influencer_id' => $user->id,
                'place_id' => $request->place_id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'deadline_days' => $request->deadline_days,
                'status' => 'pending',
            ]);

            // Criar ou recuperar chat
            $chat = Chat::firstOrCreate([
                'influencer_id' => $user->id,
                'place_id' => $request->place_id,
            ]);

            $chat->update(['proposal_id' => $proposal->id]);

            // Mensagem automática no chat
            ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'message' => "Nova proposta enviada: {$proposal->title} - R$ {$proposal->amount}",
                'type' => 'proposal',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proposta enviada com sucesso!',
                'proposal' => $proposal->load(['place']),
                'chat_id' => $chat->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar proposta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar propostas do influenciador
     */
    public function myProposals(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user || !$user->isInfluenciador()) {
                return response()->json(['error' => 'Apenas influenciadores'], 403);
            }

            $status = $request->get('status'); // pending, accepted, rejected, completed

            $query = Proposal::with(['place'])
                ->where('influencer_id', $user->id);

            if ($status) {
                $query->where('status', $status);
            }

            $proposals = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'proposals' => $proposals,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar propostas'], 500);
        }
    }

    /**
     * Listar propostas recebidas (proprietário)
     */
    public function placeProposals(Request $request, $placeId)
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

            $place = Place::find($placeId);
            if (!$place) {
                return response()->json(['error' => 'Lugar não encontrado'], 404);
            }

            // Verifica se é o dono do lugar
            if ($place->owner_id != $user->id && !$user->is_admin) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            $status = $request->get('status');

            $query = Proposal::with(['influencer'])
                ->where('place_id', $placeId);

            if ($status) {
                $query->where('status', $status);
            }

            $proposals = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'proposals' => $proposals,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar propostas'], 500);
        }
    }

    /**
     * Aceitar proposta (proprietário)
     */
    public function accept(Request $request, $id)
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

            $proposal = Proposal::with(['place', 'influencer'])->find($id);
            if (!$proposal) {
                return response()->json(['error' => 'Proposta não encontrada'], 404);
            }

            // Verifica se é o dono do lugar
            if ($proposal->place->owner_id != $user->id && !$user->is_admin) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            if (!$proposal->canBeAccepted()) {
                return response()->json(['error' => 'Proposta não pode ser aceita'], 400);
            }

            // Verifica saldo do proprietário
            if ($user->wallet_balance < $proposal->amount) {
                return response()->json(['error' => 'Saldo insuficiente na carteira'], 400);
            }

            // Aceita proposta
            $proposal->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Envia mensagem no chat
            $chat = Chat::where('influencer_id', $proposal->influencer_id)
                ->where('place_id', $proposal->place_id)
                ->first();

            if ($chat) {
                ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $user->id,
                    'message' => "Proposta aceita! Aguardando conclusão do trabalho.",
                    'type' => 'system',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Proposta aceita! O pagamento será processado após conclusão.',
                'proposal' => $proposal,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao aceitar proposta'], 500);
        }
    }

    /**
     * Rejeitar proposta
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

            $proposal = Proposal::with(['place'])->find($id);
            if (!$proposal) {
                return response()->json(['error' => 'Proposta não encontrada'], 404);
            }

            // Verifica se é o dono do lugar
            if ($proposal->place->owner_id != $user->id && !$user->is_admin) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            if (!$proposal->canBeAccepted()) {
                return response()->json(['error' => 'Proposta não pode ser rejeitada'], 400);
            }

            $proposal->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => 'Proposta rejeitada',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao rejeitar proposta'], 500);
        }
    }

    /**
     * Marcar proposta como concluída (influenciador)
     */
    public function complete(Request $request, $id)
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

            $proposal = Proposal::with(['place', 'influencer'])->find($id);
            if (!$proposal) {
                return response()->json(['error' => 'Proposta não encontrada'], 404);
            }

            // Verifica se é o influenciador da proposta
            if ($proposal->influencer_id != $user->id) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            if (!$proposal->canBeCompleted()) {
                return response()->json(['error' => 'Proposta precisa estar aceita'], 400);
            }

            $proposal->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Processa pagamento via Kirvano/Stripe
            $paymentResult = $this->processPayment($proposal);

            if (!$paymentResult['success']) {
                $proposal->update(['status' => 'accepted']); // Reverte
                return response()->json(['error' => $paymentResult['message']], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Proposta concluída e pagamento processado!',
                'proposal' => $proposal,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao completar proposta'], 500);
        }
    }

    /**
     * Processa pagamento da proposta
     */
    private function processPayment($proposal)
    {
        try {
            $owner = User::find($proposal->place->owner_id);
            $influencer = $proposal->influencer;

            if ($owner->wallet_balance < $proposal->amount) {
                return [
                    'success' => false,
                    'message' => 'Proprietário sem saldo suficiente'
                ];
            }

            // Transfere saldo
            $owner->decrement('wallet_balance', $proposal->amount);
            $influencer->increment('wallet_balance', $proposal->amount);

            // Registra transações
            \App\Models\Transaction::create([
                'user_id' => $owner->id,
                'type' => 'transfer_out',
                'amount' => $proposal->amount,
                'balance_before' => $owner->wallet_balance + $proposal->amount,
                'balance_after' => $owner->wallet_balance,
                'description' => "Pagamento proposta: {$proposal->title}",
                'related_user_id' => $influencer->id,
                'proposal_id' => $proposal->id,
                'status' => 'completed',
            ]);

            \App\Models\Transaction::create([
                'user_id' => $influencer->id,
                'type' => 'transfer_in',
                'amount' => $proposal->amount,
                'balance_before' => $influencer->wallet_balance - $proposal->amount,
                'balance_after' => $influencer->wallet_balance,
                'description' => "Recebimento proposta: {$proposal->title}",
                'related_user_id' => $owner->id,
                'proposal_id' => $proposal->id,
                'status' => 'completed',
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ];
        }
    }
}
