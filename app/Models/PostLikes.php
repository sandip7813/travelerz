<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class PostLikes extends Model
{
    use HasFactory;

    protected $table = 'post_likes';

    protected $fillable = ['post_uuid'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->user_uuid = auth('api')->user()->uuid;
        });
    }
}
