<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminVideoController extends Controller
{
    /**
     * Listar todos os vídeos
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

            $search = $request->get('search');
            $influencerId = $request->get('influencer_id');
            $placeId = $request->get('place_id');
            $isSponsored = $request->get('is_sponsored');

            $query = Video::with(['user', 'place']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($influencerId) {
                $query->where('user_id', $influencerId);
            }

            if ($placeId) {
                $query->where('place_id', $placeId);
            }

            if ($isSponsored !== null) {
                $query->where('is_sponsored', $isSponsored);
            }

            $videos = $query->latest()->paginate(20);

            return response()->json([
                'success' => true,
                'videos' => $videos,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao listar vídeos'], 500);
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

            if (!$user || !$user->is_admin) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }

            $video = Video::find($id);
            if (!$video) {
                return response()->json(['error' => 'Vídeo não encontrado'], 404);
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

    /**
     * Estatísticas gerais de vídeos
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

            $totalVideos = Video::count();
            $totalViews = Video::sum('views');
            $totalLikes = Video::sum('likes');
            $totalShares = Video::sum('shares');
            $sponsoredCount = Video::where('is_sponsored', true)->count();

            $topVideos = Video::with(['user', 'place'])
                ->orderBy('views', 'desc')
                ->take(10)
                ->get();

            $topInfluencers = User::where('role', 'influenciador')
                ->withCount('videos')
                ->orderBy('videos_count', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_videos' => $totalVideos,
                    'total_views' => $totalViews,
                    'total_likes' => $totalLikes,
                    'total_shares' => $totalShares,
                    'sponsored_count' => $sponsoredCount,
                    'avg_views_per_video' => $totalVideos > 0 ? round($totalViews / $totalVideos) : 0,
                ],
                'top_videos' => $topVideos,
                'top_influencers' => $topInfluencers,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar estatísticas'], 500);
        }
    }
}
