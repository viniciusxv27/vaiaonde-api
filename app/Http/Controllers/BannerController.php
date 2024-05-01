<?php

namespace App\Http\Controllers;

use App\Models\Banner;

use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function list(Request $request)
    {
        $banners = Banner::all();

        return response()->json(['banners' => $banners], 200);
    }
}