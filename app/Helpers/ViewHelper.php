<?php
use App\Models\Country;
use App\Models\State;

use App\Helpers\UserHelper;

if (! function_exists('country_list')) {
    function country_list($country_abbr = null){
        if( is_null($country_abbr) ){
            return Country::all();
        }
        else{
            return Country::whereIn('code', $country_abbr)->get();
        }
    }
}

if (! function_exists('country_states')) {
    function country_states($country){
        if( $country != '' ){
            $country_id = Country::where('code', $country)
                                ->orWhere('name', $country)
                                ->orWhere('id', $country)
                                ->first()->id;

            return State::where('country_id', $country_id)->get();
        }
        else{
            return null;
        }
    }
}

if (! function_exists('user_location')) {
    function user_location($uuid){
        return UserHelper::user_location($uuid);
    }
}

if (! function_exists('generate_image_url')) {
    function generate_image_url($image){
        $image_dir = 'images/';

        $dir_main = config('filesystems.image_folder.main') . '/';
        $dir_1000x600 = config('filesystems.image_folder.1000x600') . '/';
        $dir_200x160 = config('filesystems.image_folder.200x160') . '/';

        return [
            'file_name' => $image,
            'file_url_main' => url($image_dir . $dir_main . $image),
            'file_url_1000x600' => url($image_dir . $dir_1000x600 . $image),
            'file_url_200x160' => url($image_dir . $dir_200x160 . $image),
        ];
    }
}


if (! function_exists('no_image_url')) {
    function no_image_url(){
        return url('images/no-image.jpg');
    }
}
