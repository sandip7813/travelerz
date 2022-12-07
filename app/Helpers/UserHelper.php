<?php
namespace App\Helpers;

class UserHelper
{
    public static function my_full_info(){
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $user->load('interests');

            return $user;
        }
    }
}