<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function getCountries(Request $request){
        $country_abbr = $request->codes ?? null;
        $getCountries = country_list($country_abbr);

        return response()->json([
            'success' => true,
            'countries' => $getCountries
        ], 200);
    }

    public function getStates(Request $request){
        $country_abbr = $request->country ?? null;
        $getStates = country_states($country_abbr);

        return response()->json([
            'success' => true,
            'states' => $getStates
        ], 200);
    }
}
