<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Medias;
use App\Models\UserPost;
use App\Models\PostLikes;

use Auth;
use Validator;
use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 

use App\Helpers\UserHelper;
use App\Helpers\PostHelper;

class UserPostController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    public function uploadPostPicture(Request $request){
        $validator = Validator::make($request->all(), [
            'post_picture' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        //++++++++++++++++ CREATE POST :: Start ++++++++++++++++//
        $post_uuid = $request->post_uuid ?? null;

        if( is_null($post_uuid) ){
            $post = UserPost::create();
            $post_uuid = $post->uuid ?? null;
        }
        //++++++++++++++++ CREATE POST :: End ++++++++++++++++//

        //++++++++++++++++ UPLOAD PICTURE :: Start ++++++++++++++++//
        $field_name = 'post_picture';

        $image_file = $request->file($field_name);

        $upload_picture = UserHelper::uploadUserImages($field_name, $image_file);
        $file_uuid = $upload_picture['file_uuid'] ?? null;

        if( !is_null($file_uuid) && !is_null($post_uuid) ){
            Medias::where('uuid', $file_uuid)->update(['source_uuid' => $post_uuid]);
        }
        //++++++++++++++++ UPLOAD PICTURE :: End ++++++++++++++++//

        $response_array = $upload_picture;
        $response_array['post_uuid'] = $post_uuid;

        return response()->json($response_array, 200);
    }

    public function deletePostPicture(Request $request){
        $image_uuid = $request->image_uuid ?? null;
        $post_uuid = $request->post_uuid ?? null;

        if( is_null($image_uuid) || is_null($post_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $delete_response = PostHelper::deletePostPicture($image_uuid, $post_uuid);
        $response_status = $delete_response['status'] ?? null;

        return response()->json($delete_response, $response_status);
    }

    public function createUpdatePost(Request $request){
        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $post_uuid = $request->post_uuid ?? null;

        $post_array = [
            'content' => $request->content ?? null,
            'location' => $request->location ?? null,
            'latitude' => $request->latitude ? (float) $request->latitude : null,
            'longitude' => $request->longitude ? (float) $request->longitude : null,
        ];

        if( !is_null($post_uuid) ){
            $post_where = UserPost::where('uuid', $post_uuid)
                                    ->where('user_id', $this->user->id)
                                    ->where('status', '!=', 2);

            $post = $post_where->first();
            
            if( !isset($post->id) ){
                return response()->json(['success' => false, 'message' => 'Post not found!'], 400);
            }

            if( $post->status == '0' ){
                $post_array['status'] = '1';
            }

            $post_where->update($post_array);
            
            $response_msg = 'Post has been updated successfully!';
        }
        else{
            $post_array['status'] = '1';
            $post = UserPost::create($post_array);

            $post_uuid = $post->uuid ?? null;

            $response_msg = 'Post has been added successfully!';
        }

        return response()->json([
            'message' => $response_msg,
            'post_uuid' => $post_uuid
        ], 200);
    }

    public function getMyPosts(){
        $posts = $this->user->posts()->paginate(25);
        return response()->json($posts, 200);
    }

    public function likeUnlikePost(Request $request){
        $post_uuid = $request->post_uuid ?? null;

        if( is_null($post_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $check_post = UserPost::where('uuid', $post_uuid)->exists();

        if( !$check_post ){
            return response()->json(['success' => false, 'message' => 'No post found!'], 400);
        }

        $post_like_qry = PostLikes::where('post_uuid', $post_uuid)
                                ->where('user_uuid', $this->user->uuid);
        
        $post_like = $post_like_qry->exists();

        if( $post_like ){
            $post_like_qry->delete();
            $response_msg = 'unliked';
        }
        else{
            PostLikes::create(['post_uuid' => $post_uuid]);
            $response_msg = 'liked';
        }

        return response()->json([
            'message' => 'You have ' . $response_msg . ' the post',
            'post_uuid' => $post_uuid
        ], 200);
    }

    public function deletePost(Request $request){
        $post_uuid = $request->post_uuid ?? null;

        $delete_response = PostHelper::deletePost($post_uuid);
        $response_status = $delete_response['status'] ?? null;

        return response()->json($delete_response, $response_status);
    }
}