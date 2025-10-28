<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Lista todos os planos disponíveis
     */
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::where('active', true)
            ->orderBy('order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $plans = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price' => $plan->price,
                'original_price' => $plan->original_price,
                'period' => $plan->period,
                'period_count' => $plan->period_count,
                'features' => $plan->features,
                'roulette_spins_per_month' => $plan->roulette_spins_per_month,
                'is_popular' => $plan->is_popular,
                'savings_percentage' => $plan->getSavingsPercentage(),
                'monthly_equivalent' => $plan->getMonthlyEquivalent(),
            ];
        });

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }

    /**
     * Detalhes de um plano específico
     */
    public function show($slug)
    {
        $plan = SubscriptionPlan::where('slug', $slug)
            ->where('active', true)
            ->first();

        if (!$plan) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price' => $plan->price,
                'original_price' => $plan->original_price,
                'period' => $plan->period,
                'period_count' => $plan->period_count,
                'features' => $plan->features,
                'roulette_spins_per_month' => $plan->roulette_spins_per_month,
                'priority_support' => $plan->priority_support,
                'is_popular' => $plan->is_popular,
                'savings_percentage' => $plan->getSavingsPercentage(),
                'monthly_equivalent' => $plan->getMonthlyEquivalent(),
            ],
        ]);
    }
}
