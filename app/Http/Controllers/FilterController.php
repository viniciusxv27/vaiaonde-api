<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\City;

use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function city(Request $request)
    {
        $citys = City::all();

        $list = [];

        foreach($citys as $city){
            $data = [
                "id" => $city->id,
                "name" => $city->name,
            ];

            $list[] = $data;
        }

        return response()->json(['citys' => $list], 200);
    }
    
    public function categorie(Request $request, $id)
    {
        $categories = Categorie::where('tipe_id', $id)->get();
        $list = [];

        foreach($categories as $categorie){
            $data = [
                "id" => $categorie->id,
                "name" => $categorie->name
            ];

            $list[] = $data;
        }

        return response()->json(['categories' => $list], 200);
    }
}