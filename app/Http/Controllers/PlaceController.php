<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\City;
use App\Models\Categorie;
use App\Models\Rating;
use App\Models\Coords;
use App\Models\Hourly;

use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function list(Request $request, $id)
    {
        
        $places = Place::where('tipe_id', $id)->get();

        $list = [];

        foreach ($places as $place) {
            $ratings = Rating::where('place_id', $place->id)->pluck('rate')->toArray();

            $rate = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;

            $cityName = City::where('id', $place->city_id)->value('name');
            
            $coordsData = Coords::where('place_id', $place->id)->first();

            if ($coordsData) {
                $coords = [
                    "lat" => $coordsData->latitude,
                    "long" => $coordsData->longitude
                ];
            } else {
                $coords = [
                    "lat" => null,
                    "long" => null
                ];
            }

            $listCategories = json_decode($place->categories_ids, true);

            $categories = Categorie::whereIn('id', $listCategories)->pluck('name')->implode(', '); 

            $data = [
                "id" => $place->id,
                "name" => $place->name,
                "card_image" => $place->card_image,
                "categorie" => $categories,
                "city" => $cityName,
                "logo" => $place->logo,
                "ticket" => $place->ticket,
                "ticket_count" => $place->ticket_count,
                "hidden" => $place->hidden,
                "rate" => number_format($rate, 2),
                "coords" => $coords,
                "hourly" => '1'
            ];

            $list[] = $data;
        }

        return response()->json(['places' => $list], 200);
    }
    
    public function listTop(Request $request)
    {
        $tops = Place::where('top', 1)->get();
        
        $list = [];
        
        foreach ($tops as $top) {
            $ratings = Rating::where('place_id', $top->id)->pluck('rate')->toArray();

            $rate = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;

            $listCategories = json_decode($top->categories_ids, true);

            $categories = Categorie::whereIn('id', $listCategories)->pluck('name')->implode(', '); 

            $data = [
                "id" => $top->id,
                "card_image" => $top->card_image,
                "name" => $top->name,
                "categorie" => $categories,
                "rate" => number_format($rate, 2),
            ];

            $list[] = $data;

        }
    
        return response()->json(['tops' => $list], 200);
    }

    public function show(Request $request, $id)
    {
        $place = Place::where('id', $id)->get();

        return response()->json(['place' => $place], 200);
    }

}
