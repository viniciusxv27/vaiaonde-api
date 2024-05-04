<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\City;
use App\Models\Categorie;
use App\Models\Rating;
use App\Models\Coords;

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
            $ratings = Rating::where('place_id', $place->id)->get();
            $countNotes = 0;
            $notes = [];

            foreach($ratings as $rating){
                $notes[(int) $countNotes] = $rating->rate;
                $countNotes++;
            }

            if($countNotes == 0){
                $countNotes = 1;
            }

            $rate = array_sum($notes)/$countNotes;

            $citys = City::where('id', $place->city_id)->get();

            foreach($citys as $city){
                $cityName = $city->name;
            }
            
            
            $coordsData = Coords::where('place_id', $place->id)->get();

            foreach($coordsData as $coord){
                $coords = [
                    "lat" => $coord->latitude, 
                    "long" => $coord->longitude
                ];
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
                "rate" => number_format($rate, 2),
                "coords" => $coords,
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
            $ratings = Rating::where('place_id', $top->id)->get();
            $countNotes = 0;
            $notes = [];

            foreach($ratings as $rating){
                $notes[(int) $countNotes] = $rating->rate;
                $countNotes++;
            }

            if($countNotes == 0){
                $countNotes = 1;
            }

            $rate = array_sum($notes)/$countNotes;

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
                "rate" => number_format($rate, 2),
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

}
