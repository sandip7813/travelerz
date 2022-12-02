<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $table = 'otp_verifications';

    protected $fillable = ['otp', 'user_uuid', 'otp_type', 'otp_type'];

    public static function generate_otp( $digits = 4 ){
        return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
    }
}
