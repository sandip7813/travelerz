<?php
namespace App\Helpers;

use App\Models\Medias;
use App\Models\User;

use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 

class UserHelper
{
    public static function my_full_info(){
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user->load(['interests', 'profile_picture', 'banner_picture', 'followings', 'followers']);

            return $user;
        }
    }

    public static function uploadUserImages($image_type = 'profile_picture', $image_file){
        $response_array = [];

        $dir_main = config('filesystems.image_folder.main') . '/';
        $dir_1000x600 = config('filesystems.image_folder.1000x600') . '/';
        $dir_200x160 = config('filesystems.image_folder.200x160') . '/';

        $image_obj = Image::make($image_file);

        $image_name = time() . '-' . uniqid() . '.' . $image_file->getClientOriginalExtension();
        $image_dir = 'images/';

        $public_path = public_path( $image_dir );

        //------------- MAIN BANNER UPLOAD :: Start -------------//
        $destinationPath = $public_path . $dir_main;
        $image_obj->save($destinationPath . $image_name);
        //------------- MAIN BANNER UPLOAD :: End -------------//

        //------------- 1000 x 600 BANNER UPLOAD :: Start -------------//
        $destinationPathMid = $public_path . $dir_1000x600;
        $image_obj->resize(1000, 600);
        $image_obj->save($destinationPathMid . $image_name);
        //------------- 1000 x 600 BANNER UPLOAD :: End -------------//

        //------------- 200 x 160 BANNER UPLOAD :: Start -------------//
        $destinationPathThumbnail = $public_path . $dir_200x160;
        $image_obj->resize(200, 160);
        $image_obj->save($destinationPathThumbnail . $image_name);
        //------------- 200 x 160 BANNER UPLOAD :: End -------------//

        if( File::exists(public_path($image_dir . $dir_main . $image_name)) ){
            $user = auth('api')->user();
            $user_id = $user->id;

            $media_type = '';
            $message = '';

            if( $image_type == 'profile_picture' ){
                $media_type = 'user_profile';
                $message = 'Profile picture uploaded successfully!';
            }
            elseif( $image_type == 'banner_picture' ){
                $media_type = 'user_banner';
                $message = 'Banner picture uploaded successfully!';
            }

            Medias::where('user_id', $user_id)
                    ->where('file_type', 'image')
                    ->where('media_type', $media_type)
                    ->update(['is_active' => 0]);
            
            $media = Medias::create([
                        'user_id' => $user_id,
                        'file_type' => 'image',
                        'media_type' => $media_type,
                        'name' => $image_name,
                        'is_active' => 1
                    ]);

            $response_array = [
                'message' => $message,
                'file_name' => $image_name,
                'file_url_main' => url($image_dir . $dir_main . $image_name),
                'file_url_1000x600' => url($image_dir . $dir_1000x600 . $image_name),
                'file_url_200x160' => url($image_dir . $dir_200x160 . $image_name),
                'file_uuid' => $media->uuid ?? null
            ];
        }

        return $response_array;
    }

    public static function user_full_info($uuid){
        $user = User::where('uuid', $uuid)->first();

        if( isset($user->id) ){
            $user->load(['interests', 'profile_picture', 'banner_picture']);
        }

        return $user;
    }
}