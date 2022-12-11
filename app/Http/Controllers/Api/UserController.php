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

        $media = Medias::where('uuid', $image_uuid)->first();

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
