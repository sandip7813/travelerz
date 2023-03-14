<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Medias;
use App\Models\Move;
use App\Models\User;

use Auth;
use Validator;
use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 

class MoveController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    public function createMove(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'move_on'=> 'required|date_format:Y-m-d H:i:s',
            'category' => 'required',
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'privacy' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $move = Move::create([
            'title' => $request->title,
            'move_on' => $request->move_on,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'privacy' => $request->privacy,
            'category_id' => $request->category,
        ]);

        $move_uuid = $move->uuid ?? null;
        $invited_members = $request->invited_members ?? [];

        if( !is_null($move_uuid) && !empty($invited_members) ){
            $invited_users = User::whereIn('id', $invited_members)->get();
            $move->invitees()->attach($invited_users);
        }

        $response_msg = 'Move has been added successfully!';

        return response()->json([
            'message' => $response_msg,
            'move_uuid' => $move_uuid
        ], 200);
    }
}
