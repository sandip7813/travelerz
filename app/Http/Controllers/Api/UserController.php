<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\InterestUser;

use App\Helpers\UserHelper;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public $user;

    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    public function myProfile() {
        $user_data = UserHelper::my_full_info();
        return response()->json($user_data);
    }

    public function editProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $user_data = $this->user;

        $user_data->name = $request->name;
        $user_data->date_of_birth = ( $request->date_of_birth && !empty($request->date_of_birth) ) ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : NULL;

        $user_data->save();

        return response()->json([
            'message' => 'Profile details updated successfully!',
        ], 200);
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
