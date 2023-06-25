<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use Illuminate\Http\Request;

use App\Events\NewMessageSent;
use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Auth;
use Validator;

class ChatMessageController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }
    /**
     * Gets chat message
     *
     * @param GetMessageRequest $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $roomId = $request->room_id ?? null;
        $currentPage = $request->page ?? 1;
        $pageSize = $request->page_size ?? 15;
        echo $roomId;
        $messages = ChatMessage::where('chat_id', $roomId)
                                //->with('user')
                                ->latest('created_at')
                                ->get()
                                /* ->simplePaginate(
                                    $pageSize,
                                    ['*'],
                                    'page',
                                    $currentPage
                                ) */;
        print_r($messages);
        return $this->success($messages);
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

        $data['chat_id'] = $request->chat_id;
        $data['user_id'] = $this->user->id;
        $data['message'] = $request->message;

        $chatMessage = ChatMessage::create($data);
        $chatMessage->load('user');

        /// TODO send broadcast event to pusher and send notification to onesignal services
        $this->sendNotificationToOther($chatMessage);

        return $this->success('Message has been sent successfully.');
    }

    /**
     * Send notification to other users
     *
     * @param ChatMessage $chatMessage
     */
    private function sendNotificationToOther(ChatMessage $chatMessage) : void {

        // TODO move this event broadcast to observer
        broadcast(new NewMessageSent($chatMessage))->toOthers();

        $user = $this->user;
        $userId = $user->id;

        $chat = Chat::where('id',$chatMessage->chat_id)
            ->with(['participants'=>function($query) use ($userId){
                $query->where('user_id','!=',$userId);
            }])
            ->first();
        if(count($chat->participants) > 0){
            $otherUserId = $chat->participants[0]->id;

            $otherUser = User::where('id',$otherUserId)->first();
            $otherUser->sendNewMessageNotification([
                'messageData'=>[
                    'senderName'=>$user->username,
                    'message'=>$chatMessage->message,
                    'chatId'=>$chatMessage->chat_id
                ]
            ]);

        }

    }


}