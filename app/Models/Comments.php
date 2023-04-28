<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Comments extends Model
{
    use HasFactory, SoftDeletes, HasRecursiveRelationships;

    protected $table = 'comments';

    protected $fillable = ['post_uuid', 'parent_id', 'parent_uuid', 'content'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->user_uuid = auth('api')->user()->uuid;
            $model->is_active = 1;
        });
    }

    public function parent(){
        return $this->belongsTo(\App\Models\Comments::class, 'parent_id');
    }

    public function comment_by(){
        return $this->hasOne(User::class, 'uuid', 'user_uuid')->with(['profile_picture']);
    }
}
