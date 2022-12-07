<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\InterestUser;

use Auth;

class UserController extends Controller
{
    public $user;

    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    public function myProfile() {
        $user_data = $this->user;
        $user_data->load('interests');
        
        return response()->json($user_data);
    }

    public function addInterest(Request $request){
        $user_id = $this->user->id;
        $interest_ids = $request->interest_ids ?? [];

        if( !is_array($interest_ids) ){
            return response()->json(['success' => false, 'message' => 'Invalid data provided!'], 400);
        }

        if( count($interest_ids) == 0 ){
            return response()->json(['success' => false, 'message' => 'You have to select atleast one interest!'], 400);
        }

        InterestUser::where('user_id', $user_id)->delete();

        if( count($interest_ids) > 0 ){
            foreach($interest_ids as $interest){
                InterestUser::create([
                    'user_id' => $user_id,
                    'interest_id' => $interest,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Interests added successfully!'
        ], 200);
    }
}
