<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\Place;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class InfluencerController extends Controller
{
    /**
     * Listar influenciadores (para proprietários)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $minFollowers = $request->get('min_followers', 0);
            $orderBy = $request->get('order_by', 'videos_count'); // videos_count, views, likes

            $query = User::where('role', 'influenciador')
                ->withCount(['videos'])
                ->with(['videos' => function ($q) {
                    $q->latest()->take(3);
                }]);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Ordenação
            if ($orderBy === 'videos_count') {
                $query->orderBy('videos_count', 'desc');
            }

            $influencers = $query->paginate(20);

            // Adiciona estatísticas de cada influenciador
            $influencers->getCollection()->transform(function ($influencer) {
                $stats = Video::where('user_id', $influencer->id)
                    ->selectRaw('
                        COUNT(*) as total_videos,
                        SUM(views) as total_views,
                        SUM(likes) as total_likes,
                        SUM(shares) as total_shares
                    ')
                    ->first();

                $influencer->stats = $stats;
                $influencer->avg_views = $stats->total_videos > 0 
                    ? round($stats->total_views / $stats->total_videos) 
                    : 0;

                return $influencer;
            });

            return response()->json([
                'success' => true,
                'influencers' => $influencers,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar influenciadores'], 500);
        }
    }

    /**
     * Ver perfil de um influenciador
     */
    public function show($id)
    {
        try {
            $influencer = User::where('id', $id)
                ->where('role', 'influenciador')
                ->first();

            if (!$influencer) {
                return response()->json(['error' => 'Influenciador não encontrado'], 404);
            }

            // Estatísticas gerais
            $stats = Video::where('user_id', $id)
                ->selectRaw('
                    COUNT(*) as total_videos,
                    SUM(views) as total_views,
                    SUM(likes) as total_likes,
                    SUM(shares) as total_shares
                ')
                ->first();

            // Vídeos recentes
            $recentVideos = Video::where('user_id', $id)
                ->with('place')
                ->latest()
                ->take(9)
                ->get();

            // Vídeos mais populares
            $topVideos = Video::where('user_id', $id)
                ->with('place')
                ->orderBy('views', 'desc')
                ->take(5)
                ->get();

            // Lugares mencionados
            $placeMentions = Video::where('user_id', $id)
                ->whereNotNull('place_id')
                ->with('place')
                ->select('place_id')
                ->groupBy('place_id')
                ->get()
                ->pluck('place')
                ->unique('id');

            return response()->json([
                'success' => true,
                'influencer' => $influencer,
                'stats' => [
                    'total_videos' => $stats->total_videos ?? 0,
                    'total_views' => $stats->total_views ?? 0,
                    'total_likes' => $stats->total_likes ?? 0,
                    'total_shares' => $stats->total_shares ?? 0,
                    'avg_views' => $stats->total_videos > 0 
                        ? round($stats->total_views / $stats->total_videos) 
                        : 0,
                ],
                'recent_videos' => $recentVideos,
                'top_videos' => $topVideos,
                'place_mentions' => $placeMentions,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar perfil'], 500);
        }
    }

    /**
     * Buscar influenciadores por nicho/categoria
     */
    public function byCategory(Request $request, $categoryId)
    {
        try {
            // Busca influenciadores que já fizeram vídeos de lugares desta categoria
            $influencers = User::where('role', 'influenciador')
                ->whereHas('videos.place', function ($q) use ($categoryId) {
                    $q->where('categorie_id', $categoryId);
                })
                ->withCount(['videos'])
                ->get();

            // Adiciona estatísticas de cada influenciador
            $influencers->transform(function ($influencer) use ($categoryId) {
                $stats = Video::where('user_id', $influencer->id)
                    ->whereHas('place', function ($q) use ($categoryId) {
                        $q->where('categorie_id', $categoryId);
                    })
                    ->selectRaw('
                        COUNT(*) as videos_in_category,
                        SUM(views) as total_views,
                        SUM(likes) as total_likes
                    ')
                    ->first();

                $influencer->category_stats = $stats;
                return $influencer;
            });

            return response()->json([
                'success' => true,
                'influencers' => $influencers,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar influenciadores'], 500);
        }
    }

    /**
     * Iniciar contato com influenciador (proprietário)
     */
    public function contact(Request $request, $influencerId)
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

            if (!$user->isProprietario()) {
                return response()->json(['error' => 'Apenas proprietários podem contatar influenciadores'], 403);
            }

            $influencer = User::where('id', $influencerId)
                ->where('role', 'influenciador')
                ->first();

            if (!$influencer) {
                return response()->json(['error' => 'Influenciador não encontrado'], 404);
            }

            // Retorna informações de contato
            return response()->json([
                'success' => true,
                'influencer' => [
                    'id' => $influencer->id,
                    'name' => $influencer->name,
                    'email' => $influencer->email,
                ],
                'message' => 'Use a API de Chat para iniciar conversa ou envie uma proposta diretamente',
                'next_steps' => [
                    'create_chat' => '/api/chats (POST com influencer_id)',
                    'create_proposal' => '/api/proposals (POST)',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar contato'], 500);
        }
    }

    /**
     * Top influenciadores (ranking)
     */
    public function top(Request $request)
    {
        try {
            $metric = $request->get('metric', 'views'); // views, likes, videos
            $limit = $request->get('limit', 10);

            $influencers = User::where('role', 'influenciador')
                ->withCount(['videos'])
                ->get();

            // Calcula estatísticas e ordena
            $influencers = $influencers->map(function ($influencer) {
                $stats = Video::where('user_id', $influencer->id)
                    ->selectRaw('
                        COUNT(*) as total_videos,
                        SUM(views) as total_views,
                        SUM(likes) as total_likes,
                        SUM(shares) as total_shares
                    ')
                    ->first();

                $influencer->total_views = $stats->total_views ?? 0;
                $influencer->total_likes = $stats->total_likes ?? 0;
                $influencer->total_shares = $stats->total_shares ?? 0;
                $influencer->total_videos = $stats->total_videos ?? 0;

                return $influencer;
            });

            // Ordena pelo metric
            $influencers = $influencers->sortByDesc("total_{$metric}")
                ->take($limit)
                ->values();

            return response()->json([
                'success' => true,
                'ranking' => $influencers,
                'metric' => $metric,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar ranking'], 500);
        }
    }
}
