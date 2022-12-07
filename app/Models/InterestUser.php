<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestUser extends Model
{
    use HasFactory;

    protected $table = 'interest_user';

    protected $fillable = ['user_id', 'interest_id'];

    public $timestamps = false;
}
