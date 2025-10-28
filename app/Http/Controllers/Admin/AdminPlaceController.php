<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPlaceController extends Controller
{
    /**
     * Lista todos os lugares
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Place::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $places = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'places' => $places,
        ]);
    }

    /**
     * Mostra um lugar específico
     */
    public function show($id)
    {
        $place = Place::find($id);

        if (!$place) {
            return response()->json(['error' => 'Lugar não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'place' => $place,
        ]);
    }

    /**
     * Cria um novo lugar
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'card_image' => 'nullable|string',
            'review' => 'nullable|numeric',
            'categories_ids' => 'nullable|string',
            'city_id' => 'required|integer',
            'logo' => 'nullable|string',
            'instagram_url' => 'nullable|string',
            'phone' => 'nullable|string',
            'location_url' => 'nullable|string',
            'location' => 'nullable|string',
            'uber_url' => 'nullable|string',
            'hidden' => 'nullable|boolean',
            'tipe_id' => 'nullable|integer',
            'top' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $place = Place::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lugar criado com sucesso',
            'place' => $place,
        ], 201);
    }

    /**
     * Atualiza um lugar
     */
    public function update(Request $request, $id)
    {
        $place = Place::find($id);

        if (!$place) {
            return response()->json(['error' => 'Lugar não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'card_image' => 'nullable|string',
            'review' => 'nullable|numeric',
            'categories_ids' => 'nullable|string',
            'city_id' => 'sometimes|integer',
            'logo' => 'nullable|string',
            'instagram_url' => 'nullable|string',
            'phone' => 'nullable|string',
            'location_url' => 'nullable|string',
            'location' => 'nullable|string',
            'uber_url' => 'nullable|string',
            'hidden' => 'nullable|boolean',
            'tipe_id' => 'nullable|integer',
            'top' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $place->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lugar atualizado com sucesso',
            'place' => $place,
        ]);
    }

    /**
     * Deleta um lugar
     */
    public function destroy($id)
    {
        $place = Place::find($id);

        if (!$place) {
            return response()->json(['error' => 'Lugar não encontrado'], 404);
        }

        $place->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lugar deletado com sucesso',
        ]);
    }
}
