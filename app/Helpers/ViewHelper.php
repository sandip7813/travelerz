<?php
use App\Models\Country;
use App\Models\State;

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