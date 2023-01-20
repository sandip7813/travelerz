<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

use Illuminate\Support\Str;

class Medias extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'file_type', 'source_type', 'name', 'is_active'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user(){
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
