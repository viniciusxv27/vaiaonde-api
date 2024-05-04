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
        $cityList = [];
        $count = 0;

        foreach($citys as $city){
            $cityList[$count] = [
                "id" => $city->id,
                "name" => $city->name,
            ];

            $count++;
        }

        return response()->json(['citys' => $cityList], 200);
    }
    
    public function categorie(Request $request, $id)
    {
        $categories = Categorie::where('tipe_id', $id)->get();
        $categoryList = [];
        $count = 0;

        foreach($categories as $categorie){
            $categoryList[$count] = [
                "id" => $categorie->id,
                "name" => $categorie->name
            ];

            $count++;
        }

        return response()->json(['categories' => $categoryList], 200);
    }
}