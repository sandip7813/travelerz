<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;
use App\Models\UserPost;

class Interest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public static function generateSlug($name){
        $slug = Str::slug($name);
        $duplicate = static::withTrashed()->where('slug', 'like', '%' . $slug . '%')->count();
        return ($duplicate > 0) ? $slug . '-' . ($duplicate + 1) : $slug;
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function posts(){
        return $this->belongsToMany(Interest::class, 'interest_post', 'user_post_id', 'interest_id');
    }
}
