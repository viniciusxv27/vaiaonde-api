<?php

namespace App\Http\Controllers;

use App\Models\Place;

use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function list(Request $request)
    {
        $places = Place::all();

        return response()->json(['places' => $places], 200);
    }
    public function listTop(Request $request)
    {
        $tops = Place::where('top', 1)->get();

        $data = [];
    
        foreach ($tops as $top) {
            $data[] = [
                "id" => $top->id,
                "card_image" => $top->card_image,
                "name" => $top->name,
                "review" => $top->review,
            ];
        }
    
        return response()->json(['tops' => $data], 200);
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
