<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

use App\Models\User;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'amount', 'duration'];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}
