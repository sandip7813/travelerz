<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Medias;
use App\Models\UserPost;
use App\Models\PostLikes;
use App\Models\Comments;

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
        $field_name = 'post_picture';

        if(!$request->hasFile($field_name)) {
            return response()->json(['success' => false, 'message' => 'No file uploaded!'], 400);
        }
        
        //++++++++++++++++ CREATE POST :: Start ++++++++++++++++//
        $post_uuid = $request->post_uuid ?? null;

        if( is_null($post_uuid) ){
            $post = UserPost::create();
            $post_uuid = $post->uuid ?? null;
        }
        //++++++++++++++++ CREATE POST :: End ++++++++++++++++//

        $allowedfileExtension = ['jpeg', 'jpg', 'png', 'gif'];
        $mediaFiles = $request->file($field_name);
        $upload_picture_array = [];

        /* foreach ($files_array as $mediaFiles) {
            $extension = $mediaFiles->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if( $check ) {
                $upload_picture = UserHelper::uploadUserImages($field_name, $mediaFiles);
                $file_uuid = $upload_picture['file_uuid'] ?? null;

                if( !is_null($file_uuid) && !is_null($post_uuid) ){
                    Medias::where('uuid', $file_uuid)->update(['source_uuid' => $post_uuid]);
                }

                $upload_picture_array[] = $upload_picture;
            }
            else {
                return response()->json(['success' => false, 'message' => 'Invaid file extensions! Allowed extensions are ' . implode(', ', $allowedfileExtension)], 400);
            }
        } */

        $extension = $mediaFiles->getClientOriginalExtension();
        $check = in_array($extension, $allowedfileExtension);
        
        if( $check ) {
            $upload_picture = UserHelper::uploadUserImages($field_name, $mediaFiles);
            $file_uuid = $upload_picture['file_uuid'] ?? null;

            if( !is_null($file_uuid) && !is_null($post_uuid) ){
                Medias::where('uuid', $file_uuid)->update(['source_uuid' => $post_uuid]);
            }

            $upload_picture_array[] = $upload_picture;
        }
        else {
            return response()->json(['success' => false, 'message' => 'Invaid file extensions! Allowed extensions are ' . implode(', ', $allowedfileExtension)], 400);
        }

        $response_array = $upload_picture_array;
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

    public function addComment(Request $request){
        $post_uuid = $request->post_uuid ?? null;

        if( is_null($post_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $check_post = UserPost::where('uuid', $post_uuid)->exists();

        if( !$check_post ){
            return response()->json(['success' => false, 'message' => 'No post found!'], 400);
        }

        $content = $request->content ?? null;

        if( empty($content) ){
            return response()->json(['success' => false, 'message' => 'Type something to post!'], 400);
        }

        //+++++++++++++++++++++++ REPLY :: Start +++++++++++++++++++++++//
        $parent_uuid = $request->parent_uuid ?? null;
        $parent_id = null;

        if( !is_null($parent_uuid) ){
            $parent_comment = Comments::where('uuid', $parent_uuid)
                                        ->where('is_active', 1)
                                        ->first();
            
            $parent_id = isset($parent_comment->id) ? $parent_comment->id : null;

            if( is_null($parent_id) ){
                return response()->json(['success' => false, 'message' => 'No comment found to reply!'], 400);
            }
        }
        //+++++++++++++++++++++++ REPLY :: End +++++++++++++++++++++++//

        $comment = Comments::create([
           'post_uuid' => $post_uuid,
           'parent_id' => $parent_id,
           'parent_uuid' => $parent_uuid,
           'content' => $content
        ]);

        if( !is_null($parent_uuid) ){
            $response_message = 'You have replied to the comment uuid ' . $parent_uuid;
        }
        else{
            $response_message = 'Comment has been added successfully!';
        }

        return response()->json([
            'message' => $response_message,
            'post_uuid' => $post_uuid,
            'comment_uuid' => $comment->uuid
        ], 200);
    }

    public function deleteComment(Request $request){
        $comment_uuid = $request->comment_uuid ?? null;

        if( is_null($comment_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $comment = Comments::where('uuid', $comment_uuid)
                            ->where('user_uuid', $this->user->uuid)
                            ->first();
        
        if( !isset($comment->id) ){
            return response()->json(['success' => false, 'message' => 'No comment found!'], 400);
        }

        $comment->delete();

        Comments::where('parent_uuid', $comment_uuid)
                ->delete();
        
        return response()->json([
            'message' => 'Comment deleted successfully!'
        ], 200);
    }

    public function showAllPosts(){
        return UserPost::with(['pictures', 'created_by'])
                        ->withCount(['likes'])
                        ->where('status', '1')
                        ->paginate(25);
    }

    public function showComments($post_uuid){
        if( !isset($post_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $check_post = UserPost::where('uuid', $post_uuid)->exists();

        if( !$check_post ){
            return response()->json(['success' => false, 'message' => 'No post found!'], 400);
        }
        
        $comments = Comments::with('descendants')
                                ->where('post_uuid', $post_uuid)
                                ->whereNull('parent_uuid')
                                ->get()->toArray();
        
        return response()->json($comments, 200);
    }
}
