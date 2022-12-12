<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Interest;

class InterestController extends Controller
{
    public function getAllActiveInterests(){
        $interests = Interest::where('status', '1')->get()->map->only('id', 'uuid', 'name', 'slug', 'created_at');

        return response()->json([
            'success' => true,
            'interests' => $interests
        ], 200);
    }
}
