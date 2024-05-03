<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\City;
use App\Models\Categorie;
use App\Models\Rating;

use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function list(Request $request, $id)
    {
        
        $places = Place::where('tipe_id', $id)->get();

        $list = [];
        $data = [];
        $count = 0;

        foreach ($places as $place) {
            $citys = City::where('id', $place->city_id)->get();

            foreach($citys as $city){
                $cityName = $city->name;
            }

            $listCategories = json_decode($place->categories_ids, true);
            $categories = '';

            foreach($listCategories as $ids){
                $categorieTemp = Categorie::where('id', $ids)->get();

                foreach($categorieTemp as $categorie){
                    $categories .= $categorie->name . ', ' ;
                }
            }


            $data = [
                "id" => $place->id,
                "name" => $place->name,
                "card_image" => $place->card_image,
                "categorie" => rtrim($categories, ", "),
                "city" => $cityName,
                "logo" => $place->logo,
                "ticket" => $place->ticket,
                "ticket_count" => $place->ticket_count,
                "hidden" => $place->hidden,
            ];

            $list[$count] = $data;

            $count++;
        }

        return response()->json(['places' => $list], 200);
    }
    public function listTop(Request $request)
    {
        $tops = Place::where('top', 1)->get();
        
        $list = [];
        $data = [];
        $count = 0;
        
        foreach ($tops as $top) {
            $listCategories = json_decode($top->categories_ids, true);
            $categories = '';

            foreach($listCategories as $ids){
                $categorieTemp = Categorie::where('id', $ids)->get();

                foreach($categorieTemp as $categorie){
                    $categories .= $categorie->name . ', ' ;
                }
            }

            $data = [
                "id" => $top->id,
                "card_image" => $top->card_image,
                "name" => $top->name,
                "categorie" => rtrim($categories, ", "),
            ];

            $list[$count] = $data;

            $count++;
        }
    
        return response()->json(['tops' => $list], 200);
    }
    public function show(Request $request, $id)
    {
        $place = Place::where('id', $id)->get();

        return response()->json(['place' => $place], 200);
    }
    public function rate(Request $request, $id)
    {
    }

}
