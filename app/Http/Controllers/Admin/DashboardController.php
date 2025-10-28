<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Place;
use App\Models\Voucher;
use App\Models\Banner;
use App\Models\Rating;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard com estatísticas gerais
     */
    public function index(Request $request)
    {
        $stats = [
            'total_users' => User::count(),
            'total_places' => Place::count(),
            'total_vouchers' => Voucher::count(),
            'total_banners' => Banner::count(),
            'active_subscriptions' => User::where('subscription', true)->count(),
            'total_ratings' => Rating::count(),
            'total_revenue' => User::where('subscription', true)->count() * 29.90, // Valor exemplo
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Retorna gráficos e métricas detalhadas
     */
    public function analytics(Request $request)
    {
        // Usuários por mês nos últimos 6 meses
        $usersPerMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $usersPerMonth[] = [
                'month' => $date->format('M/Y'),
                'count' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        // Top places por avaliações
        $topPlaces = Place::withCount('ratings')
            ->orderBy('ratings_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'ratings_count']);

        // Vouchers mais usados
        $topVouchers = Voucher::withCount('userVouchers')
            ->orderBy('user_vouchers_count', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'uses_count']);

        return response()->json([
            'success' => true,
            'analytics' => [
                'users_per_month' => $usersPerMonth,
                'top_places' => $topPlaces,
                'top_vouchers' => $topVouchers,
            ],
        ]);
    }
}
