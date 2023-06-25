<?php
namespace App\Helpers;

use App\Models\Move;
use App\Models\Chat;
use App\Models\User;

class ChatHelper
{
    public static function createChatRoomFromMove($move_uuid){
        $move = Move::where('uuid', $move_uuid)
                    ->where('status', 1)
                    ->first();

        $move_id = $move->id ?? null;

        if( is_null($move_id) ){
            return false;
        }

        $chat = Chat::updateOrCreate(
            ['move_uuid' => $move_uuid],
            [
                'created_by' => $move->user_id ?? null,
                'name' => $move->title ?? null,
                'move_uuid' => $move->uuid ?? null,
                'is_private' => 0
            ]
        );

        $chat_id = $chat->id ?? null;

        if( !is_null($chat_id) ){
            self::updateChatRoomParticipients($move_id, $chat_id);
        }

        return $chat;
    }

    public static function updateChatRoomParticipients($move_id, $chat_id){
        $chat = Chat::find($chat_id);
        $move = Move::find($move_id);
        $move_owner = $move->user_id ?? null;
        $move_members = $move->invitees->pluck('id')->toArray();
        array_push($move_members, $move_owner);
        $users = User::whereIn('id', $move_members)->get();
        $chat->participants()->sync($users, false);
    }
}