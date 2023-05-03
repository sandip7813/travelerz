<?php
namespace App\Helpers;

use App\Models\Medias;
use App\Models\User;
use App\Models\UserPost;
use App\Models\PostLikes;

use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 

class PostHelper
{
    public static function deletePostPicture($image_uuid = null, $post_uuid = null){
        if( is_null($image_uuid) || is_null($post_uuid) ){
            return ['success' => false, 'message' => 'Invalid request!', 'status' => 400];
        }
        
        $user_id = 0;

        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user_id = $user->id;
        }

        $media = Medias::where('user_id', $user_id)
                        ->where('source_type', 'user_post')
                        ->where('source_uuid', $post_uuid)
                        ->where('uuid', $image_uuid)
                        ->first();

        $image_name = $media->name ?? null;

        if( is_null($image_name) ){
            return ['success' => false, 'message' => 'No record found!', 'status' => 400];
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
            return ['success' => false, 'message' => 'Image not found!', 'status' => 400];
        }

        $files_array = [
                        $file_path_main, 
                        $file_path_1000x600, 
                        $file_path_200x160
                    ];
        
        File::delete($files_array);

        return [
            'success' => true,
            'message' => 'Post image deleted successfully!',
            'post_uuid' => $post_uuid,
            'status' => 200
        ];
    }

    public static function deletePost($post_uuid = null){
        if( is_null($post_uuid) ){
            return ['success' => false, 'message' => 'Invalid request!', 'status' => 400];
        }

        $user_id = 0;
        $user_uuid = '';

        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user_id = $user->id;
            $user_uuid = $user->uuid;
        }

        $check_post_qry = UserPost::where('uuid', $post_uuid)->where('user_id', $user_id);
        $check_post = $check_post_qry->exists();

        if( !$check_post ){
            return ['success' => false, 'message' => 'No post found!', 'status' => 400];
        }

        $post_info = $check_post_qry->with(['pictures'])->first();

        //++++++++++++ DELETE POST IMAGES :: Start ++++++++++++//
        $pictures = $post_info->pictures;
        
        if( $pictures->count() > 0 ){
            foreach($pictures as $pic){
                self::deletePostPicture($pic->uuid, $post_uuid);
            }
        }
        //++++++++++++ DELETE POST IMAGES :: End ++++++++++++//

        //++++++++++++ DELETE LIKES :: Start ++++++++++++//
        PostLikes::where('post_uuid', $post_uuid)
                ->where('user_uuid', $user_uuid)
                ->delete();
        //++++++++++++ DELETE LIKES :: End ++++++++++++//

        //++++++++++++ DELETE POST :: Start ++++++++++++//
        $check_post_qry->forceDelete();
        //++++++++++++ DELETE POST :: End ++++++++++++//

        return [
            'success' => true,
            'message' => 'Post deleted successfully!',
            'post_uuid' => $post_uuid,
            'status' => 200
        ];
    }

    public static function shared_count($post_uuid){
        return UserPost::where('parent_uuid', $post_uuid)->count();
    }

    public static function fetchPostUuid($parent_uuid){
        $parent_post = UserPost::where('uuid', $parent_uuid)->first();

        return !is_null($parent_post->parent_uuid) ? $parent_post->parent_uuid : $parent_uuid;
    }
}