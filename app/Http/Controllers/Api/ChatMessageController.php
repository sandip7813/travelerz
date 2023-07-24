<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Auth;
use Validator;
use App\Notifications\ChatMessageNotification;

class ChatMessageController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    /**
     * Create a chat message
     *
     * @param StoreMessageRequest $request
     * @return JsonResponse
     */
    public function store(Request $request) : JsonResponse
    {
        $chatModel = get_class(new Chat());

        $validator = Validator::make($request->all(), [
            'chat_id'=>"required|exists:{$chatModel},id",
            'message'=>'required|string',
        ]);

        if( $validator->fails() ){
            return response()->json($validator->errors()->toJson(), 422);
        }

        $checkUser = ChatParticipant::where('chat_id', $request->chat_id)->where('user_id', $this->user->id)->exists();
        if( !$checkUser ){
            return response()->json(['success' => false, 'message' => 'You are not allowed to send message to this chat!'], 400);
        }

        $data['chat_id'] = $request->chat_id;
        $data['user_id'] = $this->user->id;
        $data['message'] = $request->message;

        $chatMessage = ChatMessage::create($data);
        $chatMessage->load('user');

        //+++++++++++++++++++++++ CHAT NOTIFICATION :: Start +++++++++++++++++++++++//
        $otherUsers = ChatParticipant::where('chat_id', $request->chat_id)->where('user_id', '!=', $this->user->id)->get();

        if( !empty($otherUsers) ){
            foreach($otherUsers as $otherUserVal){
                $otherUserVal->user_id;
                $chatUser = User::whereId($otherUserVal->user_id)->where('status', 1)->first();

                if($chatUser){
                    $notoficationParams = [];
                    $notoficationParams['message_by_user_id'] = $this->user->id;
                    $notoficationParams['message_by_user_uuid'] = $this->user->uuid ?? null;

                    $chatUser->notify(new ChatMessageNotification($notoficationParams));
                }
            }
        }
        //+++++++++++++++++++++++ CHAT NOTIFICATION :: End +++++++++++++++++++++++//

        return $this->success('Message has been sent successfully.');
    }

}