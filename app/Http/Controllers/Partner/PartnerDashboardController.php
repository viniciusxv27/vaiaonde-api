<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\Video;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class PartnerDashboardController extends Controller
{
    /**
     * Dashboard do parceiro com métricas gerais
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
                return response()->json(['error' => 'Acesso negado. Apenas proprietários.'], 403);
            }

            // Buscar lugares do proprietário
            $places = Place::where('owner_id', $user->id)->get();
            $placeIds = $places->pluck('id');

            if ($placeIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Você ainda não possui estabelecimentos cadastrados.',
                    'stats' => [
                        'total_places' => 0,
                        'total_videos_mentions' => 0,
                        'total_views' => 0,
                        'total_proposals' => 0,
                        'active_contracts' => 0,
                        'wallet_balance' => $user->wallet_balance,
                    ],
                    'places' => [],
                ]);
            }

            // Estatísticas gerais
            $totalVideoMentions = Video::whereIn('place_id', $placeIds)->count();
            $totalViews = Video::whereIn('place_id', $placeIds)->sum('views');
            $totalLikes = Video::whereIn('place_id', $placeIds)->sum('likes');
            $totalShares = Video::whereIn('place_id', $placeIds)->sum('shares');

            $totalProposals = Proposal::whereIn('place_id', $placeIds)->count();
            $activeContracts = Proposal::whereIn('place_id', $placeIds)
                ->where('status', 'accepted')
                ->count();
            $completedContracts = Proposal::whereIn('place_id', $placeIds)
                ->where('status', 'completed')
                ->count();

            // Estatísticas por lugar
            $placesWithStats = $places->map(function ($place) {
                $videoStats = Video::where('place_id', $place->id)
                    ->selectRaw('COUNT(*) as total, SUM(views) as views, SUM(likes) as likes, SUM(shares) as shares')
                    ->first();

                $proposalStats = Proposal::where('place_id', $place->id)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
                    ')
                    ->first();

                $place->stats = [
                    'videos' => $videoStats->total ?? 0,
                    'views' => $videoStats->views ?? 0,
                    'likes' => $videoStats->likes ?? 0,
                    'shares' => $videoStats->shares ?? 0,
                    'proposals_total' => $proposalStats->total ?? 0,
                    'proposals_active' => $proposalStats->active ?? 0,
                    'proposals_completed' => $proposalStats->completed ?? 0,
                ];

                return $place;
            });

            // Vídeos recentes mencionando meus lugares
            $recentVideos = Video::with(['user', 'place'])
                ->whereIn('place_id', $placeIds)
                ->latest()
                ->take(5)
                ->get();

            // Propostas recentes
            $recentProposals = Proposal::with(['influencer', 'place'])
                ->whereIn('place_id', $placeIds)
                ->latest()
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_places' => $places->count(),
                    'total_videos_mentions' => $totalVideoMentions,
                    'total_views' => $totalViews,
                    'total_likes' => $totalLikes,
                    'total_shares' => $totalShares,
                    'total_proposals' => $totalProposals,
                    'active_contracts' => $activeContracts,
                    'completed_contracts' => $completedContracts,
                    'wallet_balance' => $user->wallet_balance,
                ],
                'places' => $placesWithStats,
                'recent_videos' => $recentVideos,
                'recent_proposals' => $recentProposals,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao carregar dashboard',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métricas detalhadas de um estabelecimento específico
     */
    public function placeMetrics(Request $request, $placeId)
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

            $place = Place::find($placeId);
            if (!$place) {
                return response()->json(['error' => 'Estabelecimento não encontrado'], 404);
            }

            // Verifica se é dono
            if ($place->owner_id != $user->id && !$user->is_admin) {
                return response()->json(['error' => 'Você não é o dono deste estabelecimento'], 403);
            }

            // Métricas de vídeos
            $videoStats = Video::where('place_id', $placeId)
                ->selectRaw('
                    COUNT(*) as total_videos,
                    SUM(views) as total_views,
                    SUM(likes) as total_likes,
                    SUM(shares) as total_shares,
                    AVG(views) as avg_views
                ')
                ->first();

            // Vídeos por período (últimos 30 dias)
            $videosPerDay = Video::where('place_id', $placeId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(views) as views')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            // Top vídeos
            $topVideos = Video::with('user')
                ->where('place_id', $placeId)
                ->orderBy('views', 'desc')
                ->take(10)
                ->get();

            // Influenciadores que mencionaram
            $topInfluencers = Video::where('place_id', $placeId)
                ->with('user')
                ->select('user_id')
                ->selectRaw('COUNT(*) as video_count, SUM(views) as total_views')
                ->groupBy('user_id')
                ->orderBy('total_views', 'desc')
                ->take(10)
                ->get();

            // Propostas
            $proposalStats = Proposal::where('place_id', $placeId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_spent
                ')
                ->first();

            return response()->json([
                'success' => true,
                'place' => $place,
                'video_stats' => [
                    'total_videos' => $videoStats->total_videos ?? 0,
                    'total_views' => $videoStats->total_views ?? 0,
                    'total_likes' => $videoStats->total_likes ?? 0,
                    'total_shares' => $videoStats->total_shares ?? 0,
                    'avg_views' => round($videoStats->avg_views ?? 0),
                ],
                'videos_per_day' => $videosPerDay,
                'top_videos' => $topVideos,
                'top_influencers' => $topInfluencers,
                'proposal_stats' => [
                    'total' => $proposalStats->total ?? 0,
                    'pending' => $proposalStats->pending ?? 0,
                    'accepted' => $proposalStats->accepted ?? 0,
                    'completed' => $proposalStats->completed ?? 0,
                    'rejected' => $proposalStats->rejected ?? 0,
                    'total_spent' => $proposalStats->total_spent ?? 0,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao carregar métricas'], 500);
        }
    }

    /**
     * Listar todos os vídeos onde o estabelecimento foi mencionado
     */
    public function placeVideos(Request $request, $placeId)
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

            $place = Place::find($placeId);
            if (!$place) {
                return response()->json(['error' => 'Estabelecimento não encontrado'], 404);
            }

            if ($place->owner_id != $user->id && !$user->is_admin) {
                return response()->json(['error' => 'Sem permissão'], 403);
            }

            $orderBy = $request->get('order_by', 'created_at'); // created_at, views, likes
            $order = $request->get('order', 'desc');

            $videos = Video::with(['user'])
                ->where('place_id', $placeId)
                ->orderBy($orderBy, $order)
                ->paginate(20);

            return response()->json([
                'success' => true,
                'place' => $place,
                'videos' => $videos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar vídeos'], 500);
        }
    }

    /**
     * Listar contratos ativos (propostas aceitas)
     */
    public function activeContracts(Request $request)
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

            $contracts = Proposal::with(['influencer', 'place'])
                ->whereIn('place_id', $placeIds)
                ->where('status', 'accepted')
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'contracts' => $contracts,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar contratos'], 500);
        }
    }
}
