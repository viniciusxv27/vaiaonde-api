<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminBannerController extends Controller
{
    /**
     * Lista todos os banners
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $banners = Banner::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'banners' => $banners,
        ]);
    }

    /**
     * Mostra um banner específico
     */
    public function show($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['error' => 'Banner não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'banner' => $banner,
        ]);
    }

    /**
     * Cria um novo banner
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'required|string',
            'link_url' => 'nullable|string',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $banner = Banner::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Banner criado com sucesso',
            'banner' => $banner,
        ], 201);
    }

    /**
     * Atualiza um banner
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['error' => 'Banner não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'image_url' => 'sometimes|string',
            'link_url' => 'nullable|string',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $banner->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Banner atualizado com sucesso',
            'banner' => $banner,
        ]);
    }

    /**
     * Deleta um banner
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['error' => 'Banner não encontrado'], 404);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deletado com sucesso',
        ]);
    }
}
