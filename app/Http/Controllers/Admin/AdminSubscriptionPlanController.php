<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminSubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $plans = SubscriptionPlan::orderBy('order', 'asc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }

    public function show($id)
    {
        $plan = SubscriptionPlan::find($id);

        if (!$plan) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'plan' => $plan,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:subscription_plans,slug',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric',
            'period' => 'required|in:month,year,quarter,semester',
            'period_count' => 'nullable|integer|min:1',
            'stripe_price_id' => 'nullable|string',
            'features' => 'nullable|array',
            'roulette_spins_per_month' => 'nullable|integer|min:0',
            'priority_support' => 'nullable|integer',
            'active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Gera slug automaticamente se não fornecido
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $plan = SubscriptionPlan::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Plano criado com sucesso',
            'plan' => $plan,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::find($id);

        if (!$plan) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:subscription_plans,slug,' . $id,
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric',
            'period' => 'sometimes|in:month,year,quarter,semester',
            'period_count' => 'nullable|integer|min:1',
            'stripe_price_id' => 'nullable|string',
            'features' => 'nullable|array',
            'roulette_spins_per_month' => 'nullable|integer|min:0',
            'priority_support' => 'nullable|integer',
            'active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Plano atualizado com sucesso',
            'plan' => $plan,
        ]);
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::find($id);

        if (!$plan) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plano deletado com sucesso',
        ]);
    }
}
