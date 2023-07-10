<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Medias;
use App\Models\Move;
use App\Models\User;
use App\Models\Bookmark;

use App\Helpers\UserHelper;
use App\Helpers\MoveHelper;
use App\Helpers\ChatHelper;

use Auth;
use Validator;
use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 

class MoveController extends Controller
{
    protected $invite_status = [
        0 => 'invited',
        1 => 'interested',
        2 => 'maybe',
        3 => 'going',
        4 => 'not going'
    ];

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
        $invited_members = $request->invited_members ?? null;

        if( !is_null($move_uuid) && !is_null($invited_members) ){
            $invited_array = explode(',', $invited_members);
            $invited_users = User::whereIn('id', $invited_array)->get();
            $move->invitees()->attach($invited_users);

            ChatHelper::createChatRoomFromMove($move_uuid);
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
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
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

        $invited_members = $request->invited_members ?? null;

        if( !is_null($move_uuid) && !is_null($invited_members) ){
            $invited_array = explode(',', $invited_members);
            $invited_users = User::whereIn('id', $invited_array)->get();
            $move->invitees()->sync($invited_users,);

            ChatHelper::createChatRoomFromMove($move_uuid);
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
        return Move::with(['banner', 'category', 'created_by', 'invitees', 'interested', 'maybe', 'going', 'notgoing'])
                    ->where('uuid', $uuid)
                    ->where('status', '1')
                    ->first();
    }

    public function getMyMoves(){
        return $this->user->moves()->orderBy('move_on', 'DESC')->paginate(25);

        /* $move_date = $request->move_date ?? null;
        $move_qry = $this->user->moves();

        if( !is_null($move_date) ){
            $move_qry->whereDate('move_on', \Carbon\Carbon::createFromFormat('d/m/Y', $move_date));
        }
        
        $moves = $move_qry->orderBy('move_on', 'DESC')->paginate(25);

        return response()->json($moves, 200); */
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

    public function saveUnsaveBookmark(Request $request){
        $move_uuid = $request->move_uuid ?? null;

        if( is_null($move_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $check_move = Move::where('uuid', $move_uuid)->exists();

        if( !$check_move ){
            return response()->json(['success' => false, 'message' => 'No move found!'], 400);
        }

        $bookmark_qry = Bookmark::where('move_uuid', $move_uuid)
                                ->where('user_uuid', $this->user->uuid);
        
        $bookmark = $bookmark_qry->exists();

        if( $bookmark ){
            $bookmark_qry->delete();
            $response_msg = 'unsaved';
        }
        else{
            Bookmark::create(['move_uuid' => $move_uuid]);
            $response_msg = 'saved';
        }

        return response()->json([
            'message' => 'You have ' . $response_msg . ' the move',
            'move_uuid' => $move_uuid
        ], 200);
    }

    public function mySavedMoves(Request $request){
        $my_saved_moves = Bookmark::with('move')->where('user_uuid', $this->user->uuid)
                                    ->orderBy('updated_at', 'DESC')->paginate(25);
        
        return response()->json($my_saved_moves, 200);
    }

    public function movesInvited(){
        return $this->user->moves_invited()->orderBy('move_on', 'DESC')->paginate(25);

        /* $move_date = $request->move_date ?? null;
        $move_qry = $this->user->moves_invited();

        if( !is_null($move_date) ){
            $move_qry->whereDate('move_on', \Carbon\Carbon::createFromFormat('d/m/Y', $move_date));
        }
        
        $moves = $move_qry->orderBy('move_on', 'DESC')->paginate(25);

        return response()->json($moves, 200); */
    }

    public function updateInviteStatus(Request $request){
        $move_uuid = $request->move_uuid ?? null;
        $invite_status = $request->status ?? null;

        if( is_null($move_uuid) || is_null($invite_status) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $move = Move::where('uuid', $move_uuid)->first();

        if( !isset($move->id) ){
            return response()->json(['success' => false, 'message' => 'No move found!'], 400);
        }

        $move_id = $move->id;
        $user = $this->user;

        $moves_invited = $user->moves_invited();

        if( !$moves_invited->where('move_id', $move_id)->exists() ){
            return response()->json(['success' => false, 'message' => 'You are not invited in this move!'], 400);
        }

        $moves_invited->updateExistingPivot($move_id, ['invite_status' => $invite_status]);

        return response()->json([
            'message' => 'Status updated successfully!',
        ], 200); 
    }

    public function hitList(){
        $hitList = Move::select('location', \DB::raw('count(*) as total'))
                        ->groupBy('location')
                        ->orderBy('total', 'DESC')
                        ->limit(4)
                        ->get();
        
        return response()->json($hitList, 200);
    }

}
