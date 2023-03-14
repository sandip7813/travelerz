<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Categories;
use App\Models\User;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Move extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'move_on', 'location', 'latitude', 'longitude', 'privacy', 'category_id', 'status'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->user_id = auth('api')->user()->id;
        });
    }

    public function category(){
        return $this->hasOne(Categories::class, 'id', 'category_id');
    }

    public function created_by(){
        return $this->hasOne(User::class, 'id', 'user_id')->with(['profile_picture']);
    }

    public function banner(){
        return $this->hasOne(Medias::class, 'source_uuid', 'uuid')
                    ->where('file_type', 'image')
                    ->where('source_type', 'move_banner')
                    ->where('is_active', 1);
    }

    public function invitees(){
        return $this->belongsToMany(User::class);
    }
}
