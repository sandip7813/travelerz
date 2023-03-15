<?php
namespace App\Helpers;

use App\Models\Medias;
use App\Models\User;
use App\Models\Move;

use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; 

class MoveHelper
{
    public static function deleteBanner($image_uuid = null, $move_uuid = null){
        if( is_null($image_uuid) || is_null($move_uuid) ){
            return ['success' => false, 'message' => 'Invalid request!', 'status' => 400];
        }
        
        $user_id = 0;

        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user_id = $user->id;
        }

        $media = Medias::where('user_id', $user_id)
                        ->where('source_type', 'move_banner')
                        ->where('source_uuid', $move_uuid)
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
            'message' => 'Move Banner deleted successfully!',
            'move_uuid' => $move_uuid,
            'status' => 200
        ];
    }

    public static function deleteMove($move_uuid = null){
        if( is_null($move_uuid) ){
            return ['success' => false, 'message' => 'Invalid request!', 'status' => 400];
        }

        $user_id = 0;

        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user_id = $user->id;
        }

        $check_move_qry = Move::where('uuid', $move_uuid)->where('user_id', $user_id);
        $check_move = $check_move_qry->exists();

        if( !$check_move ){
            return ['success' => false, 'message' => 'No move found!', 'status' => 400];
        }

        $move = $check_move_qry->with(['banner'])->first();

        //++++++++++++ DELETE MOVE IMAGES :: Start ++++++++++++//
        $banner = $move->banner->uuid ?? null;
        
        if( !is_null($banner) ){
            self::deleteBanner($banner, $move_uuid);
        }
        //++++++++++++ DELETE MOVE IMAGES :: End ++++++++++++//

        //++++++++++++ DELETE INVITEES :: Start ++++++++++++//
        $move->invitees()->detach();
        //++++++++++++ DELETE INVITEES :: End ++++++++++++//

        //++++++++++++ DELETE MOVE :: Start ++++++++++++//
        $check_move_qry->forceDelete();
        //++++++++++++ DELETE MOVE :: End ++++++++++++//

        return [
            'success' => true,
            'message' => 'Move deleted successfully!',
            'move_uuid' => $move_uuid,
            'status' => 200
        ];
    }
}