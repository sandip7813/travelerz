<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Chat;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $table = "chat_participants";
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->with(['profile_picture']);
    }

    public function chat_room(){
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
