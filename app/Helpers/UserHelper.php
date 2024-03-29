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
            $user->load(['interests', 'profile_picture', 'banner_picture', 'followings', 'followers', 'friends']);

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

            $source_type = '';
            $message = '';

            if( $image_type == 'profile_picture' ){
                $source_type = 'user_profile';
                $message = 'Profile picture uploaded successfully!';
            }
            elseif( $image_type == 'banner_picture' ){
                $source_type = 'user_banner';
                $message = 'Banner picture uploaded successfully!';
            }
            elseif( $image_type == 'post_picture' ){
                $source_type = 'user_post';
                $message = 'Post picture uploaded successfully!';
            }
            elseif( $image_type == 'move_banner' ){
                $source_type = 'move_banner';
                $message = 'Move Banner uploaded successfully!';
            }

            if( !in_array($image_type, ['post_picture', 'move_banner']) ){
                Medias::where('user_id', $user_id)
                        ->where('file_type', 'image')
                        ->where('source_type', $source_type)
                        ->update(['is_active' => 0]);
            }
            
            $media = Medias::create([
                        'user_id' => $user_id,
                        'file_type' => 'image',
                        'source_type' => $source_type,
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
            $user->load(['interests', 'profile_picture', 'banner_picture', 'followings', 'followers', 'friends', 'followed_by_me']);
        }
        //$user->followed_by_me();
        return $user;
    }

    public static function user_location($uuid){
        $user = User::with(['user_country', 'user_state'])
                    ->where('uuid', $uuid)->first();
        
        $user_country = $user->user_country->name ?? null;
        $user_state = $user->user_state->name ?? null;
        $user_city = $user->city ?? null;

        $location_array = [];

        if( !is_null($user_city) ){
            $location_array[] = $user_city;
        }

        if( !is_null($user_state) ){
            $location_array[] = $user_state;
        }

        if( !is_null($user_country) ){
            $location_array[] = $user_country;
        }

        $location_string = '';

        if( !empty($location_array) ){
            $location_string = implode(', ', $location_array);
        }

        return $location_string;
    }
}