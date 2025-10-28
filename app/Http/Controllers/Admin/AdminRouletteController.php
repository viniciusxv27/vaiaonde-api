<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoulettePrize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminRouletteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $prizes = RoulettePrize::with('voucher')->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'prizes' => $prizes,
        ]);
    }

    public function show($id)
    {
        $prize = RoulettePrize::with('voucher')->find($id);

        if (!$prize) {
            return response()->json(['error' => 'Prêmio não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'prize' => $prize,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:voucher,points,discount,free_item,cashback',
            'prize_value' => 'nullable|string',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'points_value' => 'nullable|integer',
            'discount_value' => 'nullable|numeric',
            'image_url' => 'nullable|string',
            'color' => 'nullable|string',
            'probability' => 'required|integer|min:1|max:100',
            'quantity' => 'nullable|integer',
            'active' => 'nullable|boolean',
            'club_exclusive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prize = RoulettePrize::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prêmio criado com sucesso',
            'prize' => $prize,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $prize = RoulettePrize::find($id);

        if (!$prize) {
            return response()->json(['error' => 'Prêmio não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|in:voucher,points,discount,free_item,cashback',
            'prize_value' => 'nullable|string',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'points_value' => 'nullable|integer',
            'discount_value' => 'nullable|numeric',
            'image_url' => 'nullable|string',
            'color' => 'nullable|string',
            'probability' => 'sometimes|integer|min:1|max:100',
            'quantity' => 'nullable|integer',
            'active' => 'nullable|boolean',
            'club_exclusive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $prize->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prêmio atualizado com sucesso',
            'prize' => $prize,
        ]);
    }

    public function destroy($id)
    {
        $prize = RoulettePrize::find($id);

        if (!$prize) {
            return response()->json(['error' => 'Prêmio não encontrado'], 404);
        }

        $prize->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prêmio deletado com sucesso',
        ]);
    }
}
