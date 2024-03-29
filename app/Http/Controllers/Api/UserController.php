<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\InterestUser;
use App\Models\Medias;

use App\Helpers\UserHelper;

use Auth;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Twilio\Rest\Client;

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
        $user_data->gender = $request->gender ?? null;
        $user_data->about_me = $request->about_me ?? null;
        $user_data->country_id = $request->country_id ?? null;
        $user_data->state_id = $request->state_id ?? null;
        $user_data->city = $request->city ?? null;

        $user_data->save();

        return response()->json([
            'message' => 'Profile details updated successfully!',
        ], 200);
    }

    public function uploadProfilePicture(Request $request){
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $field_name = 'profile_picture';

        $image_file = $request->file($field_name);

        $upload_picture = UserHelper::uploadUserImages($field_name, $image_file);

        return response()->json($upload_picture, 200);
    }

    public function deletePicture(Request $request){
        $image_uuid = $request->image_uuid ?? null;

        if( is_null($image_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $media = Medias::where('uuid', $image_uuid)
                        ->where('user_id', $this->user->id)
                        ->whereIn('source_type', ['user_profile', 'user_banner'])
                        ->first();

        $image_name = $media->name ?? null;

        if( is_null($image_name) ){
            return response()->json(['success' => false, 'message' => 'No record found!'], 400);
        }

        $media->delete();

        $image_dir = 'images/';

        $dir_main = config('filesystems.image_folder.main') . '/';
        $dir_1000x600 = config('filesystems.image_folder.1000x600') . '/';
        $dir_200x160 = config('filesystems.image_folder.200x160') . '/';

        $file_path_main = public_path($image_dir . $dir_main . $image_name);
        $file_path_1000x600 = public_path($image_dir . $dir_1000x600 . $image_name);
        $file_path_200x160 = public_path($image_dir . $dir_200x160 . $image_name);

        if( !File::exists($file_path_main) ){
            return response()->json(['success' => false, 'message' => 'Image not found!'], 400);
        }

        $files_array = [
                        $file_path_main, 
                        $file_path_1000x600, 
                        $file_path_200x160
                    ];
        
        File::delete($files_array);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully!'
        ], 200);
    }

    public function uploadBannerPicture(Request $request){
        $validator = Validator::make($request->all(), [
            'banner_picture' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $field_name = 'banner_picture';

        $image_file = $request->file($field_name);

        $upload_picture = UserHelper::uploadUserImages($field_name, $image_file);

        return response()->json($upload_picture, 200);
    }

    public function addInterest(Request $request){
        $user_id = $this->user->id;
        $interest_id = $request->interest_id ? (int) $request->interest_id : null;

        if( is_null($interest_id) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        /* InterestUser::where('user_id', $user_id)
                    ->where('interest_id', $interest_id)
                    ->delete(); */
        
        $interestParams = [
            'user_id' => $user_id,
            'interest_id' => $interest_id,
        ];

        InterestUser::firstOrCreate(
            $interestParams,
            $interestParams
        );

        return response()->json([
            'success' => true,
            'message' => 'Interests added successfully!'
        ], 200);
    }

    public function followUser(Request $request){
        $following_uuid = $request->following_uuid ?? null;
        
        if( is_null($following_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid following uuid provided!'], 400);
        }

        $following_user = UserHelper::user_full_info($following_uuid);
        $following_id = $following_user->id ?? null;
        $following_name = $following_user->name ?? null;

        if( !isset($following_id) ){
            return response()->json(['success' => false, 'message' => 'No user found!'], 400);
        }

        $user = $this->user;

        $user_blocked = $following_user->blocked_users()->where('user_id', $user->id)->exists();

        if($user_blocked){
            return response()->json(['success' => false, 'message' => 'You are blocked by ' . $following_name ], 400);
        }

        $follower = $user->followings()->where('following_id', $following_id)->first();

        if( isset($follower->id) ){
            return response()->json(['success' => false, 'message' => 'You are already following ' . $following_name], 400);
        }

        $user->followings()->attach($following_user);

        return response()->json([
            'success' => true,
            'message' => 'You are now successfully following ' . $following_name,
            'user' => $following_user
        ], 200);
    }

    public function unfollowUser(Request $request){
        $following_uuid = $request->following_uuid ?? null;
        
        if( is_null($following_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid following uuid provided!'], 400);
        }

        $following_user = User::where('uuid', $following_uuid)->first();
        $following_id = $following_user->id ?? null;
        $following_name = $following_user->name ?? null;

        if( !isset($following_id) ){
            return response()->json(['success' => false, 'message' => 'No user found!'], 400);
        }

        $user = $this->user;

        $follower = $user->followings()->where('following_id', $following_id)->first();

        if( !isset($follower->id) ){
            return response()->json(['success' => false, 'message' => 'You are not following ' . $following_name], 400);
        }

        $user->followings()->detach($following_user);

        return response()->json([
            'success' => true,
            'message' => 'You have unfollowed ' . $following_name,
        ], 200);
    }

    public function blockUser(Request $request){
        $block_user_uuid = $request->block_user_uuid ?? null;
        
        if( is_null($block_user_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid uuid provided!'], 400);
        }

        $block_user = UserHelper::user_full_info($block_user_uuid);
        $block_user_id = $block_user->id ?? null;
        $block_user_name = $block_user->name ?? null;

        if( !isset($block_user_id) ){
            return response()->json(['success' => false, 'message' => 'No user found!'], 400);
        }

        $user = $this->user;

        $block = $user->blocked_users()->where('blocked_user_id', $block_user_id)->first();

        if( isset($block->id) ){
            return response()->json(['success' => false, 'message' => 'You have already blocked ' . $block_user_name], 400);
        }

        $user->blocked_users()->attach($block_user);

        //+++++++++++++ FORCED UNFOLLOW IF USER BLOCKED :: Start +++++++++++++//
        $user->followings()->detach($block_user);
        $block_user->followings()->detach($user);
        //+++++++++++++ FORCED UNFOLLOW IF USER BLOCKED :: End +++++++++++++//

        //+++++++++++++ FORCED UNFRIEND IF USER BLOCKED :: Start +++++++++++++//
        $user->friends()->detach($block_user);
        $block_user->friends()->detach($user);
        //+++++++++++++ FORCED UNFRIEND IF USER BLOCKED :: End +++++++++++++//

        return response()->json([
            'success' => true,
            'message' => 'You have blocked ' . $block_user_name,
            'user' => $block_user
        ], 200);
    }

    public function unblockUser(Request $request){
        $block_user_uuid = $request->block_user_uuid ?? null;
        
        if( is_null($block_user_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid uuid provided!'], 400);
        }

        $block_user = User::where('uuid', $block_user_uuid)->first();
        $block_user_id = $block_user->id ?? null;
        $block_user_name = $block_user->name ?? null;

        if( !isset($block_user_id) ){
            return response()->json(['success' => false, 'message' => 'No user found!'], 400);
        }

        $user = $this->user;

        $block = $user->blocked_users()->where('blocked_user_id', $block_user_id)->first();

        if( !isset($block->id) ){
            return response()->json(['success' => false, 'message' => $block_user_name . ' is not blocked by you!'], 400);
        }

        $user->blocked_users()->detach($block_user);

        return response()->json([
            'success' => true,
            'message' => 'You have unblocked ' . $block_user_name,
        ], 200);
    }

    public function syncUser(Request $request){
        $sync_list = $request->sync_list ?? [];

        if( empty($sync_list) ){
            return response()->json(['success' => false, 'message' => 'No number found in your phone contact list!'], 400);
        }

        $sync_users = User::whereIn('phone', $sync_list)->get();
        $total_sync_users = $sync_users->count();

        if($total_sync_users == 0){
            return response()->json(['success' => false, 'message' => 'No user matched with your phone contact list!'], 400);
        }

        $user = $this->user;

        foreach($sync_users as $sync_user){
            $user->friends()->detach($sync_user);
            $user_blocked = $sync_user->blocked_users()->where('user_id', $user->id)->exists();
            
            if( !$user_blocked ){
                $user->friends()->attach($sync_user);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $total_sync_users . ' friends added!',
        ], 200);
    }

    public function userDetails($user_uuid) {
        if( !isset($user_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }
        
        $user_data = UserHelper::user_full_info($user_uuid);
        return response()->json($user_data);
    }

    public function myInterests(){
        $user = $this->user;
        $user->load('interests');

        return $user;
    }

    public function sendSMS($recipient){
        $recipient = $recipient ?? null;

        $message = 'Hi Twilio test';

        $account_sid = config('services.twilio.sid');
        $auth_token = config('services.twilio.auth_token');
        $twilio_number = config('services.twilio.from');
        $client = new Client($account_sid, $auth_token);
        $twilio_response = $client->messages->create($recipient, ['from' => $twilio_number, 'body' => $message]);

        //print_r($twilio_response); exit;

        return response()->json([
            'success' => true,
            'twilio_response' => $twilio_response,
        ], 200);
    }

    public function updateFcmToken(Request $request){
        try{
            $request->user()->update(['fcm_token'=>$request->token]);
            return response()->json([
                'success'=>true
            ]);
        }catch(\Exception $e){
            report($e);
            return response()->json([
                'success'=>false
            ],500);
        }
    }

}
