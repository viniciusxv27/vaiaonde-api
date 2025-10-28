<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminVoucherController extends Controller
{
    /**
     * Lista todos os vouchers
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Voucher::with('place');

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Mostra um voucher específico
     */
    public function show($id)
    {
        $voucher = Voucher::with('place')->find($id);

        if (!$voucher) {
            return response()->json(['error' => 'Voucher não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'voucher' => $voucher,
        ]);
    }

    /**
     * Cria um novo voucher
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'place_id' => 'required|exists:place,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_type' => 'required|in:percentage,fixed,free_item',
            'discount_value' => 'nullable|numeric',
            'code' => 'nullable|string|unique:vouchers,code',
            'max_uses' => 'nullable|integer',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'active' => 'nullable|boolean',
            'club_exclusive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Gera código automaticamente se não fornecido
        if (!isset($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
        }

        $voucher = Voucher::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Voucher criado com sucesso',
            'voucher' => $voucher,
        ], 201);
    }

    /**
     * Atualiza um voucher
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher) {
            return response()->json(['error' => 'Voucher não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'place_id' => 'sometimes|exists:place,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'discount_type' => 'sometimes|in:percentage,fixed,free_item',
            'discount_value' => 'nullable|numeric',
            'code' => 'nullable|string|unique:vouchers,code,' . $id,
            'max_uses' => 'nullable|integer',
            'valid_from' => 'sometimes|date',
            'valid_until' => 'sometimes|date',
            'active' => 'nullable|boolean',
            'club_exclusive' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $voucher->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Voucher atualizado com sucesso',
            'voucher' => $voucher,
        ]);
    }

    /**
     * Deleta um voucher
     */
    public function destroy($id)
    {
        $voucher = Voucher::find($id);

        if (!$voucher) {
            return response()->json(['error' => 'Voucher não encontrado'], 404);
        }

        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Voucher deletado com sucesso',
        ]);
    }
}
