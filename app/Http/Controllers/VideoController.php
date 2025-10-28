<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoInteraction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class VideoController extends Controller
{
    /**
     * Feed de vídeos tipo TikTok
     */
    public function feed(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $user = null;

        // Verifica autenticação
        $token = $request->bearerToken();
        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                if ($payload && $payload->get('sub')) {
                    $user = User::find($payload->get('sub'));
                }
            } catch (\Exception $e) {
                // Token inválido, continua como anônimo
            }
        }

        // Usuário comum (não autenticado) vê apenas vídeos públicos
        $query = Video::with(['user', 'place'])
            ->where('active', true)
            ->orderBy('created_at', 'desc');

        // Se usuário é assinante, vê todos os vídeos
        // Se não for assinante, vê apenas vídeos não patrocinados ou vídeos gerais
        if (!$user || !$user->isAssinante()) {
            // Usuários não assinantes podem ver o feed mas com limitações
        }

        $videos = $query->paginate($perPage);

        // Adiciona informação se usuário deu like
        if ($user) {
            $videos->getCollection()->transform(function ($video) use ($user) {
                $video->is_liked = $video->isLikedBy($user->id);
                return $video;
            });
        }

        return response()->json([
            'success' => true,
            'videos' => $videos,
        ]);
    }

    /**
     * Upload de vídeo (apenas influenciadores)
     */
    public function upload(Request $request)
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
                return response()->json(['error' => 'Apenas influenciadores podem fazer upload de vídeos'], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'video_url' => 'required|url',
                'thumbnail_url' => 'nullable|url',
                'duration' => 'required|integer',
                'place_id' => 'nullable|exists:place,id',
                'is_sponsored' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $video = Video::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'video_url' => $request->video_url,
                'thumbnail_url' => $request->thumbnail_url,
                'duration' => $request->duration,
                'place_id' => $request->place_id,
                'is_sponsored' => $request->is_sponsored ?? false,
                'active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo publicado com sucesso!',
                'video' => $video->load(['user', 'place']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao fazer upload do vídeo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detalhes de um vídeo
     */
    public function show(Request $request, $id)
    {
        $video = Video::with(['user', 'place'])->find($id);

        if (!$video) {
            return response()->json(['error' => 'Vídeo não encontrado'], 404);
        }

        // Verifica autenticação
        $user = null;
        $token = $request->bearerToken();
        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                if ($payload && $payload->get('sub')) {
                    $user = User::find($payload->get('sub'));
                    $video->is_liked = $video->isLikedBy($user->id);
                }
            } catch (\Exception $e) {
                // Token inválido
            }
        }

        return response()->json([
            'success' => true,
            'video' => $video,
        ]);
    }

    /**
     * Registrar visualização
     */
    public function view(Request $request, $id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['error' => 'Vídeo não encontrado'], 404);
        }

        $token = $request->bearerToken();
        if (!$token) {
            // Incrementa view mesmo sem autenticação
            $video->incrementViews();
            return response()->json(['success' => true]);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if ($user) {
                // Registra visualização única por usuário
                VideoInteraction::firstOrCreate([
                    'video_id' => $video->id,
                    'user_id' => $user->id,
                    'type' => 'view',
                ]);

                $video->incrementViews();
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao registrar visualização'], 500);
        }
    }

    /**
     * Like/Unlike
     */
    public function like(Request $request, $id)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Autenticação necessária'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => 'Vídeo não encontrado'], 404);
            }

            // Verifica se já deu like
            $interaction = VideoInteraction::where('video_id', $video->id)
                ->where('user_id', $user->id)
                ->where('type', 'like')
                ->first();

            if ($interaction) {
                // Remove like
                $interaction->delete();
                $video->decrementLikes();
                $liked = false;
            } else {
                // Adiciona like
                VideoInteraction::create([
                    'video_id' => $video->id,
                    'user_id' => $user->id,
                    'type' => 'like',
                ]);
                $video->incrementLikes();
                $liked = true;
            }

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $video->fresh()->likes_count,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao processar like'], 500);
        }
    }

    /**
     * Compartilhar
     */
    public function share(Request $request, $id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json(['error' => 'Vídeo não encontrado'], 404);
        }

        $token = $request->bearerToken();
        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                $userId = $payload->get('sub');
                $user = User::find($userId);

                if ($user) {
                    VideoInteraction::firstOrCreate([
                        'video_id' => $video->id,
                        'user_id' => $user->id,
                        'type' => 'share',
                    ]);
                }
            } catch (\Exception $e) {
                // Continua sem erro
            }
        }

        $video->incrementShares();

        return response()->json([
            'success' => true,
            'share_url' => $video->share_url,
            'shares_count' => $video->fresh()->shares_count,
        ]);
    }

    /**
     * Vídeos de um influenciador
     */
    public function influencerVideos(Request $request, $influencerId)
    {
        $perPage = $request->get('per_page', 20);

        $influencer = User::find($influencerId);
        if (!$influencer || !$influencer->isInfluenciador()) {
            return response()->json(['error' => 'Influenciador não encontrado'], 404);
        }

        $videos = Video::with(['place'])
            ->where('user_id', $influencer->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'influencer' => [
                'id' => $influencer->id,
                'name' => $influencer->name,
                'email' => $influencer->email,
            ],
            'videos' => $videos,
        ]);
    }

    /**
     * Meus vídeos (influenciador)
     */
    public function myVideos(Request $request)
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
                return response()->json(['error' => 'Apenas influenciadores têm vídeos'], 403);
            }

            $perPage = $request->get('per_page', 20);
            $videos = Video::with(['place'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'videos' => $videos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar vídeos'], 500);
        }
    }

    /**
     * Deletar vídeo
     */
    public function destroy(Request $request, $id)
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

            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => 'Vídeo não encontrado'], 404);
            }

            // Apenas o dono do vídeo pode deletar
            if ($video->user_id != $user->id && !$user->is_admin) {
                return response()->json(['error' => 'Sem permissão para deletar este vídeo'], 403);
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vídeo deletado com sucesso',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao deletar vídeo'], 500);
        }
    }
}
