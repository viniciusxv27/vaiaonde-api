<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Favorite;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class FavoriteController extends Controller
{
    public function list(Request $request)
    {
        
        $token = $request->bearerToken();
        
        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                
                if ($payload && $payload->get('sub')) {
                    $userId = $payload->get('sub');
                    
                    $favorites = Favorite::where('user_id', $userId)->get();

                    if ($favorites) {
                        $data = [];

                        foreach ($favorites as $favorite) {
                            $place = Place::find($favorite->place_id);

                            $listCategories = json_decode($place->categories_ids, true);

                            $categories = Categorie::whereIn('id', $listCategories)->pluck('name')->implode(', ');

                            $data = [
                                "name" => $place->name,
                                "image" => $place->card_image,
                                "categories" => $categories,
                            ];
                        }

                        return response()->json(['favorites' => $data], 200);
                    } else {
                        return response()->json(['error' => 'Sem Favoritos'], 200);
                    }
                } else {
                    return response()->json(['error' => 'Token de autorização inválido'], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['error' => 'Token expirado'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['error' => 'Token inválido'], 401);
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['error' => 'Erro ao processar o token'], 500);
            }
        } else {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }
    }

    public function add(Request $request)
    {
    }

    public function delete(Request $request)
    {
    }
}
