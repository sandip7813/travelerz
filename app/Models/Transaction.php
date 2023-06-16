<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = ['payment_intent_id', 'charge_id', 'payment_status', 'amount', 'membership_uuid', 'membership_details'];

    protected $casts = [
        'membership_details' => 'array'
    ];
}
