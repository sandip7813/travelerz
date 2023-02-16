<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Medias;
use App\Models\PostLikes;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_posts';

    protected $fillable = ['user_id', 'content', 'location', 'latitude', 'longitude', 'status'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->user_id = auth('api')->user()->id;
        });
    }

    public function pictures(){
        return $this->hasMany(Medias::class, 'source_uuid', 'uuid')
                    ->where('file_type', 'image')
                    ->where('source_type', 'user_post')
                    ->where('is_active', 1);
    }

    public function likes(){
        return $this->hasMany(PostLikes::class, 'post_uuid', 'uuid');
    }
}