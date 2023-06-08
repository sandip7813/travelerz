<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Move;

use Illuminate\Support\Str;

class Bookmark extends Model
{
    use HasFactory;

    protected $table = 'bookmarks';

    protected $fillable = ['move_uuid'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->user_uuid = auth('api')->user()->uuid;
        });
    }

    public function move(){
        return $this->hasOne(Move::class, 'uuid', 'move_uuid')->with(['banner', 'category', 'created_by', 'invitees']);
    }
}
