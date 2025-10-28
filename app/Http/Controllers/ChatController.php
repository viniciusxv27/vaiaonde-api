<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChatController extends Controller
{
    /**
     * Listar conversas do usuário
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

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $chats = [];

            // Se é influenciador
            if ($user->isInfluenciador()) {
                $chats = Chat::with(['place', 'lastMessage', 'proposal'])
                    ->where('influencer_id', $user->id)
                    ->orderBy('last_message_at', 'desc')
                    ->get();

                // Adiciona contador de não lidas
                $chats = $chats->map(function ($chat) use ($user) {
                    $chat->unread_count = $chat->unreadCount($user->id);
                    return $chat;
                });
            }

            // Se é proprietário
            if ($user->isProprietario()) {
                $ownedPlaces = Place::where('owner_id', $user->id)->pluck('id');
                
                $chats = Chat::with(['influencer', 'lastMessage', 'proposal'])
                    ->whereIn('place_id', $ownedPlaces)
                    ->orderBy('last_message_at', 'desc')
                    ->get();

                // Adiciona contador de não lidas
                $chats = $chats->map(function ($chat) use ($user) {
                    $chat->unread_count = $chat->unreadCount($user->id);
                    return $chat;
                });
            }

            return response()->json([
                'success' => true,
                'chats' => $chats,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar conversas'], 500);
        }
    }

    /**
     * Ver mensagens de uma conversa
     */
    public function messages(Request $request, $chatId)
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

            $chat = Chat::with(['influencer', 'place', 'proposal'])->find($chatId);
            if (!$chat) {
                return response()->json(['error' => 'Conversa não encontrada'], 404);
            }

            // Verifica permissão
            $isInfluencer = $chat->influencer_id == $user->id;
            $isOwner = $chat->place && $chat->place->owner_id == $user->id;

            if (!$isInfluencer && !$isOwner && !$user->is_admin) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            // Busca mensagens
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 50);

            $messages = ChatMessage::with('sender')
                ->where('chat_id', $chatId)
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            // Marca como lidas
            $chat->markAsRead($user->id);

            return response()->json([
                'success' => true,
                'chat' => $chat,
                'messages' => $messages,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar mensagens'], 500);
        }
    }

    /**
     * Enviar mensagem
     */
    public function send(Request $request, $chatId)
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
                'message' => 'required|string|max:1000',
                'type' => 'in:text,image,video,proposal',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $chat = Chat::with(['place'])->find($chatId);
            if (!$chat) {
                return response()->json(['error' => 'Conversa não encontrada'], 404);
            }

            // Verifica permissão
            $isInfluencer = $chat->influencer_id == $user->id;
            $isOwner = $chat->place && $chat->place->owner_id == $user->id;

            if (!$isInfluencer && !$isOwner) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            // Cria mensagem
            $message = ChatMessage::create([
                'chat_id' => $chatId,
                'sender_id' => $user->id,
                'message' => $request->message,
                'type' => $request->get('type', 'text'),
                'is_read' => false,
            ]);

            // Atualiza último timestamp
            $chat->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao enviar mensagem'], 500);
        }
    }

    /**
     * Criar nova conversa (influenciador + lugar)
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
                return response()->json(['error' => 'Apenas influenciadores podem iniciar conversas'], 403);
            }

            $validator = Validator::make($request->all(), [
                'place_id' => 'required|exists:place,id',
                'initial_message' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Verifica se já existe conversa
            $existingChat = Chat::where('influencer_id', $user->id)
                ->where('place_id', $request->place_id)
                ->first();

            if ($existingChat) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conversa já existe',
                    'chat' => $existingChat->load(['place']),
                ]);
            }

            // Cria nova conversa
            $chat = Chat::create([
                'influencer_id' => $user->id,
                'place_id' => $request->place_id,
                'last_message_at' => now(),
            ]);

            // Mensagem inicial se fornecida
            if ($request->initial_message) {
                ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $user->id,
                    'message' => $request->initial_message,
                    'type' => 'text',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Conversa criada',
                'chat' => $chat->load(['place']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao criar conversa'], 500);
        }
    }

    /**
     * Marcar conversa como lida
     */
    public function markRead(Request $request, $chatId)
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

            $chat = Chat::find($chatId);
            if (!$chat) {
                return response()->json(['error' => 'Conversa não encontrada'], 404);
            }

            // Verifica permissão
            $isInfluencer = $chat->influencer_id == $user->id;
            $isOwner = Place::where('id', $chat->place_id)
                ->where('owner_id', $user->id)
                ->exists();

            if (!$isInfluencer && !$isOwner) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            $chat->markAsRead($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Mensagens marcadas como lidas',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao marcar como lida'], 500);
        }
    }
}
