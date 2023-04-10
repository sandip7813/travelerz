<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Medias;
use App\Models\Move;
use App\Models\User;

use App\Helpers\UserHelper;
use App\Helpers\MoveHelper;

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
            /* 'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required', */
            'privacy' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $location = $request->location ?? null;
        $latitude = $request->latitude ?? null;
        $longitude = $request->longitude ?? null;

        $move = Move::create([
            'title' => $request->title,
            'move_on' => $request->move_on,
            'location' => $location,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'privacy' => $request->privacy,
            'category_id' => $request->category,
        ]);

        $move_uuid = $move->uuid ?? null;
        $invited_members = $request->invited_members ?? [];

        if( !is_null($move_uuid) && !empty($invited_members) ){
            $invited_users = User::whereIn('id', $invited_members)->get();
            $move->invitees()->attach($invited_users);
        }

        $field_name = 'move_banner';
        $banner_uploaded = false;

        if( $request->hasFile($field_name) ) {
            $allowedfileExtension = ['jpeg', 'jpg', 'png', 'gif'];
            $mediaFile = $request->file($field_name);

            $extension = $mediaFile->getClientOriginalExtension();
            $checkExtension = in_array($extension, $allowedfileExtension);

            if( $checkExtension ) {
                $upload_picture = UserHelper::uploadUserImages($field_name, $mediaFile);
                $file_uuid = $upload_picture['file_uuid'] ?? null;
    
                if( !is_null($file_uuid) && !is_null($move_uuid) ){
                    Medias::where('uuid', $file_uuid)->update(['source_uuid' => $move_uuid]);
                }

                $banner_uploaded = true;
            }
            else {
                return response()->json(['success' => false, 'message' => 'Invaid file extensions! Allowed extensions are ' . implode(', ', $allowedfileExtension)], 400);
            }
        }

        $response_array = [];
        $response_array['message'] = 'Move has been created successfully!';
        $response_array['move_uuid'] = $move_uuid;

        if( $banner_uploaded ){
            $response_array['banner'] = $upload_picture;
        }

        return response()->json($response_array, 200);
    }

    public function editMove(Request $request){
        $validator = Validator::make($request->all(), [
            'uuid' => 'required',
            'title' => 'required',
            'move_on'=> 'required|date_format:Y-m-d H:i:s',
            'category' => 'required',
            /* 'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required', */
            'privacy' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $move_uuid = $request->uuid ?? null;

        $move = Move::with('banner')
                    ->where('uuid', $move_uuid)
                    ->where('user_id', $this->user->id)
                    ->first();
        
        if( !isset($move->id) ){
            return response()->json(['success' => false, 'message' => 'No move found!'], 400);
        }
        
        $move->title = $request->title;
        $move->move_on = $request->move_on;
        $move->location = $request->location ?? null;
        $move->latitude = $request->latitude ?? null;
        $move->longitude = $request->longitude ?? null;
        $move->privacy = $request->privacy;
        $move->category_id = $request->category;

        $move->save();

        $invited_members = $request->invited_members ?? [];

        if( !is_null($move_uuid) && !empty($invited_members) ){
            $invited_users = User::whereIn('id', $invited_members)->get();
            $move->invitees()->attach($invited_users);
        }

        $field_name = 'move_banner';
        $banner_uploaded = false;

        if( $request->hasFile($field_name) ) {
            //+++++++++ DELETE EXISTING BANNER :: Start +++++++++//
            $banner_uuid = $move->banner->uuid ?? null;

            if( !is_null($banner_uuid) ){
                MoveHelper::deleteBanner($banner_uuid, $move_uuid);
            }
            //+++++++++ DELETE EXISTING BANNER :: End +++++++++//

            $allowedfileExtension = ['jpeg', 'jpg', 'png', 'gif'];
            $mediaFile = $request->file($field_name);

            $extension = $mediaFile->getClientOriginalExtension();
            $checkExtension = in_array($extension, $allowedfileExtension);

            if( $checkExtension ) {
                $upload_picture = UserHelper::uploadUserImages($field_name, $mediaFile);
                $file_uuid = $upload_picture['file_uuid'] ?? null;
    
                if( !is_null($file_uuid) && !is_null($move_uuid) ){
                    Medias::where('uuid', $file_uuid)->update(['source_uuid' => $move_uuid]);
                }

                $banner_uploaded = true;
            }
            else {
                return response()->json(['success' => false, 'message' => 'Invaid file extensions! Allowed extensions are ' . implode(', ', $allowedfileExtension)], 400);
            }
        }

        $response_array = [];
        $response_array['message'] = 'Move has been updated successfully!';
        $response_array['move_uuid'] = $move_uuid;

        if( $banner_uploaded ){
            $response_array['banner'] = $upload_picture;
        }

        return response()->json($response_array, 200);
    }

    public function moveDetails($uuid){
        return Move::with(['banner', 'category', 'created_by', 'invitees'])
                    ->where('uuid', $uuid)
                    ->where('status', '1')
                    ->first();
    }

    public function getMyMoves(){
        $moves = $this->user->moves()->paginate(25);
        return response()->json($moves, 200);
    }

    public function deleteInvited(Request $request){
        $move_uuid = $request->move_uuid ?? null;
        $invited_members = $request->invited_members ?? null;

        if( is_null($invited_members) || is_null($move_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $users = User::whereIn('id', $invited_members)->get();
        $move = Move::where('uuid', $move_uuid)->where('user_id', $this->user->id)->first();
        $move->invitees()->detach($users);

        return response()->json([
            'success' => true,
            'message' => 'Invited members are deleted!',
            'move_uuid' => $move_uuid
        ], 200);
    }

    public function deleteBanner(Request $request){
        $image_uuid = $request->image_uuid ?? null;
        $move_uuid = $request->move_uuid ?? null;

        if( is_null($image_uuid) || is_null($move_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $delete_response = MoveHelper::deleteBanner($image_uuid, $move_uuid);
        $response_status = $delete_response['status'] ?? null;

        return response()->json($delete_response, $response_status);
    }

    public function deleteMove(Request $request){
        $move_uuid = $request->move_uuid ?? null;

        $delete_response = MoveHelper::deleteMove($move_uuid);
        $response_status = $delete_response['status'] ?? null;

        return response()->json($delete_response, $response_status);
    }
}
